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
            'date_to' => 'required|date|after_or_equal:date',  // Fixed: should reference 'date' not 'date_to'
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
                $data['total_for_sale'] = $requestData['total_for_sale'];  // Fixed variable name
            }
            if (array_key_exists('is_available', $requestData)) {
                $data['is_available'] = $requestData['is_available'];  // Fixed variable name
            }

            $updatedCount = $this->roomService->bulkUpdateInventory(  // Fixed method name
                $hotel->id,
                $requestData['roomtype_id'],
                $requestData['date'],  // Fixed variable reference
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

    /**
     * Lấy danh sách templates
     */
    public function getTemplates(Request $request): JsonResponse
    {
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }

        try {
            $roomtypeId = $request->get('roomtype_id');
            $templates = $this->roomService->getTemplates($hotel->id, $roomtypeId);

            return $this->successResponse($templates);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get templates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tạo template mới
     */
    public function createTemplate(Request $request): JsonResponse
    {
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }

        $requestData = $request->json('data', $request->all());

        if (!is_array($requestData)) {
            return $this->errorResponse('Invalid data format. Expected array data.', 400);
        }

        $validator = Validator::make($requestData, [
            'roomtype_id' => 'required|integer|exists:roomtypes,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'rates' => 'required|array',
            'rates.monday' => 'required|numeric|min:0|max:99999999',
            'rates.tuesday' => 'required|numeric|min:0|max:99999999',
            'rates.wednesday' => 'required|numeric|min:0|max:99999999',
            'rates.thursday' => 'required|numeric|min:0|max:99999999',
            'rates.friday' => 'required|numeric|min:0|max:99999999',
            'rates.saturday' => 'required|numeric|min:0|max:99999999',
            'rates.sunday' => 'required|numeric|min:0|max:99999999',
            'min_stay' => 'nullable|integer|min:1|max:365',
            'max_stay' => 'nullable|integer|min:1|max:365|gte:min_stay',
            'is_active' => 'nullable|boolean',
            // Restrictions
            'close_to_arrival' => 'nullable|boolean',
            'close_to_departure' => 'nullable|boolean',
            'is_closed' => 'nullable|boolean',
        ]);
        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 400, $validator->errors());
        }

        try {
            $template = $this->roomService->createTemplate($hotel->id, $requestData);

            return $this->successResponse($template, 'Template created successfully');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create template',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy thông tin template
     */
    public function getTemplate(Request $request, $id): JsonResponse
    {
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }

        try {
            $template = $this->roomService->getTemplate($hotel->id, $id);

            return $this->successResponse($template);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Template not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Cập nhật template
     */
    public function updateTemplate(Request $request, $id): JsonResponse
    {
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }

        $requestData = $request->json('data', $request->all());

        if (!is_array($requestData)) {
            return $this->errorResponse('Invalid data format. Expected array data.', 400);
        }

        $validator = Validator::make($requestData, [
            'roomtype_id' => 'nullable|integer|exists:roomtypes,id',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'rates' => 'nullable|array',
            'rates.monday' => 'nullable|numeric|min:0|max:99999999',
            'rates.tuesday' => 'nullable|numeric|min:0|max:99999999',
            'rates.wednesday' => 'nullable|numeric|min:0|max:99999999',
            'rates.thursday' => 'nullable|numeric|min:0|max:99999999',
            'rates.friday' => 'nullable|numeric|min:0|max:99999999',
            'rates.saturday' => 'nullable|numeric|min:0|max:99999999',
            'rates.sunday' => 'nullable|numeric|min:0|max:99999999',
            'min_stay' => 'nullable|integer|min:1|max:365',
            'max_stay' => 'nullable|integer|min:1|max:365|gte:min_stay',
            'is_active' => 'nullable|boolean',
            // Restrictions
            'close_to_arrival' => 'nullable|boolean',
            'close_to_departure' => 'nullable|boolean',
            'is_closed' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 400, $validator->errors());
        }

        try {
            $template = $this->roomService->updateTemplate($hotel->id, $id, $requestData);

            return $this->successResponse($template, 'Template updated successfully');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update template',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xóa template
     */
    public function deleteTemplate(Request $request, $id): JsonResponse
    {
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }

        try {
            $this->roomService->deleteTemplate($hotel->id, $id);

            return $this->successResponse(null, 'Template deleted successfully');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete template',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Áp dụng template
     */
    public function applyTemplate(Request $request): JsonResponse
    {
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }

        $requestData = $request->json('data', $request->all());

        if (!is_array($requestData)) {
            return $this->errorResponse('Invalid data format. Expected array data.', 400);
        }

        $validator = Validator::make($requestData, [
            'template_id' => 'required|integer|exists:rate_templates,id',
            'roomtype_id' => 'required|integer|exists:roomtypes,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'overwrite_existing' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 400, $validator->errors());
        }

        try {
            $appliedCount = $this->roomService->applyTemplate(
                $hotel->id,
                $requestData['template_id'],
                $requestData['roomtype_id'],
                $requestData['date_from'],
                $requestData['date_to'],
                $requestData['overwrite_existing'] ?? false
            );

            return $this->successResponse([
                'applied_count' => $appliedCount
            ], "Successfully applied template to {$appliedCount} dates");
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to apply template',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Copy all data (rates, inventory, restrictions) trong một API call
     */
    public function copyAll(Request $request): JsonResponse
    {
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }

        $requestData = $request->json('data', $request->all());

        if (!is_array($requestData)) {
            return $this->errorResponse('Invalid data format. Expected array data.', 400);
        }

        $validator = Validator::make($requestData, [
            'roomtype_id' => 'required|integer|exists:roomtypes,id',
            'source_start_date' => 'required|date',
            'source_end_date' => 'required|date|after_or_equal:source_start_date',
            'target_start_date' => 'required|date',
            'target_end_date' => 'required|date|after_or_equal:target_start_date',
            'copy_rates' => 'nullable|boolean',
            'copy_availability' => 'nullable|boolean',
            'copy_restrictions' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 400, $validator->errors());
        }

        // Validate at least one copy option is selected
        $copyOptions = [
            'copy_rates' => boolval($requestData['copy_rates']) ?? false,
            'copy_availability' => boolval($requestData['copy_availability']) ?? false,
            'copy_restrictions' => boolval($requestData['copy_restrictions']) ?? false,
        ];

        if (!$copyOptions['copy_rates'] && !$copyOptions['copy_availability'] && !$copyOptions['copy_restrictions']) {
            return $this->errorResponse('At least one copy option must be selected', 400);
        }
        try {
            $results = $this->roomService->copyAll(
                $hotel->id,
                $requestData['roomtype_id'],
                $requestData['source_start_date'],
                $requestData['source_end_date'],
                $requestData['target_start_date'],
                $requestData['target_end_date'],
                $copyOptions
            );

            $message = [];
            if ($results['rates_copied'] > 0) {
                $message[] = "{$results['rates_copied']} giá phòng";
            }
            if ($results['inventory_copied'] > 0) {
                $message[] = "{$results['inventory_copied']} tình trạng phòng";
            }
            if ($results['restrictions_copied'] > 0) {
                $message[] = "{$results['restrictions_copied']} hạn chế";
            }

            $successMessage = count($message) > 0
                ? "Successfully copied " . implode(', ', $message)
                : "Copy operation completed";

            return $this->successResponse($results, $successMessage);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to copy data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
