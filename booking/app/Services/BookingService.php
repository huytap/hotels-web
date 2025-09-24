<?php

namespace App\Services;

use App\Enums\PromotionType;
use App\Models\Hotel;
use App\Models\Roomtype;
use App\Models\RoomInventory;
use App\Models\RoomRate;
use App\Models\Promotion;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class BookingService
{
    /**
     * Tìm các tổ hợp phòng còn trống và áp dụng khuyến mãi.
     *
     * @param int $hotelId
     * @param string $checkIn
     * @param string $checkOut
     * @param int $adults
     * @param int $children
     * @return array
     */
    public function findRoomCombinations($hotelId, $checkIn, $checkOut, $adults, $children)
    {
        // Sử dụng hotelId để tìm khách sạn
        $hotel = Hotel::find($hotelId);
        if (!$hotel) {
            return []; // Trả về mảng rỗng nếu không tìm thấy khách sạn
        }

        // 1. Tìm tất cả các loại phòng phù hợp với số lượng khách
        $roomTypes = $hotel->roomtypes()
            ->where('adult_capacity', '>=', $adults)
            ->get();

        $availableCombinations = [];
        $numberOfNights = (new Carbon($checkIn))->diffInDays($checkOut);

        // 2. Duyệt qua từng loại phòng để tìm các tổ hợp khả thi
        foreach ($roomTypes as $roomType) {
            // Kiểm tra số lượng phòng trống cho toàn bộ kỳ lưu trú
            $availableCount = $this->checkAvailability($roomType->id, $checkIn, $checkOut);
            if ($availableCount <= 0) {
                continue; // Bỏ qua nếu không có phòng trống
            }

            // Tính giá cơ bản cho toàn bộ kỳ lưu trú
            $basePrice = $this->calculateBaseRate($roomType->id, $checkIn, $checkOut);

            // Kiểm tra nếu giá không đủ cho tất cả các đêm
            if ($basePrice <= 0) {
                continue;
            }

            // Lấy các khuyến mãi hợp lệ
            $promotions = $this->getApplicablePromotions($roomType, $checkIn, $checkOut);

            // Xây dựng chi tiết tổ hợp phòng
            $combinationDetails = [
                'room_type' => [
                    'id' => $roomType->id,
                    'name' => $roomType->title,
                    'description' => $roomType->description,
                    'amenities' => $roomType->amenities,
                    'gallery' => $roomType->gallery_images,
                ],
                'quantity' => 1,
                'available_rooms' => $availableCount,
                'base_price_total' => $basePrice,
                'promotions' => $promotions->map(function ($promo) use ($basePrice) {
                    return [
                        'details' => $promo,
                        'discounted_price_total' => $this->calculateDiscountedPrice($basePrice, $promo),
                    ];
                }),
            ];

            $availableCombinations[] = [
                'total_price' => $basePrice,
                'combination_details' => [$combinationDetails],
            ];
        }

        return $availableCombinations;
    }

    /**
     * Kiểm tra phòng trống xuyên suốt khoảng thời gian.
     */
    private function checkAvailability($roomTypeId, $checkIn, $checkOut)
    {
        $minAvailable = PHP_INT_MAX;

        $endDate = (new Carbon($checkOut))->subDay();
        $period = CarbonPeriod::create($checkIn, '1 day', $endDate);

        foreach ($period as $date) {
            $inventory = RoomInventory::where('roomtype_id', $roomTypeId)
                ->where('date', $date->toDateString())
                ->first();

            if (!$inventory || $inventory->is_available === false) {
                return 0;
            } else {
                $available = $inventory->total_for_sale - $inventory->booked_rooms;
            }
            if ($available < $minAvailable) {
                $minAvailable = $available;
            }
        }
        return $minAvailable;
    }

    /**
     * Tính tổng giá cơ bản cho toàn bộ khoảng thời gian.
     */
    private function calculateBaseRate($roomTypeId, $checkIn, $checkOut)
    {
        $endDate = (new Carbon($checkOut))->subDay();
        $period = CarbonPeriod::create($checkIn, '1 day', $endDate);

        $totalPrice = 0;
        $numberOfNights = 0;

        foreach ($period as $date) {
            $rate = RoomRate::where('roomtype_id', $roomTypeId)
                ->where('date', $date->toDateString())
                ->first();

            if (!$rate) {
                return 0; // Trả về 0 nếu thiếu giá của bất kỳ đêm nào
            }
            $totalPrice += $rate->price;
            $numberOfNights++;
        }

        return $totalPrice;
    }

    /**
     * Lấy danh sách khuyến mãi hợp lệ cho một loại phòng trong khoảng thời gian cụ thể.
     */
    private function getApplicablePromotions($roomType, $checkIn, $checkOut)
    {
        $promotions = $roomType->promotions()
            ->where('is_active', true)
            ->where('start_date', '<=', $checkIn)
            ->where('end_date', '>=', $checkIn)
            ->get();

        $today = Carbon::now()->startOfDay();
        $checkInDate = (new Carbon($checkIn))->startOfDay();
        $numberOfNights = (new Carbon($checkIn))->diffInDays($checkOut);

        return $promotions->filter(function ($promo) use ($today, $checkInDate, $numberOfNights) {
            $isConditionMet = true;

            if ($promo->booking_days_in_advance !== null) {
                $daysBetween = $today->diffInDays($checkInDate, false);
                if ($promo->type === PromotionType::EARLY_BIRD && $daysBetween < $promo->booking_days_in_advance) {
                    $isConditionMet = false;
                } elseif ($promo->type === PromotionType::LAST_MINUTES && ($daysBetween > $promo->booking_days_in_advance || $daysBetween < 0)) {
                    $isConditionMet = false;
                }
            }

            if (!$isConditionMet) {
                return false;
            }

            if ($promo->min_stay !== null && $numberOfNights < $promo->min_stay) {
                $isConditionMet = false;
            }
            if ($promo->max_stay !== null && $numberOfNights > $promo->max_stay) {
                $isConditionMet = false;
            }

            return $isConditionMet;
        });
    }

    /**
     * Tính giá sau khuyến mãi.
     */
    private function calculateDiscountedPrice($basePrice, $promotion)
    {
        if ($promotion->value_type === 'fixed') {
            return max(0, $basePrice - $promotion->value);
        } elseif ($promotion->value_type === 'percentage') {
            return $basePrice * (1 - $promotion->value / 100);
        }
        return $basePrice;
    }
}
