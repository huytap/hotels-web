<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\HotelHelper;
use App\Models\Hotel;
use App\Models\Promotion;
use App\Models\RoomRate;
use App\Models\Roomtype;
use Illuminate\Http\JsonResponse;

class HotelController extends BaseApiController
{
    public function getRooms(Request $request)
    {
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }

        $roomtypes = Roomtype::where('hotel_id', $hotel->id)
            ->select([
                'id',
                'title',
                'description',
                'area',
                'adult_capacity',
                'child_capacity',
                'bed_type',
                'amenities',
                'room_amenities',
                'bathroom_amenities',
                'view',
                'gallery_images',
                'featured_image',
                'price',
                'is_extra_bed_available'
            ])
            ->get();

        // Trả về dữ liệu
        return $this->successResponse($roomtypes, 'Lấy danh sách phòng thành công');
    }

    public function getRoomDetail($id, Request $request)
    {
        $wpId = $request->input('wp_id');
        if (!$wpId) {
            return response()->json(['error' => 'wp_id is required.'], 400);
        }

        $hotelId = HotelHelper::getHotelIdByWpId($wpId);
        if (!$hotelId) {
            return response()->json(['error' => 'Invalid wp_id. Hotel not found.'], 404);
        }

        $roomtype = Roomtype::where('id', $id)
            ->where('hotel_id', $hotelId)
            ->where('is_active', true)
            ->first();

        if (!$roomtype) {
            return response()->json(['error' => 'Room not found.'], 404);
        }

        return $this->successResponse($roomtype, 'Lấy thông tin chi tiết phòng thành công');
    }

    public function getRoomRates(Request $request)
    {
        $wpId = $request->input('wp_id');
        if (!$wpId) {
            return response()->json(['error' => 'wp_id is required.'], 400);
        }

        $hotelId = HotelHelper::getHotelIdByWpId($wpId);
        if (!$hotelId) {
            return response()->json(['error' => 'Invalid wp_id. Hotel not found.'], 404);
        }
        $roomtypeId = $request->input("room_type_id");
        $fromDate = $request->input("date_from");
        $toDate = $request->input("date_to");
        $roomType = Roomtype::where('id', $roomtypeId)
            ->where('hotel_id', $hotelId)
            ->first();

        if (!$roomType) {
            return ['error' => 'Room type not found for this hotel.'];
        }

        $rates = RoomRate::where('roomtype_id', $roomtypeId)
            ->whereBetween('date', [$fromDate, $toDate])
            ->orderBy('date')
            ->get();

        if ($rates->isEmpty()) {
            return ['error' => 'No rates found for the selected dates.'];
        }

        $roomData = [
            'room_type_id' => $roomType->id,
            'name' => $roomType->title,
            'data' => [],
        ];

        foreach ($rates as $rate) {
            $roomData['data'][] = [
                'date' => $rate->date,
                'rate_formatted' => $rate->price,
            ];
        }

        return $roomData;
    }
}
