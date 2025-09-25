<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RoomManagementService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class RoomManagementController extends BaseApiController
{
    protected $roomService;

    public function __construct(RoomManagementService $roomService)
    {
        $this->roomService = $roomService;
    }

    /**
     * Lấy dữ liệu calendar (rates + inventory)
     */
    public function getCalendarData(Request $request): JsonResponse
    {
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }

        $validator = Validator::make($request->all(), [
            'roomtype_id' => 'required|integer|exists:roomtypes,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 400, $validator->errors());
        }

        try {
            $data = $this->roomService->getCalendarData(
                $hotel->id,
                $request->roomtype_id,
                $request->start_date,
                $request->end_date
            );

            return $this->successResponse($data);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get calendar data', 500);
        }
    }

    /**
     * Cập nhật giá phòng
     */
    public function updateRate(Request $request): JsonResponse
    {
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }

        $validator = Validator::make($request->all(), [
            'roomtype_id' => 'required|integer|exists:roomtypes,id',
            'date' => 'required|date',
            'price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 400, $validator->errors());
        }

        try {
            $rate = $this->roomService->updateRate(
                $hotel->id,
                $request->roomtype_id,
                $request->date,
                $request->price
            );

            return $this->successResponse($rate, 'Rate updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update rate', 500);
        }
    }

    /**
     * Cập nhật inventory
     */
    public function updateInventory(Request $request): JsonResponse
    {
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }

        $validator = Validator::make($request->all(), [
            'roomtype_id' => 'required|integer|exists:roomtypes,id',
            'date' => 'required|date',
            'total_for_sale' => 'required|integer|min:0',
            'is_available' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 400, $validator->errors());
        }

        try {
            $inventoryData = [
                'total_for_sale' => $request->total_for_sale,
                'is_available' => $request->get('is_available', true),
            ];

            if ($request->has('booked_rooms')) {
                $inventoryData['booked_rooms'] = max(0, min($request->booked_rooms, $request->total_for_sale));
            }

            $inventory = $this->roomService->updateInventory(
                $hotel->id,
                $request->roomtype_id,
                $request->date,
                $inventoryData
            );

            return $this->successResponse($inventory, 'Inventory updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update inventory', 500);
        }
    }

    /**
     * Cập nhật cả rate và inventory cùng lúc
     */
    public function updateBoth(Request $request): JsonResponse
    {
        // Validate hotel access
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }

        $requestData = $request->json('data', []);

        if (!is_array($requestData)) {
            return $this->errorResponse('Invalid data format. "data" key is missing or invalid.', 400);
        }

        // Add date_to to validation rules
        $validator = Validator::make($requestData, [
            'roomtype_id' => 'required|integer|exists:roomtypes,id',
            'date' => 'required|date',
            'date_to' => 'nullable|date|after_or_equal:date',
            'price' => 'nullable|numeric|min:0',
            'total_for_sale' => 'nullable|integer|min:0',
            'is_available' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 400, $validator->errors());
        }

        try {
            $startDate = \Carbon\Carbon::parse($requestData['date']);
            $endDate = array_key_exists('date_to', $requestData) && $requestData['date_to'] ?
                \Carbon\Carbon::parse($requestData['date_to']) :
                clone $startDate;

            $results = [];

            // Loop through the date range
            for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
                $rateData = [];
                $inventoryData = [];

                // Populate rate and inventory data for the current day
                if (array_key_exists('price', $requestData)) {
                    $rateData['price'] = $requestData['price'];
                }
                if (array_key_exists('total_for_sale', $requestData)) {
                    $inventoryData['total_for_sale'] = $requestData['total_for_sale'];
                }
                if (array_key_exists('is_available', $requestData)) {
                    $inventoryData['is_available'] = $requestData['is_available'];
                }

                // Call the service for each day
                $results[] = $this->roomService->updateRateAndInventory(
                    $hotel->id,
                    $requestData['roomtype_id'],
                    $date->format('Y-m-d'), // Use formatted date
                    $rateData,
                    $inventoryData
                );
            }

            return $this->successResponse($results, 'Rates and inventory updated successfully for the specified range');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update rates and inventory', 500);
        }
    }

    /**
     * Bulk update rates
     */
    public function bulkUpdateRates(Request $request): JsonResponse
    {
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }
        $requestData = $request->json('data', []);
        $validator = Validator::make($requestData, [
            'roomtype_id' => 'required|integer|exists:roomtypes,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'price' => 'required|numeric|min:0',
            //'weekdays' => 'nullable|array',
            //'weekdays.*' => 'integer|min:0|max:6',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 400, $validator->errors());
        }

        try {
            $updatedCount = $this->roomService->bulkUpdateRates(
                $hotel->id,
                $request->roomtype_id,
                $request->start_date,
                $request->end_date,
                $request->price,
                //$request->weekdays
            );

            return $this->successResponse(
                ['updated_count' => $updatedCount],
                "Successfully updated {$updatedCount} rates"
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to bulk update rates', 500);
        }
    }

    /**
     * Bulk update inventory
     */
    public function bulkUpdateInventory(Request $request): JsonResponse
    {
        // Validate hotel access
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }

        $requestData = $request->json('data', []);

        if (!is_array($requestData)) {
            return $this->errorResponse('Invalid data format. "data" key is missing or invalid.', 400);
        }

        // Add date_to to validation rules
        $validator = Validator::make($requestData, [
            'roomtype_id' => 'required|integer|exists:roomtypes,id',
            'date' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_to',
            'total_for_sale' => 'nullable|integer|min:0',
            'is_available' => 'nullable|boolean',
            //'weekdays' => 'nullable|array',
            //'weekdays.*' => 'integer|min:0|max:6',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 400, $validator->errors());
        }

        try {
            $data = [];

            if (array_key_exists('total_for_sale', $requestData)) {
                $inventoryData['total_for_sale'] = $requestData['total_for_sale'];
            }
            if (array_key_exists('is_available', $requestData)) {
                $inventoryData['is_available'] = $requestData['is_available'];
            }

            $updatedCount = $this->roomService->updateInventory(
                $hotel->id,
                $requestData['roomtype_id'],
                $request->date,
                $request->date_to,
                $data,
                //$request->weekdays
            );

            return $this->successResponse([
                'updated_count' => $updatedCount
            ], "Successfully updated {$updatedCount} inventory records");
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk update inventory',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Copy rates từ period này sang period khác
     */
    public function copyRates(Request $request): JsonResponse
    {
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }

        $validator = Validator::make($request->all(), [
            'roomtype_id' => 'required|integer|exists:roomtypes,id',
            'source_start' => 'required|date',
            'source_end' => 'required|date|after_or_equal:source_start',
            'target_start' => 'required|date',
            'target_end' => 'required|date|after_or_equal:target_start',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 400, $validator->errors());
        }

        try {
            $copiedCount = $this->roomService->copyRates(
                $hotel->id,
                $request->roomtype_id,
                $request->source_start,
                $request->source_end,
                $request->target_start,
                $request->target_end
            );

            return $this->successResponse([
                'copied_count' => $copiedCount
            ], "Successfully copied {$copiedCount} rates");
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to copy rates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Copy inventory từ period này sang period khác
     */
    public function copyInventory(Request $request): JsonResponse
    {
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }

        $validator = Validator::make($request->all(), [
            'roomtype_id' => 'required|integer|exists:roomtypes,id',
            'source_start' => 'required|date',
            'source_end' => 'required|date|after_or_equal:source_start',
            'target_start' => 'required|date',
            'target_end' => 'required|date|after_or_equal:target_start',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 400, $validator->errors());
        }

        try {
            $copiedCount = $this->roomService->copyInventory(
                $hotel->id,
                $request->roomtype_id,
                $request->source_start,
                $request->source_end,
                $request->target_start,
                $request->target_end
            );

            return $this->successResponse([
                'copied_count' => $copiedCount
            ], "Successfully copied {$copiedCount} inventory records");
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to copy inventory',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy thống kê
     */
    public function getStatistics(Request $request): JsonResponse
    {
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }

        $validator = Validator::make($request->all(), [
            'roomtype_id' => 'required|integer|exists:roomtypes,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 400, $validator->errors());
        }

        try {
            $statistics = $this->roomService->getStatistics(
                $hotel->id,
                $request->roomtype_id,
                $request->start_date,
                $request->end_date
            );

            return $this->successResponse($statistics);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Đặt phòng
     */
    public function bookRoom(Request $request): JsonResponse
    {
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }

        $validator = Validator::make($request->all(), [
            'roomtype_id' => 'required|integer|exists:roomtypes,id',
            'date' => 'required|date',
            'quantity' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 400, $validator->errors());
        }

        try {
            $result = $this->roomService->bookRoom(
                $hotel->id,
                $request->roomtype_id,
                $request->date,
                $request->get('quantity', 1)
            );

            return $this->successResponse([
                'booked' => $result
            ], 'Room booked successfully');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to book room',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Hủy đặt phòng
     */
    public function cancelBooking(Request $request): JsonResponse
    {
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }

        $validator = Validator::make($request->all(), [
            'roomtype_id' => 'required|integer|exists:roomtypes,id',
            'date' => 'required|date',
            'quantity' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 400, $validator->errors());
        }

        try {
            $result = $this->roomService->cancelBooking(
                $hotel->id,
                $request->roomtype_id,
                $request->date,
                $request->get('quantity', 1)
            );

            return $this->successResponse([
                'cancelled' => $result
            ], 'Booking cancelled successfully');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
