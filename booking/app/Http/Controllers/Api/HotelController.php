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

class HotelController extends Controller
{
    public function getRooms(Request $request)
    {
        $wpId = $request->input('wp_id');
        if (!$wpId) {
            return response()->json(['error' => 'wp_id is required.'], 400);
        }

        $hotelId = HotelHelper::getHotelIdByWpId($wpId);
        if (!$hotelId) {
            return response()->json(['error' => 'Invalid wp_id. Hotel not found.'], 404);
        }
        $roomtypes = Roomtype::select('id', 'title')->get();

        // Trả về dữ liệu
        return response()->json($roomtypes);
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
