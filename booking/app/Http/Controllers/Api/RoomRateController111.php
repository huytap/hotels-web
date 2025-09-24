<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RoomRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Helpers\HotelHelper;

class RoomRateController111 extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $wpData = $request->json()->all();
        $wpId = $wpData['wp_id'];
        if (!$wpId) {
            return response()->json(['error' => 'wp_id is required.'], 400);
        }

        $hotelId = HotelHelper::getHotelIdByWpId($wpId);
        if (!$hotelId) {
            return response()->json(['error' => 'Invalid wp_id. Hotel not found.'], 404);
        }

        $roomRates = RoomRate::with(['hotel', 'roomType'])->where('hotel_id', $hotelId)
            ->get();

        return response()->json($roomRates);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $wpData = $request->json()->all();

        $validator = Validator::make($wpData, [
            'wp_id' => 'required|integer',
            'data' => 'required|array',
            // Sửa ở đây
            'data.*.roomtype_id' => 'required|exists:roomtypes,id', // <-- Sửa từ 'room_types' thành 'roomtypes'
            'data.*.date' => 'required|date',
            'data.*.price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 3. Lấy hotel_id từ wp_id
        $hotelId = HotelHelper::getHotelIdByWpId($wpData['wp_id']);
        if (!$hotelId) {
            return response()->json(['error' => 'wp_id không hợp lệ. Không tìm thấy khách sạn.'], 404);
        }

        $rates = $wpData['data'];
        $processedRates = [];

        // 4. Chuẩn bị dữ liệu để insert, thêm hotel_id vào mỗi record
        foreach ($rates as $rate) {
            // Thêm hotel_id vào mỗi mảng con trước khi lưu
            $rate['hotel_id'] = $hotelId;
            $processedRates[] = $rate;
        }

        // 5. Kiểm tra trùng lặp trong dữ liệu gửi lên
        $uniqueRates = collect($processedRates)->unique(function ($item) {
            return $item['roomtype_id'] . $item['date'];
        });

        if ($uniqueRates->count() !== count($processedRates)) {
            return response()->json(['error' => 'Dữ liệu chứa các mức giá trùng lặp cho cùng một loại phòng và ngày.'], 422);
        }

        try {
            DB::beginTransaction();

            // 6. Kiểm tra trùng lặp với cơ sở dữ liệu hiện có
            foreach ($uniqueRates as $rate) {
                $existingRate = RoomRate::where('roomtype_id', $rate['roomtype_id'])
                    ->where('hotel_id', $rate['hotel_id'])
                    ->where('date', $rate['date'])
                    ->first();
                if ($existingRate) {
                    DB::rollBack();
                    return response()->json(['error' => 'Một hoặc nhiều mức giá đã tồn tại trong hệ thống.'], 422);
                }
            }

            // 7. Lưu dữ liệu hàng loạt
            RoomRate::insert($uniqueRates->toArray());

            DB::commit();

            return response()->json(['message' => 'Lưu hàng loạt thành công.'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Lỗi khi lưu dữ liệu. Vui lòng thử lại.'], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\RoomRate  $roomRate
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        $wpId = $request->json('wp_id');

        if (is_null($wpId)) {
            return response()->json(['error' => 'wp_id là bắt buộc để xác định khách sạn.'], 400);
        }

        $hotelId = HotelHelper::getHotelIdByWpId($wpId);
        if (!$hotelId) {
            return response()->json(['error' => 'wp_id không hợp lệ. Không tìm thấy khách sạn.'], 404);
        }

        // Tìm RoomRate theo ID và hotel_id
        $roomRate = RoomRate::with(['hotel', 'roomType'])
            ->where('id', $id)
            ->where('hotel_id', $hotelId)
            ->first();

        if (!$roomRate) {
            return response()->json(['error' => 'Không tìm thấy mức giá hoặc không thuộc về khách sạn này.'], 404);
        }

        return response()->json($roomRate);
    }

    /**
     * Cập nhật nhiều mức giá phòng cùng lúc.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function batchUpdate(Request $request)
    {
        $wpData = $request->json()->all();

        $validator = Validator::make($wpData, [
            'wp_id' => 'required|integer',
            'data' => 'required|array',
            'data.*.id' => 'required|integer|exists:room_rates,id',
            'data.*.roomtype_id' => 'required|exists:roomtypes,id',
            'data.*.date' => 'required|date',
            'data.*.price' => 'required|numeric|min:0',
        ], [
            'data.*.id.required' => 'Mỗi mức giá cần phải có ID để cập nhật.',
            'data.*.id.exists' => 'Một hoặc nhiều ID mức giá không tồn tại trong hệ thống.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $hotelId = HotelHelper::getHotelIdByWpId($wpData['wp_id']);
        if (!$hotelId) {
            return response()->json(['error' => 'wp_id không hợp lệ. Không tìm thấy khách sạn.'], 404);
        }

        $updates = $wpData['data'];

        try {
            DB::beginTransaction();

            $processedIds = [];
            foreach ($updates as $rate) {
                $processedIds[] = $rate['id'];

                // Kiểm tra trùng lặp (date, roomtype_id) cho cùng một khách sạn
                $existingRate = RoomRate::where('roomtype_id', $rate['roomtype_id'])
                    ->where('hotel_id', $hotelId)
                    ->where('date', $rate['date'])
                    ->where('id', '!=', $rate['id'])
                    ->first();

                if ($existingRate) {
                    DB::rollBack();
                    return response()->json(['error' => "Mức giá cho loại phòng '{$rate['roomtype_id']}' vào ngày '{$rate['date']}' đã tồn tại."], 422);
                }

                // Cập nhật từng bản ghi
                RoomRate::where('id', $rate['id'])
                    ->where('hotel_id', $hotelId)
                    ->update([
                        'roomtype_id' => $rate['roomtype_id'],
                        'date' => $rate['date'],
                        'price' => $rate['price'],
                    ]);
            }

            DB::commit();

            return response()->json(['message' => 'Cập nhật hàng loạt thành công.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Lỗi khi cập nhật dữ liệu. Vui lòng thử lại.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\RoomRate  $roomRate
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        $wpId = $request->json('wp_id');

        if (is_null($wpId)) {
            return response()->json(['error' => 'wp_id là bắt buộc để xác định khách sạn.'], 400);
        }

        $hotelId = HotelHelper::getHotelIdByWpId($wpId);
        if (!$hotelId) {
            return response()->json(['error' => 'wp_id không hợp lệ. Không tìm thấy khách sạn.'], 404);
        }

        // Tìm mức giá theo ID và hotel_id để đảm bảo nó thuộc về khách sạn này.
        $roomRate = RoomRate::where('id', $id)
            ->where('hotel_id', $hotelId)
            ->first();

        if (!$roomRate) {
            return response()->json(['error' => 'Không tìm thấy mức giá để xóa hoặc không thuộc về khách sạn này.'], 404);
        }

        $roomRate->delete();

        return response()->json(null, 204);
    }
}
