<?php

namespace App\Http\Controllers\Api;

use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\Roomtype;
use App\Models\Promotion;
use App\Mail\BookingConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class BookingController extends BaseApiController
{
    protected $bookingService;
    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }
    /**
     * Tìm các tổ hợp phòng còn trống đáp ứng đủ số lượng người.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableRoomCombinations(Request $request)
    {
        \Log::info("🚀 API Called - getAvailableRoomCombinations", $request->all());

        // 1. Validate hotel access using BaseApiController method
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            \Log::info("❌ Hotel validation failed");
            return $hotel;
        }
        \Log::info("✅ Hotel validation passed - Hotel: {$hotel->name}");

        // 2. Validate request data (WordPress callApi wraps data in 'data' key)
        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
            'language' => 'nullable|string|in:vi,en,ko,ja',
            'data.check_in' => 'required|date|after_or_equal:today',
            'data.check_out' => 'required|date|after_or_equal:data.check_in',
            'data.adults' => 'required|integer|min:1',
            'data.children' => 'nullable|integer|min:0',
            'data.children_ages' => 'nullable|array',
            'data.children_ages.*' => 'integer|min:0|max:17',
        ]);

        if ($validator->fails()) {
            \Log::info("❌ Input validation failed", $validator->errors()->toArray());
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }
        \Log::info("✅ Input validation passed");

        try {
            // Get language from request, default to 'vi'
            $language = $request->input('language', 'vi');

            // Set the app locale for translations
            app()->setLocale($language);

            // 3. Call service to find room combinations
            $params = $request->input('data');

            // Validate children ages if children count is provided
            $children = $params['children'] ?? 0;
            $childrenAges = $params['children_ages'] ?? [];

            // Additional validation: children_ages array length should match children count
            if ($children > 0 && count($childrenAges) !== $children) {
                return $this->errorResponse('Children ages must be provided for all children', 422);
            }

            \Log::info("🔍 Calling BookingService->findRoomCombinations");
            $data = $this->bookingService->findRoomCombinations(
                $hotel->id,
                $params['check_in'],
                $params['check_out'],
                $params['adults'],
                $children,
                $childrenAges,
                $language
            );

            // 4. Add hotel tax settings to response
            $response = [
                'rooms' => $data,
                'hotel_tax_settings' => [
                    'vat_rate' => $hotel->vat_rate ?? 10.00,
                    'service_charge_rate' => $hotel->service_charge_rate ?? 5.00,
                    'prices_include_tax' => $hotel->prices_include_tax ?? false,
                ]
            ];

            // 5. Return successful response using BaseApiController method
            \Log::info("📦 Returning response - Data count: " . count($data));
            return $this->successResponse($response, 'Available rooms found successfully');
        } catch (\Exception $e) {
            \Log::error('Find room combinations failed', [
                'hotel_id' => $hotel->id,
                'params' => $params ?? null,
                'error' => $e->getMessage()
            ]);
            return $this->errorResponse('Failed to find available rooms: ' . $e->getMessage(), 500);
        }
    }


    /*admin*/
    public function index(Request $request): JsonResponse
    {
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }

        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100',
            'status' => 'in:pending,confirmed,cancelled,completed,no_show',
            'search' => 'string|max:255',
            'date_from' => 'date',
            'date_to' => 'date|after_or_equal:date_from',
            'sort_field' => 'in:id,customer_name,check_in,check_out,total_amount,created_at',
            'sort_order' => 'in:asc,desc',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 400, $validator->errors());
        }

        $query = Booking::with(['bookingDetails.roomType', 'bookingDetails.promotion'])
            ->where('hotel_id', $hotel->id);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'LIKE', "%{$search}%")
                    ->orWhere('customer_email', 'LIKE', "%{$search}%")
                    ->orWhere('customer_phone', 'LIKE', "%{$search}%")
                    ->orWhere('booking_number', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->where('check_in', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('check_in', '<=', $request->date_to);
        }

        // Apply sorting
        $sortField = $request->get('sort_field', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortField, $sortOrder);

        // Paginate results
        $perPage = $request->get('per_page', 20);
        $bookings = $query->paginate($perPage);

        // Transform data
        $bookings->getCollection()->transform(function ($booking) {
            return $this->transformBooking($booking);
        });

        return $this->paginatedResponse($bookings);
    }

    /**
     * Get single booking details
     */
    public function show(Request $request, $id): JsonResponse
    {
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }

        $booking = Booking::with(['bookingDetails.roomType', 'bookingDetails.promotion'])
            ->where('hotel_id', $hotel->id)
            ->find($id);

        if (!$booking) {
            return $this->errorResponse('Booking not found', 404);
        }

        return $this->successResponse($this->transformBooking($booking, true));
    }

    /**
     * Create new booking
     */
    public function store(Request $request): JsonResponse
    {
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }

        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
            'data.guest' => 'required|array',
            'data.guest.first_name' => 'required|string|max:255',
            'data.guest.last_name' => 'required|string|max:255',
            'data.guest.email' => 'required|email|max:255',
            'data.guest.phone' => 'required|string|max:20',
            'data.guest.nationality' => 'nullable|string|max:3',
            'data.booking_details' => 'required|array',
            'data.booking_details.check_in' => 'required|date|after_or_equal:today',
            'data.booking_details.check_out' => 'required|date|after:data.booking_details.check_in',
            'data.booking_details.adults' => 'required|integer|min:1|max:10',
            'data.booking_details.children' => 'nullable|integer|min:0',
            'data.booking_details.rooms' => 'required|integer|min:1',
            'data.rooms' => 'required|array|min:1',
            'data.rooms.*.room_id' => 'required|string',
            'data.rooms.*.quantity' => 'required|integer|min:1',
            'data.rooms.*.promotion_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 400, $validator->errors());
        }

        // Extract data from request
        $data = $request->input('data');
        $guest = $data['guest'];
        $bookingDetails = $data['booking_details'];
        $rooms = $data['rooms'];

        // SECURITY: Validate guest information integrity
        $this->validateGuestSecurity($guest);

        // Parse dates
        $checkIn = Carbon::parse($bookingDetails['check_in']);
        $checkOut = Carbon::parse($bookingDetails['check_out']);

        // Validate all rooms exist and belong to hotel
        $roomIds = collect($rooms)->pluck('room_id')->unique();
        $roomTypes = Roomtype::whereIn('id', $roomIds)
            ->where('hotel_id', $hotel->id)
            ->get()
            ->keyBy('id');

        if ($roomTypes->count() !== $roomIds->count()) {
            return $this->errorResponse('One or more room types not found or inactive', 404);
        }

        // Validate all promotions if specified
        $promotionIds = collect($rooms)->pluck('promotion_id')->filter()->unique();
        if ($promotionIds->isNotEmpty()) {
            $promotions = Promotion::whereIn('id', $promotionIds)
                ->active()
                ->forHotel($hotel->id)
                ->get()
                ->keyBy('id');

            if ($promotions->count() !== $promotionIds->count()) {
                return $this->errorResponse('One or more promotions not found or invalid', 404);
            }
        }

        \DB::beginTransaction();
        try {
            // SECURITY: Re-validate all data to prevent tampering
            $this->validateBookingIntegrity($rooms, $roomTypes, $checkIn, $checkOut, $bookingDetails);

            // Calculate total pricing for all rooms
            $totalAmount = 0;
            $totalDiscount = 0;
            $totalTax = 0;
            $bookingDetails_array = [];

            foreach ($rooms as $roomData) {
                $roomType = $roomTypes[$roomData['room_id']];
                $quantity = (int) $roomData['quantity'];
                $promotionId = $roomData['promotion_id'] ?? null;

                // SECURITY: Double-check availability at booking time
                if (!$roomType->isAvailable($checkIn->toDateString(), $checkOut->toDateString(), $bookingDetails['adults'])) {
                    throw new \Exception("Room type {$roomType->name} not available for selected dates");
                }

                // SECURITY: Check inventory limits to prevent overbooking
                // Note: Inventory field doesn't exist in current database structure
                // $bookedQuantity = $this->getBookedQuantity($roomType->id, $checkIn, $checkOut);
                // $availableQuantity = $roomType->inventory - $bookedQuantity;
                // if ($quantity > $availableQuantity) {
                //     throw new \Exception("Only {$availableQuantity} rooms available for {$roomType->name}");
                // }

                // SECURITY: Re-calculate pricing server-side using BookingService (never trust client)
                $bookingService = new \App\Services\BookingService();

                // For mixed room bookings, distribute guests optimally based on room capabilities
                // Rooms with extra bed/additional pricing should accommodate extra guests first

                $roomCapacity = $roomType->adult_capacity ?? 2;
                $hasExtraBed = $roomType->is_extra_bed_available ?? false;
                $pricingPolicy = $roomType->pricingPolicy;

                // Calculate how many guests this room should handle for pricing
                $totalAdults = $bookingDetails['adults'];
                $totalRooms = array_sum(array_column($rooms, 'quantity'));

                if ($hasExtraBed && $pricingPolicy && $pricingPolicy->additional_adult_price > 0) {
                    // Room có extra bed - ưu tiên chứa thêm người
                    $adultsForPricing = min($roomCapacity + 1, $totalAdults); // +1 for extra bed
                } else {
                    // Room thường - chỉ chứa base capacity
                    $adultsForPricing = min($roomCapacity, $totalAdults);
                }

                // Distribute children evenly
                $childrenForPricing = round(($bookingDetails['children'] ?? 0) / $totalRooms);

                // Get room combinations for this specific room type with calculated guest count
                $searchResult = $bookingService->findRoomCombinations(
                    $hotel->id,
                    $checkIn->toDateString(),
                    $checkOut->toDateString(),
                    $adultsForPricing,
                    $childrenForPricing,
                    $bookingDetails['children_ages'] ?? [],
                    'vi'
                );

                // Find the matching room combination for this specific room type
                $matchingCombination = null;
                foreach ($searchResult as $combination) {
                    foreach ($combination['combination_details'] as $detail) {
                        if ($detail['room_type']['id'] == $roomType->id) {
                            $matchingCombination = $detail;
                            break 2;
                        }
                    }
                }

                if (!$matchingCombination) {
                    throw new \Exception("Unable to calculate pricing for room type {$roomType->name} with current booking parameters");
                }

                // Use pricing from BookingService, adjusting for actual requested quantity
                $apiQuantity = $matchingCombination['quantity'];
                $pricePerRoom = $matchingCombination['pricing_breakdown']['final_total'] / $apiQuantity;

                $pricing = [
                    'total_amount' => $pricePerRoom * $quantity,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                    'rate_per_night' => $pricePerRoom / $checkIn->diffInDays($checkOut),
                    'subtotal' => ($matchingCombination['pricing_breakdown']['promotion_applicable_amount'] + $matchingCombination['pricing_breakdown']['non_promotion_amount']) / $apiQuantity * $quantity,
                    'nights' => $checkIn->diffInDays($checkOut)
                ];

                // Apply promotion if specified
                if ($promotionId) {
                    // Find promotion by ID (promotions are indexed by array key, not promotion ID)
                    $promotionData = null;
                    foreach ($matchingCombination['promotions'] as $promoKey => $promoInfo) {
                        if ($promoInfo['details']['id'] == $promotionId) {
                            $promotionData = $promoInfo;
                            break;
                        }
                    }

                    if ($promotionData) {
                        $promotionDiscountPerRoom = ($promotionData['promotion_applicable_price'] - $promotionData['discounted_price_total']) / $apiQuantity;
                        $promotionTotalPerRoom = ($promotionData['discounted_price_total'] + $matchingCombination['pricing_breakdown']['non_promotion_amount']) / $apiQuantity;

                        $pricing['discount_amount'] = $promotionDiscountPerRoom * $quantity;
                        $pricing['total_amount'] = $promotionTotalPerRoom * $quantity;
                    }
                }

                // SECURITY: Validate promotion is still active and valid
                if ($promotionId) {
                    $this->validatePromotionSecurity($promotionId, $roomType, $checkIn, $checkOut);
                }

                // Multiply by quantity
                $roomTotal = $pricing['total_amount'] * $quantity;
                $roomDiscount = $pricing['discount_amount'] * $quantity;
                $roomTax = $pricing['tax_amount'] * $quantity;

                $totalAmount += $roomTotal;
                $totalDiscount += $roomDiscount;
                $totalTax += $roomTax;

                $bookingDetails_array[] = [
                    'roomtype_id' => $roomType->id,
                    'quantity' => $quantity,
                    'adults' => $adultsForPricing, // Use the calculated adults for this specific room
                    'children' => $childrenForPricing, // Use the calculated children for this specific room
                    'is_extra_bed_requested' => ($adultsForPricing > $roomCapacity), // Mark if extra bed is needed
                    'price_per_night' => $pricing['rate_per_night'],
                    'sub_total' => $pricing['subtotal'] * $quantity,
                    'nights' => $pricing['nights'],
                    'tax_amount' => $roomTax,
                    'discount_amount' => $roomDiscount,
                    'total_amount' => $roomTotal,
                    'promotion_id' => $promotionId,
                ];
            }

            // Create main booking record
            $booking = Booking::create([
                'hotel_id' => $hotel->id,
                'first_name' => $guest['first_name'],
                'last_name' => $guest['last_name'],
                'email' => $guest['email'],
                'phone_number' => $guest['phone'],
                'nationality' => $guest['nationality'] ?? null,
                'check_in' => $checkIn->toDateString(),
                'check_out' => $checkOut->toDateString(),
                'nights' => $checkIn->diffInDays($checkOut),
                'guests' => $bookingDetails['adults'] + ($bookingDetails['children'] ?? 0),
                'total_amount' => $totalAmount,
                'discount_amount' => $totalDiscount,
                'tax_amount' => $totalTax,
                'status' => 'pending',
            ]);

            // Create booking details for each room
            foreach ($bookingDetails_array as $detail) {
                BookingDetail::create(array_merge($detail, [
                    'booking_id' => $booking->id,
                ]));
            }

            // IMPORTANT: Deduct inventory (reduce available rooms)
            foreach ($rooms as $roomData) {
                $roomType = $roomTypes[$roomData['room_id']];
                $quantity = (int) $roomData['quantity'];

                // Update inventory for each date in the stay
                $currentDate = $checkIn->copy();
                while ($currentDate->lt($checkOut)) {
                    $roomRate = \App\Models\RoomRate::where('hotel_id', $hotel->id)
                        ->where('roomtype_id', $roomType->id)
                        ->where('date', $currentDate->toDateString())
                        ->first();

                    if ($roomRate) {
                        $success = $roomRate->bookRooms($quantity);
                        if (!$success) {
                            throw new \Exception("Failed to book {$quantity} rooms for {$roomType->name} on {$currentDate->toDateString()}. Only {$roomRate->available_rooms} available.");
                        }
                    } else {
                        // Create room rate if it doesn't exist with default values
                        $roomRate = \App\Models\RoomRate::create([
                            'hotel_id' => $hotel->id,
                            'roomtype_id' => $roomType->id,
                            'date' => $currentDate->toDateString(),
                            'price' => $roomType->price ?? 1000000,
                            'total_for_sale' => 10, // Default total
                            'booked_rooms' => $quantity,
                            'is_available' => true,
                        ]);
                    }

                    $currentDate->addDay();
                }
            }

            \DB::commit();

            // Send booking confirmation email
            try {
                // Prepare booking details for email
                $emailBookingDetails = [];
                foreach ($bookingDetails_array as $detail) {
                    $roomType = $roomTypes[$detail['roomtype_id']];
                    $emailBookingDetails[] = array_merge($detail, [
                        'roomtype_name' => $roomType->title ?? $roomType->name,
                    ]);
                }

                // Send email in background queue
                Mail::to($booking->email)->queue(
                    new BookingConfirmation($booking, $hotel, $emailBookingDetails)
                );

                \Log::info('Booking confirmation email queued', [
                    'booking_id' => $booking->id,
                    'email' => $booking->email
                ]);
            } catch (\Exception $emailException) {
                // Log email error but don't fail the booking
                \Log::error('Failed to send booking confirmation email', [
                    'booking_id' => $booking->id,
                    'email' => $booking->email,
                    'error' => $emailException->getMessage()
                ]);
            }

            return $this->successResponse([
                'booking_id' => $booking->id,
                'booking_number' => $booking->booking_number,
                'total_amount' => $totalAmount,
                'status' => 'pending',
            ], 'Booking created successfully', 201);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Booking creation failed', ['error' => $e->getMessage(), 'request' => $request->all()]);
            return $this->errorResponse('Failed to create booking: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update booking
     */
    public function update(Request $request, $id): JsonResponse
    {
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }

        $booking = Booking::where('hotel_id', $hotel->id)->find($id);
        if (!$booking) {
            return $this->errorResponse('Booking not found', 404);
        }

        if (!$booking->canBeModified()) {
            return $this->errorResponse('Booking cannot be modified', 422);
        }

        $validator = Validator::make($request->all(), [
            'customer_name' => 'string|max:255',
            'customer_email' => 'email|max:255',
            'customer_phone' => 'string|max:20',
            'customer_nationality' => 'nullable|string|max:3',
            'check_in' => 'date|after:today',
            'check_out' => 'date|after:check_in',
            'guests' => 'integer|min:1|max:10',
            'notes' => 'nullable|string|max:1000',
            'special_requests' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 400, $validator->errors());
        }

        DB::beginTransaction();
        try {
            $booking->update($request->only([
                'customer_name',
                'customer_email',
                'customer_phone',
                'customer_nationality',
                'check_in',
                'check_out',
                'guests',
                'notes',
                'special_requests'
            ]));

            // Recalculate nights if dates changed
            if ($request->has(['check_in', 'check_out'])) {
                $booking->nights = Carbon::parse($booking->check_in)->diffInDays(Carbon::parse($booking->check_out));
                $booking->save();
            }

            DB::commit();

            return $this->successResponse(
                $this->transformBooking($booking->load(['bookingDetails.roomType', 'bookingDetails.promotion'])),
                'Booking updated successfully'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to update booking', 500);
        }
    }

    /**
     * Update booking status
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }

        $booking = Booking::where('hotel_id', $hotel->id)->find($id);
        if (!$booking) {
            return $this->errorResponse('Booking not found', 404);
        }
        $requestData = $request->all();
        $validator = Validator::make($requestData['data'], [
            'status' => 'required|in:pending,confirmed,cancelled,completed,no_show',
            'cancellation_reason' => 'required_if:status,cancelled|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 400, $validator->errors());
        }
        //\Log::info($requestData['data']);
        $newStatus = $requestData['data']['status'];

        // Business rules for status changes
        if ($newStatus === 'cancelled' && !$booking->canBeCancelled()) {
            return $this->errorResponse('Booking cannot be cancelled', 422);
        }

        DB::beginTransaction();
        try {
            $oldStatus = $booking->status;
            $booking->status = $newStatus;

            if ($newStatus === 'cancelled') {
                $booking->cancellation_reason = $request->cancellation_reason;
                $booking->cancelled_at = now();
            } elseif ($newStatus === 'confirmed') {
                $booking->confirmed_at = now();
            } elseif ($newStatus === 'completed') {
                $booking->completed_at = now();
            }

            $booking->save();

            DB::commit();

            return $this->successResponse(
                $this->transformBooking($booking->load(['bookingDetails.roomType', 'bookingDetails.promotion'])),
                'Booking status updated successfully'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to update booking status', 500);
        }
    }

    /**
     * Delete booking
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }

        $booking = Booking::where('hotel_id', $hotel->id)->find($id);
        if (!$booking) {
            return $this->errorResponse('Booking not found', 404);
        }

        if (!in_array($booking->status, ['pending', 'cancelled'])) {
            return $this->errorResponse('Only pending or cancelled bookings can be deleted', 422);
        }

        DB::beginTransaction();
        try {
            $booking->delete();
            DB::commit();

            return $this->successResponse(null, 'Booking deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to delete booking', 500);
        }
    }

    /**
     * Calculate booking total
     */
    public function calculateTotal(Request $request): JsonResponse
    {
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }

        // WordPress callApi wraps data in 'data' key
        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
            'data.room_type_id' => 'required|integer|exists:roomtypes,id',
            'data.check_in' => 'required|date|after:today',
            'data.check_out' => 'required|date|after:data.check_in',
            'data.guests' => 'required|integer|min:1',
            'data.promotion_code' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $data = $request->input('data');
        $roomType = \App\Models\Roomtype::where('id', $data['room_type_id'])
            ->where('hotel_id', $hotel->id)
            ->first();

        if (!$roomType) {
            return $this->errorResponse('Room type not found', 404);
        }

        $checkIn = Carbon::parse($data['check_in']);
        $checkOut = Carbon::parse($data['check_out']);

        $pricingResult = $this->calculateBookingPricing($roomType, $checkIn, $checkOut, $data['promotion_code'] ?? null);

        if (!$pricingResult['success']) {
            return $this->errorResponse($pricingResult['message'], 422);
        }

        return $this->successResponse($pricingResult['data'], 'Total calculated successfully');
    }

    /**
     * Get dashboard statistics
     */

    public function getDashboardStats(Request $request): JsonResponse
    {
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }

        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        // Get room types for availability
        $roomTypes = \App\Models\Roomtype::where('hotel_id', $hotel->id)->get();
        $roomAvailability = $roomTypes->map(function ($roomType) {
            return [
                'name' => $roomType->name,
                'total' => $roomType->total_inventory ?? 0,
                'available' => $roomType->total_inventory ?? 0, // Simplified - can enhance later
            ];
        });

        $stats = [
            'total_bookings' => Booking::where('hotel_id', $hotel->id)->count(),
            'pending_bookings' => Booking::where('hotel_id', $hotel->id)
                ->where('status', 'pending')
                ->count(),

            'total_rooms' => $roomTypes->sum('total_inventory'),
            'available_rooms' => $roomTypes->sum('total_inventory'), // Simplified

            'active_promotions' => \App\Models\Promotion::where('hotel_id', $hotel->id)
                ->where('is_active', true)
                ->count(),

            // ✅ Doanh thu hôm nay (total_amount đã trừ discount)
            'today_revenue' => Booking::where('hotel_id', $hotel->id)
                ->whereDate('created_at', $today)
                ->whereNotIn('status', ['cancelled'])
                ->sum('total_amount'),

            // ✅ Doanh thu tháng này (total_amount đã trừ discount)
            'month_revenue' => Booking::where('hotel_id', $hotel->id)
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->whereNotIn('status', ['cancelled'])
                ->sum('total_amount'),

            'recent_bookings' => Booking::with('bookingDetails.roomtype')
                ->where('hotel_id', $hotel->id)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(fn($booking) => $this->transformBookingForDashboard($booking)),

            'room_availability' => $roomAvailability,
        ];

        return $this->successResponse($stats, 'Dashboard stats retrieved successfully');
    }


    /**
     * Calculate booking pricing
     */
    private function calculateBookingPricing($roomType, $checkIn, $checkOut, $promotionCode = null, $promotionIdParam = null)
    {
        $nights = $checkIn->diffInDays($checkOut);
        if ($nights <= 0) {
            return ['success' => false, 'message' => 'Invalid date range'];
        }

        // Get base rate (this could be enhanced to use dynamic pricing)
        $ratePerNight = $roomType->base_price ?? 1000000; // Default 1M VND per night if not set
        $subtotal = $ratePerNight * $nights;

        // Calculate tax (use default rate if hotel relationship not loaded)
        $taxRate = 0.10; // Default 10% tax rate
        $taxAmount = $subtotal * $taxRate;

        $discountAmount = 0;
        $promotionId = null;
        $promotion = null;

        // Apply promotion if provided (either by code or ID)
        if ($promotionCode) {
            $promotion = Promotion::active()
                ->forHotel($roomType->hotel_id)
                ->byCode($promotionCode)
                ->first();

            if ($promotion) {
                $validationResult = $promotion->isValid(
                    $checkIn->toDateString(),
                    $checkOut->toDateString(),
                    $roomType->id
                );

                if ($validationResult['valid']) {
                    $discountAmount = $promotion->calculateDiscount($subtotal, $nights);
                    $promotionId = $promotion->id;
                } else {
                    return ['success' => false, 'message' => $validationResult['message']];
                }
            } else {
                return ['success' => false, 'message' => 'Invalid promotion code'];
            }
        } elseif ($promotionIdParam) {
            $promotion = Promotion::active()
                ->forHotel($roomType->hotel_id)
                ->where('id', $promotionIdParam)
                ->first();

            if ($promotion) {
                $validationResult = $promotion->isValid(
                    $checkIn->toDateString(),
                    $checkOut->toDateString(),
                    $roomType->id
                );

                if ($validationResult['valid']) {
                    $discountAmount = $promotion->calculateDiscount($subtotal, $nights);
                    $promotionId = $promotion->id;
                } else {
                    return ['success' => false, 'message' => $validationResult['message']];
                }
            } else {
                return ['success' => false, 'message' => 'Invalid promotion ID'];
            }
        }

        $totalAmount = $subtotal + $taxAmount - $discountAmount;

        return [
            'success' => true,
            'data' => [
                'nights' => $nights,
                'rate_per_night' => $ratePerNight,
                'subtotal' => $subtotal,
                'tax_rate' => $taxRate * 100,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'promotion_code' => $promotionCode,
                'promotion_id' => $promotionId,
                'promotion_title' => $promotion ? $promotion->title : null,
            ]
        ];
    }

    /**
     * Transform booking data for API response
     */
    private function transformBooking($booking, $detailed = false)
    {
        // Collect room types from booking details
        $roomTypes = [];
        $totalRoomRate = 0;
        $promotionCodes = [];

        foreach ($booking->bookingDetails as $detail) {
            if ($detail->roomType) {
                $roomTypes[] = $detail->roomType->name;
                $totalRoomRate += $detail->price_per_night * $detail->quantity;
            }
            if ($detail->promotion) {
                $promotionCodes[] = $detail->promotion->promotion_code ?? $detail->promotion->name;
            }
        }

        // Get hotel tax settings
        $hotel = $booking->hotel;
        $hotelTaxSettings = [
            'vat_rate' => $hotel->vat_rate ?? 10.00,
            'service_charge_rate' => $hotel->service_charge_rate ?? 5.00,
            'prices_include_tax' => $hotel->prices_include_tax ?? false,
        ];

        $data = [
            'id' => $booking->id,
            'booking_number' => $booking->booking_number,
            'customer_name' => trim(($booking->first_name ?? '') . ' ' . ($booking->last_name ?? '')),
            'customer_email' => $booking->email,
            'customer_phone' => $booking->phone_number,
            'customer_nationality' => $booking->nationality,
            'room_types' => implode(', ', array_unique($roomTypes)),
            'room_type_count' => count($booking->bookingDetails),
            'check_in' => $booking->check_in->toDateString(),
            'check_out' => $booking->check_out->toDateString(),
            'nights' => $booking->nights,
            'guests' => $booking->guests,
            'room_rate' => $totalRoomRate,
            'tax_amount' => $booking->tax_amount,
            'discount_amount' => $booking->discount_amount,
            'total_amount' => $booking->total_amount,
            'promotion_codes' => implode(', ', array_unique($promotionCodes)),
            'status' => $booking->status,
            'hotel_tax_settings' => $hotelTaxSettings,
            'created_at' => $booking->created_at->toISOString(),
            'updated_at' => $booking->updated_at->toISOString(),
        ];

        if ($detailed) {
            // Collect detailed room and promotion information
            $roomDetails = [];
            $promotionDetails = [];

            foreach ($booking->bookingDetails as $detail) {
                if ($detail->roomType) {
                    $roomDetails[] = [
                        'id' => $detail->roomType->id,
                        'name' => $detail->roomType->name,
                        'description' => $detail->roomType->description,
                        'quantity' => $detail->quantity,
                        'adults' => $detail->adults,
                        'children' => $detail->children,
                        'children_ages' => $detail->children_ages,
                        'price_per_night' => $detail->price_per_night,
                        'total_amount' => $detail->total_amount,
                        'is_extra_bed_requested' => $detail->is_extra_bed_requested,
                    ];
                }

                if ($detail->promotion) {
                    $promotionDetails[] = [
                        'id' => $detail->promotion->id,
                        'name' => $detail->promotion->name,
                        'promotion_code' => $detail->promotion->promotion_code,
                        'discount_amount' => $detail->discount_amount,
                    ];
                }
            }

            $data = array_merge($data, [
                'notes' => $booking->notes,
                'cancellation_reason' => $booking->cancellation_reason,
                'confirmed_at' => $booking->confirmed_at?->toISOString(),
                'cancelled_at' => $booking->cancelled_at?->toISOString(),
                'completed_at' => $booking->completed_at?->toISOString(),
                'room_details' => $roomDetails,
                'promotion_details' => $promotionDetails,
                'booking_details' => $booking->bookingDetails->toArray(),
            ]);
        }

        return $data;
    }

    /**
     * SECURITY: Validate booking integrity to prevent data tampering
     */
    private function validateBookingIntegrity($rooms, $roomTypes, $checkIn, $checkOut, $bookingDetails)
    {
        // Validate date ranges haven't been manipulated
        if ($checkIn->toDateString() < now()->toDateString()) {
            throw new \Exception('Check-in date cannot be in the past');
        }

        if ($checkOut->lte($checkIn)) {
            throw new \Exception('Check-out must be after check-in');
        }

        // Validate reasonable date range (max 30 days)
        if ($checkIn->diffInDays($checkOut) > 30) {
            throw new \Exception('Booking period cannot exceed 30 days');
        }

        // Validate guest counts
        if ($bookingDetails['adults'] < 1 || $bookingDetails['adults'] > 20) {
            throw new \Exception('Invalid number of adults');
        }

        if (($bookingDetails['children'] ?? 0) < 0 || ($bookingDetails['children'] ?? 0) > 10) {
            throw new \Exception('Invalid number of children');
        }

        // Validate room quantities
        foreach ($rooms as $roomData) {
            $quantity = (int) $roomData['quantity'];
            if ($quantity < 1 || $quantity > 10) {
                throw new \Exception('Invalid room quantity');
            }

            // Validate room capacity vs guest count
            $roomType = $roomTypes[$roomData['room_id']];
            $maxCapacity = $roomType->max_guests * $quantity;
            $totalGuests = $bookingDetails['adults'] + ($bookingDetails['children'] ?? 0);

            if ($totalGuests > $maxCapacity) {
                throw new \Exception("Room {$roomType->name} cannot accommodate {$totalGuests} guests");
            }
        }

        \Log::info('Booking integrity validation passed', [
            'check_in' => $checkIn->toDateString(),
            'check_out' => $checkOut->toDateString(),
            'guests' => $bookingDetails['adults'] + ($bookingDetails['children'] ?? 0),
            'rooms_count' => count($rooms)
        ]);
    }

    /**
     * SECURITY: Get current booked quantity for a room type
     */
    private function getBookedQuantity($roomTypeId, $checkIn, $checkOut)
    {
        return BookingDetail::whereHas('booking', function ($query) use ($checkIn, $checkOut) {
            $query->where('status', '!=', 'cancelled')
                ->where(function ($q) use ($checkIn, $checkOut) {
                    $q->whereBetween('check_in', [$checkIn, $checkOut])
                        ->orWhereBetween('check_out', [$checkIn, $checkOut])
                        ->orWhere(function ($q2) use ($checkIn, $checkOut) {
                            $q2->where('check_in', '<=', $checkIn)
                                ->where('check_out', '>=', $checkOut);
                        });
                });
        })
            ->where('room_type_id', $roomTypeId)
            ->sum('quantity');
    }

    /**
     * SECURITY: Validate promotion security
     */
    private function validatePromotionSecurity($promotionId, $roomType, $checkIn, $checkOut)
    {
        $promotion = Promotion::find($promotionId);

        if (!$promotion) {
            throw new \Exception('Promotion not found');
        }

        if (!$promotion->is_active) {
            throw new \Exception('Promotion is no longer active');
        }

        if ($promotion->hotel_id !== $roomType->hotel_id) {
            throw new \Exception('Promotion does not belong to this hotel');
        }

        // Check date validity
        $now = now();
        if ($now->lt($promotion->start_date) || $now->gt($promotion->end_date)) {
            throw new \Exception('Promotion is not valid for current date');
        }

        // Check usage limits
        if ($promotion->max_uses && $promotion->current_uses >= $promotion->max_uses) {
            throw new \Exception('Promotion usage limit exceeded');
        }

        // Check minimum stay requirements
        $nights = $checkIn->diffInDays($checkOut);
        if ($promotion->min_stay && $nights < $promotion->min_stay) {
            throw new \Exception("Promotion requires minimum {$promotion->min_stay} nights stay");
        }

        \Log::info('Promotion security validation passed', [
            'promotion_id' => $promotionId,
            'promotion_code' => $promotion->code,
            'room_type_id' => $roomType->id
        ]);
    }

    /**
     * SECURITY: Validate guest information security
     */
    private function validateGuestSecurity($guest)
    {
        // Sanitize and validate names
        $firstName = trim($guest['first_name']);
        $lastName = trim($guest['last_name']);

        if (empty($firstName) || empty($lastName)) {
            throw new \Exception('Guest name cannot be empty');
        }

        if (strlen($firstName) > 100 || strlen($lastName) > 100) {
            throw new \Exception('Guest name too long');
        }

        // Check for suspicious characters in names
        if (
            !preg_match('/^[a-zA-ZÀ-ỹ\s\-\'\.]+$/u', $firstName) ||
            !preg_match('/^[a-zA-ZÀ-ỹ\s\-\'\.]+$/u', $lastName)
        ) {
            throw new \Exception('Invalid characters in guest name');
        }

        // Validate email format
        if (!filter_var($guest['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('Invalid email format');
        }

        // Check for disposable email domains (basic protection)
        $disposableDomains = ['10minutemail.com', 'tempmail.org', 'guerrillamail.com'];
        $emailDomain = substr(strrchr($guest['email'], "@"), 1);
        if (in_array($emailDomain, $disposableDomains)) {
            throw new \Exception('Disposable email addresses are not allowed');
        }

        // Validate phone number format
        $phone = preg_replace('/[^\d+]/', '', $guest['phone']);
        if (strlen($phone) < 10 || strlen($phone) > 15) {
            throw new \Exception('Invalid phone number format');
        }

        // Validate nationality code if provided
        if (!empty($guest['nationality'])) {
            if (!preg_match('/^[A-Z]{2,3}$/', $guest['nationality'])) {
                throw new \Exception('Invalid nationality code format');
            }
        }

        // Log guest validation
        \Log::info('Guest security validation passed', [
            'email_domain' => $emailDomain,
            'phone_length' => strlen($phone),
            'nationality' => $guest['nationality'] ?? 'not_provided'
        ]);
    }

    /**
     * Get child age policy for a hotel
     */
    public function getChildAgePolicy(Request $request)
    {
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }

        try {
            $childAgePolicy = $hotel->childAgePolicy()->first();

            if (!$childAgePolicy) {
                // Return default policy
                $childAgePolicy = [
                    'hotel_id' => $hotel->id,
                    'free_age_limit' => 6,
                    'surcharge_age_limit' => 12,
                    'free_description' => null,
                    'surcharge_description' => null,
                    'is_active' => true
                ];
            }

            return $this->successResponse($childAgePolicy, 'Child age policy retrieved successfully');
        } catch (\Exception $e) {
            \Log::error('Get child age policy failed', [
                'hotel_id' => $hotel->id,
                'error' => $e->getMessage()
            ]);
            return $this->errorResponse('Failed to get child age policy: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update child age policy for a hotel
     */
    public function updateChildAgePolicy(Request $request)
    {
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }

        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
            'data.free_age_limit' => 'required|integer|min:0|max:17',
            'data.surcharge_age_limit' => 'required|integer|min:0|max:17|gt:data.free_age_limit',
            'data.free_description' => 'nullable|array',
            'data.surcharge_description' => 'nullable|array',
            'data.is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        try {
            $validatedData = $validator->validated()['data'];
            $childAgePolicy = $hotel->childAgePolicy()->updateOrCreate(
                ['hotel_id' => $hotel->id],
                $validatedData
                //$request->only(['free_age_limit', 'surcharge_age_limit', 'free_description', 'surcharge_description', 'is_active'])
            );

            return $this->successResponse($childAgePolicy, 'Child age policy updated successfully');
        } catch (\Exception $e) {
            \Log::error('Update child age policy failed', [
                'hotel_id' => $hotel->id,
                'data' => $request->all(),
                'error' => $e->getMessage()
            ]);
            return $this->errorResponse('Failed to update child age policy: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get room pricing policies for a hotel
     */
    public function getRoomPricingPolicies(Request $request)
    {
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }

        try {
            $roomTypes = $hotel->roomtypes()->with('pricingPolicy')->get();

            $pricingPolicies = $roomTypes->map(function ($roomType) {
                return [
                    'roomtype_id' => $roomType->id,
                    'roomtype_name' => $roomType->title,
                    'base_occupancy' => $roomType->pricingPolicy->base_occupancy ?? 2,
                    'additional_adult_price' => $roomType->pricingPolicy->additional_adult_price ?? 0,
                    'child_surcharge_price' => $roomType->pricingPolicy->child_surcharge_price ?? 0,
                    'is_active' => $roomType->pricingPolicy->is_active ?? true
                ];
            });

            return $this->successResponse($pricingPolicies, 'Room pricing policies retrieved successfully');
        } catch (\Exception $e) {
            \Log::error('Get room pricing policies failed', [
                'hotel_id' => $hotel->id,
                'error' => $e->getMessage()
            ]);
            return $this->errorResponse('Failed to get room pricing policies: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update room pricing policy
     */
    public function updateRoomPricingPolicy(Request $request, $roomtypeId)
    {
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }

        // Validate that roomtype belongs to this hotel
        $roomType = $hotel->roomtypes()->find($roomtypeId);
        if (!$roomType) {
            return $this->errorResponse('Room type not found', 404);
        }

        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
            'data.base_occupancy' => 'required|integer|min:1|max:10',
            'data.additional_adult_price' => 'required|numeric|min:0',
            'data.child_surcharge_price' => 'required|numeric|min:0',
            'data.is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        try {
            $validatedData = $validator->validated()['data'];
            $pricingPolicy = $roomType->pricingPolicy()->updateOrCreate(
                ['roomtype_id' => $roomtypeId],
                $validatedData
                //$request->only(['base_occupancy', 'additional_adult_price', 'child_surcharge_price', 'is_active'])
            );

            return $this->successResponse($pricingPolicy, 'Room pricing policy updated successfully');
        } catch (\Exception $e) {
            \Log::error('Update room pricing policy failed', [
                'hotel_id' => $hotel->id,
                'roomtype_id' => $roomtypeId,
                'data' => $request->all(),
                'error' => $e->getMessage()
            ]);
            return $this->errorResponse('Failed to update room pricing policy: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Transform booking for dashboard (simplified)
     */
    private function transformBookingForDashboard($booking)
    {
        // Collect room types from booking details
        $roomTypes = [];
        foreach ($booking->bookingDetails as $detail) {
            if ($detail->roomtype) {
                $roomTypes[] = $detail->roomtype->name;
            }
        }

        return [
            'id' => $booking->id,
            'customer_name' => trim(($booking->first_name ?? '') . ' ' . ($booking->last_name ?? '')),
            'room_type' => implode(', ', array_unique($roomTypes)) ?: 'N/A',
            'status' => $booking->status,
            'check_in' => $booking->check_in->toDateString(),
            'total_amount' => $booking->total_amount,
        ];
    }

    /**
     * Track booking by booking number or email
     */
    public function trackBooking(Request $request): JsonResponse
    {
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }

        $bookingNumber = $request->input('booking_number');
        $email = $request->input('email');

        if (!$bookingNumber && !$email) {
            return $this->errorResponse('Please provide booking number or email', 400);
        }

        $query = Booking::with('bookingDetails.roomtype')
            ->where('hotel_id', $hotel->id);

        if ($bookingNumber) {
            $query->where('booking_number', $bookingNumber);
        } elseif ($email) {
            $query->where('email', $email);
        }

        $booking = $query->first();

        if (!$booking) {
            return $this->errorResponse('Booking not found', 404);
        }

        return $this->successResponse($booking, 'Booking retrieved successfully');
    }

    /**
     * Cancel booking
     */
    public function cancelBooking(Request $request, $id): JsonResponse
    {
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }

        $booking = Booking::where('hotel_id', $hotel->id)
            ->where('id', $id)
            ->first();

        if (!$booking) {
            return $this->errorResponse('Booking not found', 404);
        }

        if (!$booking->canBeCancelled()) {
            return $this->errorResponse('This booking cannot be cancelled', 400);
        }

        $booking->status = 'cancelled';
        $booking->cancelled_at = now();
        $booking->cancellation_reason = $request->input('cancellation_reason', 'Cancelled by customer');
        $booking->save();

        return $this->successResponse($booking, 'Booking cancelled successfully');
    }
}
