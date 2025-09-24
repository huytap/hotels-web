<?php

namespace App\Services;

use App\Models\Hotel;
use App\Models\Roomtype;
use Carbon\CarbonPeriod;

class HotelService
{
    public function findAvailableRooms($hotelId, $checkIn, $checkOut, $adults, $children)
    {
        // 1. Lấy danh sách tất cả các loại phòng có sẵn tại khách sạn, đủ sức chứa
        $allRoomTypes = Roomtype::where('hotel_id', $hotelId)
            ->with(['promotions', 'roomRates'])
            ->get();

        $availableRoomTypes = [];
        $numberOfNights = CarbonPeriod::create($checkIn, '1 day', $checkOut)->count() - 1;

        // Tính toán số phòng trống cho mỗi loại phòng trong khoảng thời gian
        foreach ($allRoomTypes as $roomType) {
            $bookedRooms = $this->calculateBookedRooms($roomType, $checkIn, $checkOut);
            $roomsAvailable = $roomType->room_number - $bookedRooms;

            if ($roomsAvailable > 0) {
                $availableRoomTypes[] = $roomType;
            }
        }

        $allCombinations = $this->findCombinations(
            $availableRoomTypes,
            $adults,
            $children,
            $roomsAvailable
        );

        $results = [];

        // 2. Tính giá và số lượng cho từng tổ hợp
        foreach ($allCombinations as $combination) {
            $totalPrice = 0;
            $combinationDetails = [];

            foreach ($combination as $roomTypeItem) {
                $roomType = $roomTypeItem['roomType'];
                $quantity = $roomTypeItem['quantity'];

                $baseRate = $this->calculateBaseRate($roomType, $checkIn, $checkOut);
                $promotions = $this->getApplicablePromotions($roomType, $checkIn, $checkOut, $baseRate, $numberOfNights);

                $combinationDetails[] = [
                    'room_type' => [
                        'id' => $roomType->id,
                        'name' => $roomType->name,
                        'adult_capacity' => $roomType->adult_capacity,
                        'child_capacity' => $roomType->child_capacity,
                        'is_extra_bed_available' => $roomType->is_extra_bed_available,
                    ],
                    'quantity' => $quantity,
                    'promotions' => $promotions,
                    'base_price_total' => $baseRate * $quantity,
                ];

                $totalPrice += ($baseRate * $quantity);
            }

            // Lựa chọn promotion tốt nhất cho tổ hợp này (logic này phức tạp hơn)
            $bestPromotion = null;
            $finalAmount = $totalPrice;

            $results[] = [
                'combination' => $combinationDetails,
                'total_base_price' => $totalPrice,
                'total_final_price' => $finalAmount,
                'promotion_applied' => $bestPromotion,
            ];
        }

        return $results;
    }

    private function findCombinations($roomTypes, $adults, $children, $roomsAvailable)
    {
        $combinations = [];
        // Đây là một ví dụ đơn giản hóa, logic đệ quy thực tế phức tạp hơn
        foreach ($roomTypes as $roomType) {
            $totalPeople = $adults + $children;
            $roomCapacity = $roomType->adult_capacity + $roomType->child_capacity;

            if ($roomCapacity >= $totalPeople) {
                // Ví dụ 1: Tìm 1 phòng duy nhất đáp ứng yêu cầu
                $combinations[] = [
                    'roomType' => $roomType,
                    'quantity' => 1
                ];
            }

            // Ví dụ 2: Tìm tổ hợp 2 phòng
            foreach ($roomTypes as $roomType2) {
                if ($roomType->id === $roomType2->id) continue;
                $totalCapacity = $roomCapacity + ($roomType2->adult_capacity + $roomType2->child_capacity);

                if ($totalCapacity >= $totalPeople) {
                    $combinations[] = [
                        ['roomType' => $roomType, 'quantity' => 1],
                        ['roomType' => $roomType2, 'quantity' => 1]
                    ];
                }
            }
        }
        return $combinations;
    }

    // Các phương thức calculateBookedRooms, calculateBaseRate, getApplicablePromotions giữ nguyên
    private function calculateBookedRooms($roomType, $checkIn, $checkOut)
    {
        // ... (Logic giữ nguyên)
    }

    private function calculateBaseRate($roomType, $checkIn, $checkOut)
    {
        // ... (Logic giữ nguyên)
    }

    private function getApplicablePromotions($roomType, $checkIn, $checkOut, $baseRate, $numberOfNights)
    {
        // ... (Logic giữ nguyên)
    }
}
