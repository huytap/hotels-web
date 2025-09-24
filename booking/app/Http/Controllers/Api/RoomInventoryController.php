<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RoomInventory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Helpers\HotelHelper;

class RoomInventoryController extends Controller
{
    /**
     * Lấy dữ liệu tồn kho phòng theo bộ lọc.
     * GET /api/room-inventories?hotel_id=1&roomtype_id=1&start_date=2025-10-25
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $inventories = RoomInventory::query();

        if ($request->has('hotel_id')) {
            $inventories->where('hotel_id', $request->input('hotel_id'));
        }
        if ($request->has('roomtype_id')) {
            $inventories->where('roomtype_id', $request->input('roomtype_id'));
        }
        if ($request->has('start_date')) {
            $inventories->where('date', '>=', $request->input('start_date'));
        }
        if ($request->has('end_date')) {
            $inventories->where('date', '<=', $request->input('end_date'));
        }

        return response()->json($inventories->get());
    }

    /**
     * Cập nhật hoặc tạo mới một bản ghi tồn kho phòng.
     * POST /api/room-inventories
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeOrUpdate(Request $request)
    {
        $wpData = $request->json()->all();

        // 1. Xác thực dữ liệu đầu vào. Sử dụng wildcard '*' để validate từng phần tử trong mảng.
        $validator = Validator::make($request->all(), [
            'inventories' => 'required|array',
            'inventories.*.roomtype_id' => 'required|exists:roomtypes,id',
            'inventories.*.date' => 'required|date',
            'inventories.*.total_for_sale' => 'required|integer|min:0',
            'inventories.*.is_available' => 'boolean',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $hotelId = HotelHelper::getHotelIdByWpId($wpData['wp_id']);
        if (!$hotelId) {
            return response()->json(['error' => 'wp_id không hợp lệ. Không tìm thấy khách sạn.'], 404);
        }
        try {
            // Bắt đầu một database transaction
            DB::beginTransaction();

            $updatedRecords = [];

            // 2. Lặp qua từng bản ghi trong mảng và updateOrCreate
            $inventories = $wpData['inventories'];
            foreach ($inventories as $inventoryData) {
                $inventory = RoomInventory::updateOrCreate(
                    [
                        'hotel_id' => $hotelId,
                        'roomtype_id' => $inventoryData['roomtype_id'],
                        'date' => $inventoryData['date'],
                    ],
                    [
                        'total_for_sale' => $inventoryData['total_for_sale'],
                        'is_available' => $inventoryData['is_available'] ?? true,
                        // booked_rooms sẽ được cập nhật bởi logic đặt phòng, không phải ở đây
                    ]
                );
                $updatedRecords[] = $inventory;
            }

            // Commit transaction nếu tất cả các bản ghi đều thành công
            DB::commit();

            return response()->json([
                'message' => 'Room inventories updated successfully.',
                'data' => $updatedRecords
            ], 200);
        } catch (QueryException $e) {
            // Rollback transaction nếu có bất kỳ lỗi nào xảy ra
            DB::rollBack();
            return response()->json(['error' => 'Database error. All changes have been reverted.'], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'An unexpected error occurred.'], 500);
        }
    }
}
