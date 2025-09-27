<?php

namespace App\Helpers;

use App\Models\Hotel;

class HotelHelper
{
    /**
     * Lấy ID của khách sạn (hotel_id) từ ID của blog WordPress (wp_id).
     *
     * @param int $wpId ID của blog WordPress (blog ID).
     * @return int|null ID của khách sạn hoặc null nếu không tìm thấy.
     */
    public static function getHotelIdByWpId(int $wpId): ?int
    {
        $hotel = Hotel::where('wp_id', $wpId)->first();

        return $hotel ? $hotel->id : null;
    }
}
