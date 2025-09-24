<?php

namespace App\Http\Controllers\Api;

use App\Models\Booking;
use App\Models\BookingDetail;
use Illuminate\Http\Request;
use App\Helpers\HotelHelper;
use Illuminate\Support\Facades\Validator;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;

class BookingController extends BaseApiController
{
    protected $bookingService;
    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }
    /**
     * Lấy danh sách bookings cho một khách sạn.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    // public function index(Request $request)
    // {
    //     $wpId = $request->query('wp_id');

    //     if (!$wpId) {
    //         return response()->json(['error' => 'wp_id is required.'], 400);
    //     }

    //     $hotelId = HotelHelper::getHotelIdByWpId($wpId);

    //     if (!$hotelId) {
    //         return response()->json(['error' => 'Invalid wp_id. Hotel not found.'], 404);
    //     }

    //     $bookings = Booking::with('bookingDetails.roomtype')
    //         ->where('hotel_id', $hotelId)
    //         ->orderBy('created_at', 'desc')
    //         ->get();

    //     return response()->json($bookings);
    // }

    // /**
    //  * Lấy chi tiết của một booking cụ thể.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @param  \App\Models\Booking  $booking
    //  * @return \Illuminate\Http\JsonResponse
    //  */
    // public function show(Request $request, Booking $booking)
    // {
    //     $wpId = $request->query('wp_id');

    //     if (!$wpId) {
    //         return response()->json(['error' => 'wp_id is required.'], 400);
    //     }

    //     $hotelId = HotelHelper::getHotelIdByWpId($wpId);

    //     // Kiểm tra quyền sở hữu: booking có thuộc về khách sạn đó không
    //     if (!$hotelId || $booking->hotel_id !== $hotelId) {
    //         return response()->json(['error' => 'Booking not found or does not belong to this hotel.'], 404);
    //     }

    //     // Tải các mối quan hệ chi tiết của booking
    //     $booking->load('bookingDetails.roomtype');

    //     return response()->json($booking);
    // }
    /**
     * Tìm các tổ hợp phòng còn trống đáp ứng đủ số lượng người.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableRoomCombinations(Request $request)
    {
        $wpData = $request->json()->all();
        // 1. Xác thực dữ liệu đầu vào
        $validator = Validator::make($request->all(), [
            'params' => 'required|array',
            'params.check_in' => 'required|date|after_or_equal:today',
            'params.check_out' => 'required|date|after_or_equal:check_in',
            'params.adults' => 'required|integer|min:1',
            'params.children' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $hotelId = HotelHelper::getHotelIdByWpId($wpData['wp_id']);
        if (!$hotelId) {
            return response()->json(['error' => 'wp_id không hợp lệ. Không tìm thấy khách sạn.'], 404);
        }

        try {
            // 2. Gọi service để xử lý logic chính
            $params = $wpData['params'];
            $data = $this->bookingService->findRoomCombinations(
                $hotelId,
                $params['check_in'],
                $params['check_out'],
                $params['adults'],
                $params['children']
            );

            // 3. Trả về kết quả
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred.' . $e->getMessage()], 500);
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

        $query = Booking::with(['roomType', 'promotion'])
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

        $booking = Booking::with(['roomType', 'promotion', 'promotionUsage'])
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
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_nationality' => 'nullable|string|max:3',
            'room_type_id' => 'required|exists:room_types,id',
            'check_in' => 'required|date|after:today',
            'check_out' => 'required|date|after:check_in',
            'guests' => 'required|integer|min:1|max:10',
            'promotion_code' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
            'special_requests' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 400, $validator->errors());
        }

        // Verify room type belongs to hotel
        $roomType = RoomType::where('id', $request->room_type_id)
            ->where('hotel_id', $hotel->id)
            ->where('is_active', true)
            ->first();

        if (!$roomType) {
            return $this->errorResponse('Room type not found or inactive', 404);
        }

        // Check availability
        $checkIn = Carbon::parse($request->check_in);
        $checkOut = Carbon::parse($request->check_out);

        if (!$roomType->isAvailable($checkIn->toDateString(), $checkOut->toDateString(), $request->guests)) {
            return $this->errorResponse('Room type not available for selected dates', 422);
        }

        DB::beginTransaction();
        try {
            // Calculate pricing
            $pricingResult = $this->calculateBookingPricing($roomType, $checkIn, $checkOut, $request->promotion_code);

            if (!$pricingResult['success']) {
                return $this->errorResponse($pricingResult['message'], 422);
            }

            $pricing = $pricingResult['data'];

            // Create booking
            $bookingData = [
                'hotel_id' => $hotel->id,
                'room_type_id' => $roomType->id,
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
                'customer_nationality' => $request->customer_nationality,
                'check_in' => $checkIn->toDateString(),
                'check_out' => $checkOut->toDateString(),
                'nights' => $pricing['nights'],
                'guests' => $request->guests,
                'room_rate' => $pricing['rate_per_night'],
                'subtotal' => $pricing['subtotal'],
                'tax_amount' => $pricing['tax_amount'],
                'discount_amount' => $pricing['discount_amount'],
                'total_amount' => $pricing['total_amount'],
                'promotion_code' => $pricing['promotion_code'],
                'promotion_id' => $pricing['promotion_id'],
                'status' => 'pending',
                'notes' => $request->notes,
                'special_requests' => $request->special_requests,
            ];

            $booking = Booking::create($bookingData);

            DB::commit();

            return $this->successResponse(
                $this->transformBooking($booking->load(['roomType', 'promotion'])),
                'Booking created successfully',
                201
            );
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Booking creation failed', ['error' => $e->getMessage(), 'request' => $request->all()]);
            return $this->errorResponse('Failed to create booking', 500);
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
                $this->transformBooking($booking->load(['roomType', 'promotion'])),
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

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,confirmed,cancelled,completed,no_show',
            'cancellation_reason' => 'required_if:status,cancelled|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 400, $validator->errors());
        }

        $newStatus = $request->status;

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
                $this->transformBooking($booking->load(['roomType', 'promotion'])),
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

        $validator = Validator::make($request->all(), [
            'room_type_id' => 'required|exists:room_types,id',
            'check_in' => 'required|date|after:today',
            'check_out' => 'required|date|after:check_in',
            'guests' => 'required|integer|min:1',
            'promotion_code' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 400, $validator->errors());
        }

        $roomType = RoomType::where('id', $request->room_type_id)
            ->where('hotel_id', $hotel->id)
            ->first();

        if (!$roomType) {
            return $this->errorResponse('Room type not found', 404);
        }

        $checkIn = Carbon::parse($request->check_in);
        $checkOut = Carbon::parse($request->check_out);

        $pricingResult = $this->calculateBookingPricing($roomType, $checkIn, $checkOut, $request->promotion_code);

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
        $thisMonth = now()->format('Y-m');

        $stats = [
            'total_bookings' => Booking::where('hotel_id', $hotel->id)->count(),
            'pending_bookings' => Booking::where('hotel_id', $hotel->id)
                ->where('status', 'pending')
                ->count(),

            // ✅ Doanh thu hôm nay từ bảng booking_details
            'today_revenue' => BookingDetail::whereHas('booking', function ($q) use ($hotel, $today) {
                $q->where('hotel_id', $hotel->id)
                    ->whereDate('created_at', $today)
                    ->whereNotIn('status', ['cancelled']);
            })
                ->sum('sub_total'),

            // ✅ Doanh thu tháng này từ bảng booking_details
            'month_revenue' => BookingDetail::whereHas('booking', function ($q) use ($hotel, $thisMonth) {
                $q->where('hotel_id', $hotel->id)
                    ->where('created_at', 'like', $thisMonth . '%')
                    ->whereNotIn('status', ['cancelled']);
            })
                ->sum('sub_total'),

            'recent_bookings' => Booking::with('roomType')
                ->where('hotel_id', $hotel->id)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(fn($booking) => $this->transformBooking($booking)),
        ];

        return $this->successResponse($stats, 'Dashboard stats retrieved successfully');
    }


    /**
     * Calculate booking pricing
     */
    private function calculateBookingPricing($roomType, $checkIn, $checkOut, $promotionCode = null)
    {
        $nights = $checkIn->diffInDays($checkOut);
        if ($nights <= 0) {
            return ['success' => false, 'message' => 'Invalid date range'];
        }

        // Get base rate (this could be enhanced to use dynamic pricing)
        $ratePerNight = $roomType->base_price;
        $subtotal = $ratePerNight * $nights;

        // Calculate tax
        $taxRate = $roomType->hotel->getSetting('tax_rate', 10) / 100;
        $taxAmount = $subtotal * $taxRate;

        $discountAmount = 0;
        $promotionId = null;
        $promotion = null;

        // Apply promotion if provided
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
        $data = [
            'id' => $booking->id,
            'booking_number' => $booking->booking_number,
            'customer_name' => $booking->customer_name,
            'customer_email' => $booking->customer_email,
            'customer_phone' => $booking->customer_phone,
            'customer_nationality' => $booking->customer_nationality,
            'room_type' => $booking->roomType ? $booking->roomType->name : null,
            'room_type_id' => $booking->room_type_id,
            'check_in' => $booking->check_in->toDateString(),
            'check_out' => $booking->check_out->toDateString(),
            'nights' => $booking->nights,
            'guests' => $booking->guests,
            'room_rate' => $booking->room_rate,
            'subtotal' => $booking->subtotal,
            'tax_amount' => $booking->tax_amount,
            'discount_amount' => $booking->discount_amount,
            'total_amount' => $booking->total_amount,
            'promotion_code' => $booking->promotion_code,
            'status' => $booking->status,
            'created_at' => $booking->created_at->toISOString(),
            'updated_at' => $booking->updated_at->toISOString(),
        ];

        if ($detailed) {
            $data = array_merge($data, [
                'notes' => $booking->notes,
                'special_requests' => $booking->special_requests,
                'cancellation_reason' => $booking->cancellation_reason,
                'confirmed_at' => $booking->confirmed_at?->toISOString(),
                'cancelled_at' => $booking->cancelled_at?->toISOString(),
                'completed_at' => $booking->completed_at?->toISOString(),
                'promotion' => $booking->promotion ? [
                    'id' => $booking->promotion->id,
                    'title' => $booking->promotion->title,
                    'discount_type' => $booking->promotion->discount_type,
                    'discount_value' => $booking->promotion->discount_value,
                ] : null,
                'room_type_details' => $booking->roomType ? [
                    'id' => $booking->roomType->id,
                    'name' => $booking->roomType->name,
                    'description' => $booking->roomType->description,
                    'max_guests' => $booking->roomType->max_guests,
                    'amenities' => $booking->roomType->amenities,
                ] : null,
            ]);
        }

        return $data;
    }
}
