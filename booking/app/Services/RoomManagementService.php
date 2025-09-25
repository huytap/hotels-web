<?php

namespace App\Services;

use App\Models\RoomRate;
use App\Models\RoomInventory;
use App\Models\Roomtype;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RoomManagementService
{
    /**
     * Lấy dữ liệu tổng hợp giá và tồn kho cho calendar view
     */
    public function getCalendarData($hotelId, $roomtypeId, $startDate, $endDate)
    {
        // Lấy rates
        $rates = RoomRate::forHotel($hotelId)
            ->forRoomtype($roomtypeId)
            ->betweenDates($startDate, $endDate)
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->date->format('Y-m-d') => $item
                ];
            });
        //dd($rates->keys()->toArray());
        // Lấy inventory
        $inventories = RoomInventory::forHotel($hotelId)
            ->forRoomtype($roomtypeId)
            ->betweenDates($startDate, $endDate)
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->date->format('Y-m-d') => $item
                ];
            });
        // Kết hợp dữ liệu
        $calendarData = [];
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        while ($start->lte($end)) {
            $dateStr = $start->format('Y-m-d');
            $rate = $rates->get($dateStr);
            $inventory = $inventories->get($dateStr);

            $calendarData[$dateStr] = [
                'date' => $dateStr,
                'price' => $rate ? $rate->price : 0,
                'total_for_sale' => $inventory ? $inventory->total_for_sale : 0,
                'booked_rooms' => $inventory ? $inventory->booked_rooms : 0,
                //'available_rooms' => $inventory ? $inventory->total_for_sale : 0,
                'is_available' => $inventory ? $inventory->is_available : false,
                'has_rate' => (bool) $rate,
                'has_inventory' => (bool) $inventory,
                'can_sell' => $this->canSell($rate, $inventory),
            ];

            $start->addDay();
        }
        return $calendarData;
    }

    protected function updateRate($hotelId, $roomtypeId, $date, $price)
    {
        // Xác định các điều kiện để tìm hoặc tạo bản ghi
        $attributes = [
            'hotel_id' => $hotelId,
            'roomtype_id' => $roomtypeId,
            'date' => $date,
        ];

        // Dữ liệu cần cập nhật hoặc tạo mới
        $values = [
            'price' => $price,
        ];

        // Sử dụng updateOrCreate để tìm và cập nhật, hoặc tạo mới
        return RoomRate::updateOrCreate($attributes, $values);
    }

    protected function updateInventory($hotelId, $roomtypeId, $date, $inventoryData)
    {
        // Xác định các điều kiện để tìm hoặc tạo bản ghi
        $attributes = [
            'hotel_id' => $hotelId,
            'roomtype_id' => $roomtypeId,
            'date' => $date
        ];

        // Dữ liệu cần cập nhật hoặc tạo mới
        $values = $inventoryData;

        // Sử dụng updateOrCreate để tìm và cập nhật, hoặc tạo mới
        return RoomInventory::updateOrCreate($attributes, $values);
    }

    /**
     * Cập nhật cả rate và inventory cùng lúc
     */
    public function updateRateAndInventory($hotelId, $roomtypeId, $date, $rateData, $inventoryData)
    {
        DB::beginTransaction();
        try {
            $rate = null;
            $inventory = null;

            if (!empty($rateData)) {
                $rate = $this->updateRate($hotelId, $roomtypeId, $date, $rateData['price']);
            }

            if (!empty($inventoryData)) {
                $inventory = $this->updateInventory($hotelId, $roomtypeId, $date, $inventoryData);
            }

            DB::commit();
            return ['rate' => $rate, 'inventory' => $inventory];
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Bulk update rates cho khoảng thời gian
     */
    public function bulkUpdateRates($hotelId, $roomtypeId, $startDate, $endDate, $price, $weekdays = null)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $updatedCount = 0;

        DB::beginTransaction();
        try {
            while ($start->lte($end)) {
                // Kiểm tra weekdays nếu có
                if ($weekdays === null || in_array($start->dayOfWeek, $weekdays)) {
                    RoomRate::setRate($hotelId, $roomtypeId, $start->format('Y-m-d'), $price);
                    $updatedCount++;
                }
                $start->addDay();
            }

            DB::commit();
            return $updatedCount;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Bulk update inventory cho khoảng thời gian
     */
    public function bulkUpdateInventory($hotelId, $roomtypeId, $date, $data)
    {
        DB::beginTransaction();
        try {
            $rate = null;
            $inventory = null;

            if (!empty($data)) {
                $inventory = $this->updateInventory($hotelId, $roomtypeId, $date, $data);
            }

            DB::commit();
            return ['inventory' => $inventory];
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Copy rates từ period này sang period khác
     */
    public function copyRates($hotelId, $roomtypeId, $sourceStart, $sourceEnd, $targetStart, $targetEnd)
    {
        $sourceRates = RoomRate::forHotel($hotelId)
            ->forRoomtype($roomtypeId)
            ->betweenDates($sourceStart, $sourceEnd)
            ->get()
            ->keyBy('date');

        $sourceStartDate = Carbon::parse($sourceStart);
        $targetStartDate = Carbon::parse($targetStart);
        $targetEndDate = Carbon::parse($targetEnd);
        $copiedCount = 0;

        DB::beginTransaction();
        try {
            $currentTarget = $targetStartDate->copy();
            $currentSource = $sourceStartDate->copy();

            while ($currentTarget->lte($targetEndDate)) {
                $sourceDate = $currentSource->format('Y-m-d');
                $targetDate = $currentTarget->format('Y-m-d');

                $sourceRate = $sourceRates->get($sourceDate);
                if ($sourceRate) {
                    RoomRate::setRate($hotelId, $roomtypeId, $targetDate, $sourceRate->price);
                    $copiedCount++;
                }

                $currentTarget->addDay();
                $currentSource->addDay();
            }

            DB::commit();
            return $copiedCount;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Copy inventory từ period này sang period khác
     */
    public function copyInventory($hotelId, $roomtypeId, $sourceStart, $sourceEnd, $targetStart, $targetEnd)
    {
        $sourceInventories = RoomInventory::forHotel($hotelId)
            ->forRoomtype($roomtypeId)
            ->betweenDates($sourceStart, $sourceEnd)
            ->get()
            ->keyBy('date');

        $sourceStartDate = Carbon::parse($sourceStart);
        $targetStartDate = Carbon::parse($targetStart);
        $targetEndDate = Carbon::parse($targetEnd);
        $copiedCount = 0;

        DB::beginTransaction();
        try {
            $currentTarget = $targetStartDate->copy();
            $currentSource = $sourceStartDate->copy();

            while ($currentTarget->lte($targetEndDate)) {
                $sourceDate = $currentSource->format('Y-m-d');
                $targetDate = $currentTarget->format('Y-m-d');

                $sourceInventory = $sourceInventories->get($sourceDate);
                if ($sourceInventory) {
                    RoomInventory::updateInventory($hotelId, $roomtypeId, $targetDate, [
                        'total_for_sale' => $sourceInventory->total_for_sale,
                        'is_available' => $sourceInventory->is_available,
                        'booked_rooms' => 0, // Reset booked rooms
                    ]);
                    $copiedCount++;
                }

                $currentTarget->addDay();
                $currentSource->addDay();
            }

            DB::commit();
            return $copiedCount;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Kiểm tra có thể bán không (có cả giá và inventory)
     */
    private function canSell($rate, $inventory)
    {
        return $rate &&
            $rate->price > 0 &&
            $inventory &&
            ($inventory->is_available == 0) &&
            $inventory->total_for_sale > 0;
    }

    /**
     * Lấy thống kê tổng quan
     */
    public function getStatistics($hotelId, $roomtypeId, $startDate, $endDate)
    {
        $rates = RoomRate::forHotel($hotelId)
            ->forRoomtype($roomtypeId)
            ->betweenDates($startDate, $endDate)
            ->get();

        $inventories = RoomInventory::forHotel($hotelId)
            ->forRoomtype($roomtypeId)
            ->betweenDates($startDate, $endDate)
            ->get();

        return [
            'total_days' => Carbon::parse($startDate)->diffInDays($endDate) + 1,
            'days_with_rates' => $rates->count(),
            'days_with_inventory' => $inventories->count(),
            'average_rate' => $rates->avg('price'),
            'min_rate' => $rates->min('price'),
            'max_rate' => $rates->max('price'),
            'total_rooms_for_sale' => $inventories->sum('total_for_sale'),
            'total_booked_rooms' => $inventories->sum('booked_rooms'),
            'total_available_rooms' => $inventories->sum(function ($inv) {
                return $inv->available_rooms;
            }),
            'occupancy_rate' => $inventories->avg(function ($inv) {
                return $inv->total_for_sale > 0 ? ($inv->booked_rooms / $inv->total_for_sale) * 100 : 0;
            }),
        ];
    }

    /**
     * Đặt phòng - giảm inventory
     */
    public function bookRoom($hotelId, $roomtypeId, $date, $quantity = 1)
    {
        $inventory = RoomInventory::getInventoryForDate($hotelId, $roomtypeId, $date);

        if (!$inventory) {
            throw new \Exception('No inventory found for this date');
        }

        if (!$inventory->hasAvailability()) {
            throw new \Exception('No rooms available');
        }

        return $inventory->bookRooms($quantity);
    }

    /**
     * Hủy đặt phòng - tăng inventory
     */
    public function cancelBooking($hotelId, $roomtypeId, $date, $quantity = 1)
    {
        $inventory = RoomInventory::getInventoryForDate($hotelId, $roomtypeId, $date);

        if ($inventory) {
            $inventory->cancelRooms($quantity);
            return true;
        }

        return false;
    }
}
