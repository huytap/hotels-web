<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use App\Models\{RoomRate, RoomType, RateTemplate};

class RoomRateController extends BaseApiController
{
    /**
     * Get room rates
     */
    public function index(Request $request): JsonResponse
    {
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }

        $validator = Validator::make($request->all(), [
            'room_type_id' => 'nullable|exists:room_types,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'calendar_view' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 400, $validator->errors());
        }

        $query = RoomRate::with(['roomType'])
            ->where('hotel_id', $hotel->id);

        if ($request->filled('room_type_id')) {
            $query->where('room_type_id', $request->room_type_id);
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        $query->orderBy('date', 'asc');

        if ($request->get('calendar_view')) {
            // Return data optimized for calendar view
            $rates = $query->get();
            $calendarData = [];

            foreach ($rates as $rate) {
                $calendarData[$rate->date->toDateString()] = [
                    'id' => $rate->id,
                    'date' => $rate->date->toDateString(),
                    'price' => $rate->price,
                    'roomtype_name' => $rate->roomtype->name,
                ];
            }

            return $this->successResponse($calendarData);
        } else {
            // Regular paginated list
            $rates = $query->paginate($request->get('per_page', 50));

            $rates->getCollection()->transform(function ($rate) {
                return $this->transformRoomRate($rate);
            });

            return $this->paginatedResponse($rates);
        }
    }

    /**
     * Update room rate
     */
    public function update(Request $request): JsonResponse
    {
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }

        $validator = Validator::make($request->all(), [
            'room_type_id' => 'required|exists:room_types,id',
            'date' => 'required|date',
            'rate' => 'required|numeric|min:0',
            'available_rooms' => 'nullable|integer|min:0',
            'min_stay' => 'nullable|integer|min:1',
            'max_stay' => 'nullable|integer|min:1',
            'is_closed' => 'nullable|boolean',
            'close_to_arrival' => 'nullable|boolean',
            'close_to_departure' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 400, $validator->errors());
        }

        // Verify room type belongs to hotel
        $roomType = RoomType::where('id', $request->room_type_id)
            ->where('hotel_id', $hotel->id)
            ->first();

        if (!$roomType) {
            return $this->errorResponse('Room type not found', 404);
        }

        try {
            $rateData = [
                'hotel_id' => $hotel->id,
                'room_type_id' => $request->room_type_id,
                'date' => $request->date,
                'rate' => $request->rate,
                'available_rooms' => $request->get('available_rooms', $roomType->total_rooms),
                'min_stay' => $request->get('min_stay', 1),
                'max_stay' => $request->get('max_stay', 30),
                'is_closed' => $request->get('is_closed', false),
                'close_to_arrival' => $request->get('close_to_arrival', false),
                'close_to_departure' => $request->get('close_to_departure', false),
            ];

            $roomRate = RoomRate::updateOrCreate(
                [
                    'hotel_id' => $hotel->id,
                    'room_type_id' => $request->room_type_id,
                    'date' => $request->date,
                ],
                $rateData
            );

            return $this->successResponse(
                $this->transformRoomRate($roomRate->load('roomType')),
                'Room rate updated successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update room rate', 500);
        }
    }

    /**
     * Bulk update room rates
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }

        $validator = Validator::make($request->all(), [
            'room_type_id' => 'required|exists:room_types,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'rate' => 'nullable|numeric|min:0',
            'rate_adjustment' => 'nullable|array',
            'rate_adjustment.type' => 'required_with:rate_adjustment|in:percentage,fixed',
            'rate_adjustment.value' => 'required_with:rate_adjustment|numeric',
            'apply_to_weekdays' => 'nullable|array',
            'apply_to_weekdays.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'available_rooms' => 'nullable|integer|min:0',
            'min_stay' => 'nullable|integer|min:1',
            'max_stay' => 'nullable|integer|min:1',
            'is_closed' => 'nullable|boolean',
            'close_to_arrival' => 'nullable|boolean',
            'close_to_departure' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 400, $validator->errors());
        }

        // Verify room type belongs to hotel
        $roomType = RoomType::where('id', $request->room_type_id)
            ->where('hotel_id', $hotel->id)
            ->first();

        if (!$roomType) {
            return $this->errorResponse('Room type not found', 404);
        }

        DB::beginTransaction();
        try {
            $dateFrom = Carbon::parse($request->date_from);
            $dateTo = Carbon::parse($request->date_to);
            $applyToWeekdays = $request->get('apply_to_weekdays');
            $updatedCount = 0;

            while ($dateFrom->lte($dateTo)) {
                // Check if we should apply to this weekday
                if ($applyToWeekdays) {
                    $dayName = strtolower($dateFrom->format('l'));
                    if (!in_array($dayName, $applyToWeekdays)) {
                        $dateFrom->addDay();
                        continue;
                    }
                }

                // Get existing rate or create default
                $existingRate = RoomRate::where('hotel_id', $hotel->id)
                    ->where('room_type_id', $request->room_type_id)
                    ->where('date', $dateFrom->toDateString())
                    ->first();

                $updateData = [];

                // Handle rate updates
                if ($request->filled('rate')) {
                    $updateData['rate'] = $request->rate;
                } elseif ($request->filled('rate_adjustment') && $existingRate) {
                    $adjustment = $request->rate_adjustment;
                    $currentRate = $existingRate->rate;

                    if ($adjustment['type'] === 'percentage') {
                        $newRate = $currentRate * (1 + ($adjustment['value'] / 100));
                    } else { // fixed
                        $newRate = $currentRate + $adjustment['value'];
                    }

                    $updateData['rate'] = max(0, $newRate);
                }

                // Handle other fields
                if ($request->has('available_rooms')) {
                    $updateData['available_rooms'] = $request->available_rooms;
                }
                if ($request->has('min_stay')) {
                    $updateData['min_stay'] = $request->min_stay;
                }
                if ($request->has('max_stay')) {
                    $updateData['max_stay'] = $request->max_stay;
                }
                if ($request->has('is_closed')) {
                    $updateData['is_closed'] = $request->is_closed;
                }
                if ($request->has('close_to_arrival')) {
                    $updateData['close_to_arrival'] = $request->close_to_arrival;
                }
                if ($request->has('close_to_departure')) {
                    $updateData['close_to_departure'] = $request->close_to_departure;
                }

                if (!empty($updateData)) {
                    RoomRate::updateOrCreate(
                        [
                            'hotel_id' => $hotel->id,
                            'room_type_id' => $request->room_type_id,
                            'date' => $dateFrom->toDateString(),
                        ],
                        array_merge([
                            'rate' => $existingRate ? $existingRate->rate : $roomType->base_price,
                            'available_rooms' => $existingRate ? $existingRate->available_rooms : $roomType->total_rooms,
                            'min_stay' => 1,
                            'max_stay' => 30,
                            'is_closed' => false,
                            'close_to_arrival' => false,
                            'close_to_departure' => false,
                        ], $updateData)
                    );

                    $updatedCount++;
                }

                $dateFrom->addDay();
            }

            DB::commit();

            return $this->successResponse(
                ['updated_count' => $updatedCount],
                "Successfully updated {$updatedCount} rate(s)"
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to bulk update rates', 500);
        }
    }

    /**
     * Toggle room availability
     */
    public function toggleAvailability(Request $request): JsonResponse
    {
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }

        $validator = Validator::make($request->all(), [
            'room_type_id' => 'required|exists:room_types,id',
            'date' => 'required|date',
            'is_closed' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 400, $validator->errors());
        }

        // Verify room type belongs to hotel
        $roomType = RoomType::where('id', $request->room_type_id)
            ->where('hotel_id', $hotel->id)
            ->first();

        if (!$roomType) {
            return $this->errorResponse('Room type not found', 404);
        }

        try {
            $roomRate = RoomRate::updateOrCreate(
                [
                    'hotel_id' => $hotel->id,
                    'room_type_id' => $request->room_type_id,
                    'date' => $request->date,
                ],
                [
                    'rate' => $roomType->base_price,
                    'available_rooms' => $request->is_closed ? 0 : $roomType->total_rooms,
                    'is_closed' => $request->is_closed,
                ]
            );

            return $this->successResponse(
                $this->transformRoomRate($roomRate->load('roomType')),
                'Room availability updated successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update availability', 500);
        }
    }

    /**
     * Copy rates from one period to another
     */
    public function copyRates(Request $request): JsonResponse
    {
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }

        $validator = Validator::make($request->all(), [
            'room_type_id' => 'required|exists:room_types,id',
            'source_date_from' => 'required|date',
            'source_date_to' => 'required|date|after_or_equal:source_date_from',
            'target_date_from' => 'required|date',
            'target_date_to' => 'required|date|after_or_equal:target_date_from',
            'copy_rates' => 'nullable|boolean',
            'copy_availability' => 'nullable|boolean',
            'copy_restrictions' => 'nullable|boolean',
            'overwrite_existing' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 400, $validator->errors());
        }

        // Verify room type belongs to hotel
        $roomType = RoomType::where('id', $request->room_type_id)
            ->where('hotel_id', $hotel->id)
            ->first();

        if (!$roomType) {
            return $this->errorResponse('Room type not found', 404);
        }

        DB::beginTransaction();
        try {
            $sourceFrom = Carbon::parse($request->source_date_from);
            $sourceTo = Carbon::parse($request->source_date_to);
            $targetFrom = Carbon::parse($request->target_date_from);
            $targetTo = Carbon::parse($request->target_date_to);

            // Get source rates
            $sourceRates = RoomRate::where('hotel_id', $hotel->id)
                ->where('room_type_id', $request->room_type_id)
                ->whereBetween('date', [$sourceFrom, $sourceTo])
                ->get()
                ->keyBy('date');

            $copiedCount = 0;
            $currentTarget = $targetFrom->copy();
            $currentSource = $sourceFrom->copy();

            while ($currentTarget->lte($targetTo) && $currentSource->lte($sourceTo)) {
                $sourceRate = $sourceRates->get($currentSource->toDateString());

                if ($sourceRate) {
                    $targetDateStr = $currentTarget->toDateString();

                    // Check if target date already has a rate
                    $existingRate = RoomRate::where('hotel_id', $hotel->id)
                        ->where('room_type_id', $request->room_type_id)
                        ->where('date', $targetDateStr)
                        ->first();

                    if (!$existingRate || $request->get('overwrite_existing', false)) {
                        $copyData = [
                            'hotel_id' => $hotel->id,
                            'room_type_id' => $request->room_type_id,
                            'date' => $targetDateStr,
                        ];

                        // Copy rates
                        if ($request->get('copy_rates', true)) {
                            $copyData['rate'] = $sourceRate->rate;
                        }

                        // Copy availability
                        if ($request->get('copy_availability', true)) {
                            $copyData['available_rooms'] = $sourceRate->available_rooms;
                            $copyData['is_closed'] = $sourceRate->is_closed;
                        }

                        // Copy restrictions
                        if ($request->get('copy_restrictions', true)) {
                            $copyData['min_stay'] = $sourceRate->min_stay;
                            $copyData['max_stay'] = $sourceRate->max_stay;
                            $copyData['close_to_arrival'] = $sourceRate->close_to_arrival;
                            $copyData['close_to_departure'] = $sourceRate->close_to_departure;
                        }

                        RoomRate::updateOrCreate(
                            [
                                'hotel_id' => $hotel->id,
                                'room_type_id' => $request->room_type_id,
                                'date' => $targetDateStr,
                            ],
                            $copyData
                        );

                        $copiedCount++;
                    }
                }

                $currentTarget->addDay();
                $currentSource->addDay();
            }

            DB::commit();

            return $this->successResponse(
                ['copied_count' => $copiedCount],
                "Successfully copied {$copiedCount} rate(s)"
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to copy rates', 500);
        }
    }

    /**
     * Get rate templates
     */
    public function getTemplates(Request $request): JsonResponse
    {
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }

        $templates = RateTemplate::with('roomType')
            ->where('hotel_id', $hotel->id)
            ->orderBy('name')
            ->get();

        $transformedTemplates = $templates->map(function ($template) {
            return [
                'id' => $template->id,
                'name' => $template->name,
                'description' => $template->description,
                'room_type_id' => $template->room_type_id,
                'room_type_name' => $template->roomType->name,
                'rates' => $template->getWeeklyRates(),
                'min_stay' => $template->min_stay,
                'max_stay' => $template->max_stay,
                'is_active' => $template->is_active,
                'created_at' => $template->created_at->toISOString(),
            ];
        });

        return $this->successResponse($transformedTemplates);
    }

    /**
     * Transform room rate data
     */
    private function transformRoomRate($roomRate)
    {
        return [
            'id' => $roomRate->id,
            'roomtype_id' => $roomRate->roomtype_id,
            'roomtype_name' => $roomRate->roomtype ? $roomRate->roomtype->title : null,
            'date' => $roomRate->date->toDateString(),
            'price' => $roomRate->price,
            'updated_at' => $roomRate->updated_at->toISOString(),
        ];
    }
}
