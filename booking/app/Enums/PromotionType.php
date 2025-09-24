<?php

namespace App\Enums;

class PromotionType
{
    public const EARLY_BIRD = 'early_bird';
    public const LAST_MINUTES = 'last_minutes';
    public const OTHERS = 'others';
    // Thêm loại mới khi cần
    //public const HOLIDAY_DEAL = 'holiday_deal';
    /**
     * Lấy tất cả các giá trị của enum.
     * @return array
     */
    public static function getValues(): array
    {
        return [
            self::EARLY_BIRD,
            self::LAST_MINUTES,
            self::OTHERS,
            // Thêm các hằng số mới vào đây
            //self::HOLIDAY_DEAL
        ];
    }
}
