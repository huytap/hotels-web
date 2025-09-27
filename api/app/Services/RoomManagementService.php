<?php

namespace App\Services;

use App\Models\RoomRate;
use App\Models\Roomtype;
use App\Models\RateTemplate;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RoomManagementService
{
    /**
     * Lấy dữ liệu tổng hợp giá và tồn kho cho calendar view
     */
    public function getCalendarData($hotelId, $roomtypeId, $startDate, $endDate)
    {
        // Lấy combined data từ room_rates (đã gộp cả inventory)
        $roomRates = RoomRate::forHotel($hotelId)
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
            $roomRate = $roomRates->get($dateStr);

            $calendarData[$dateStr] = [
                'date' => $dateStr,
                'price' => $roomRate ? $roomRate->price : 0,
                'total_for_sale' => $roomRate ? $roomRate->total_for_sale : 0,
                'booked_rooms' => $roomRate ? $roomRate->booked_rooms : 0,
                'available_rooms' => $roomRate ? $roomRate->available_rooms : 0,
                'is_available' => $roomRate ? $roomRate->is_available : false,
                'has_rate' => $roomRate && $roomRate->price > 0,
                'has_inventory' => $roomRate && $roomRate->total_for_sale > 0,
                'can_sell' => $roomRate ? $roomRate->canSell() : false,
                // New restriction fields
                'min_stay' => $roomRate ? $roomRate->min_stay : 1,
                'max_stay' => $roomRate ? $roomRate->max_stay : 30,
                'restrictions' => $roomRate ? $roomRate->restrictions : [],
                'is_closed' => $roomRate ? $roomRate->is_closed : false,
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
        return RoomRate::updateOrCreate($attributes, $values);
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
                    // Use updateOrCreate to copy both rate and restrictions
                    RoomRate::updateOrCreate(
                        [
                            'hotel_id' => $hotelId,
                            'roomtype_id' => $roomtypeId,
                            'date' => $targetDate,
                        ],
                        [
                            'price' => $sourceRate->price,
                            'min_stay' => $sourceRate->min_stay,
                            'max_stay' => $sourceRate->max_stay,
                            'close_to_arrival' => $sourceRate->close_to_arrival,
                            'close_to_departure' => $sourceRate->close_to_departure,
                            'is_closed' => $sourceRate->is_closed,
                        ]
                    );
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
        $sourceInventories = RoomRate::forHotel($hotelId)
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
                    RoomRate::updateInventory($hotelId, $roomtypeId, $targetDate, [
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
     * Lấy thống kê tổng quan
     */
    public function getStatistics($hotelId, $roomtypeId, $startDate, $endDate)
    {
        $roomRates = RoomRate::forHotel($hotelId)
            ->forRoomtype($roomtypeId)
            ->betweenDates($startDate, $endDate)
            ->get();

        return [
            'total_days' => Carbon::parse($startDate)->diffInDays($endDate) + 1,
            'days_with_rates' => $roomRates->where('price', '>', 0)->count(),
            'days_with_inventory' => $roomRates->where('total_for_sale', '>', 0)->count(),
            'average_rate' => $roomRates->where('price', '>', 0)->avg('price'),
            'min_rate' => $roomRates->where('price', '>', 0)->min('price'),
            'max_rate' => $roomRates->where('price', '>', 0)->max('price'),
            'total_rooms_for_sale' => $roomRates->sum('total_for_sale'),
            'total_booked_rooms' => $roomRates->sum('booked_rooms'),
            'total_available_rooms' => $roomRates->sum(function ($roomRate) {
                return $roomRate->available_rooms;
            }),
            'occupancy_rate' => $roomRates->avg(function ($roomRate) {
                return $roomRate->total_for_sale > 0 ? ($roomRate->booked_rooms / $roomRate->total_for_sale) * 100 : 0;
            }),
        ];
    }

    /**
     * Copy restrictions từ period này sang period khác
     */
    public function copyRestrictions($hotelId, $roomtypeId, $sourceStart, $sourceEnd, $targetStart, $targetEnd)
    {
        $sourceRestrictions = RoomRate::forHotel($hotelId)
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

                $sourceRestriction = $sourceRestrictions->get($sourceDate);
                if ($sourceRestriction) {
                    RoomRate::updateOrCreate(
                        [
                            'hotel_id' => $hotelId,
                            'roomtype_id' => $roomtypeId,
                            'date' => $targetDate,
                        ],
                        [
                            'min_stay' => $sourceRestriction->min_stay,
                            'max_stay' => $sourceRestriction->max_stay,
                            'close_to_arrival' => $sourceRestriction->close_to_arrival,
                            'close_to_departure' => $sourceRestriction->close_to_departure,
                            'is_closed' => $sourceRestriction->is_closed,
                        ]
                    );
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
     * Copy tất cả (rates, inventory, restrictions) trong một operation
     */
    public function copyAll($hotelId, $roomtypeId, $sourceStart, $sourceEnd, $targetStart, $targetEnd, $options = [])
    {
        $copyRates = $options['copy_rates'] ?? true;
        $copyAvailability = $options['copy_availability'] ?? true;
        $copyRestrictions = $options['copy_restrictions'] ?? true;

        $sourceDataCollection = RoomRate::forHotel($hotelId)
            ->forRoomtype($roomtypeId)
            ->betweenDates($sourceStart, $sourceEnd)
            ->get();
        $sourceData = $sourceDataCollection->mapWithKeys(function ($item) {
            // Ép kiểu (cast) cột 'date' thành Carbon instance trước nếu nó là chuỗi
            // hoặc đảm bảo model RoomRate có casts cho cột 'date'
            $dateKey = \Illuminate\Support\Carbon::parse($item->date)->format('Y-m-d');
            return [$dateKey => $item];
        });
        $sourceStartDate = Carbon::parse($sourceStart);
        $targetStartDate = Carbon::parse($targetStart);
        $targetEndDate = Carbon::parse($targetEnd);

        $results = [
            'rates_copied' => 0,
            'inventory_copied' => 0,
            'restrictions_copied' => 0,
            'total_copied' => 0
        ];

        DB::beginTransaction();
        try {
            $currentTarget = $targetStartDate->copy();
            $currentSource = $sourceStartDate->copy();

            while ($currentTarget->lte($targetEndDate)) {
                $sourceDate = $currentSource->format('Y-m-d');
                $targetDate = $currentTarget->format('Y-m-d');

                $sourceRecord = $sourceData->get($sourceDate);
                if ($sourceRecord) {
                    $updateData = [];
                    $hasData = false;

                    // Copy rates data
                    if ($copyRates && $sourceRecord->price > 0) {
                        $updateData['price'] = $sourceRecord->price;
                        $updateData['min_stay'] = $sourceRecord->min_stay;
                        $updateData['max_stay'] = $sourceRecord->max_stay;
                        $hasData = true;
                        $results['rates_copied']++;
                    }

                    // Copy inventory data
                    if ($copyAvailability && $sourceRecord->total_for_sale > 0) {
                        $updateData['total_for_sale'] = $sourceRecord->total_for_sale;
                        $updateData['is_available'] = $sourceRecord->is_available;
                        $updateData['booked_rooms'] = 0; // Reset booked rooms
                        $hasData = true;
                        $results['inventory_copied']++;
                    }

                    // Copy restrictions data
                    if ($copyRestrictions && $sourceRecord->hasRestrictions()) {
                        $updateData['close_to_arrival'] = $sourceRecord->close_to_arrival;
                        $updateData['close_to_departure'] = $sourceRecord->close_to_departure;
                        $updateData['is_closed'] = $sourceRecord->is_closed;
                        $hasData = true;
                        $results['restrictions_copied']++;
                    }

                    // Update if we have any data to copy
                    if ($hasData) {
                        RoomRate::updateOrCreate(
                            [
                                'hotel_id' => $hotelId,
                                'roomtype_id' => $roomtypeId,
                                'date' => $targetDate,
                            ],
                            $updateData
                        );
                        $results['total_copied']++;
                    }
                }

                $currentTarget->addDay();
                $currentSource->addDay();
            }

            DB::commit();
            return $results;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Đặt phòng - giảm inventory
     */
    public function bookRoom($hotelId, $roomtypeId, $date, $quantity = 1)
    {
        $roomRate = RoomRate::getRateForDate($hotelId, $roomtypeId, $date);

        if (!$roomRate) {
            throw new \Exception('No room rate found for this date');
        }

        if (!$roomRate->hasAvailability()) {
            throw new \Exception('No rooms available');
        }

        return $roomRate->bookRooms($quantity);
    }

    /**
     * Hủy đặt phòng - tăng inventory
     */
    public function cancelBooking($hotelId, $roomtypeId, $date, $quantity = 1)
    {
        $roomRate = RoomRate::getRateForDate($hotelId, $roomtypeId, $date);

        if ($roomRate) {
            $roomRate->cancelRooms($quantity);
            return true;
        }

        return false;
    }

    /**
     * Lấy danh sách template cho hotel
     */
    public function getTemplates($hotelId, $roomtypeId = null)
    {
        $query = RateTemplate::forHotel($hotelId)
            ->with('roomtype');

        if ($roomtypeId) {
            $query->forRoomtype($roomtypeId);
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Tạo template mới
     */
    public function createTemplate($hotelId, array $data)
    {
        return RateTemplate::create([
            'hotel_id' => $hotelId,
            'roomtype_id' => $data['roomtype_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'rates' => $data['rates'],
            'min_stay' => $data['min_stay'] ?? 1,
            'max_stay' => $data['max_stay'] ?? 30,
            'is_active' => $data['is_active'] ?? true,
            // Restrictions
            'close_to_arrival' => $data['close_to_arrival'] ?? false,
            'close_to_departure' => $data['close_to_departure'] ?? false,
            'is_closed' => $data['is_closed'] ?? false,
        ]);
    }

    /**
     * Cập nhật template
     */
    public function updateTemplate($hotelId, $templateId, array $data)
    {
        $template = RateTemplate::forHotel($hotelId)->findOrFail($templateId);

        $template->update([
            'roomtype_id' => $data['roomtype_id'] ?? $template->roomtype_id,
            'name' => $data['name'] ?? $template->name,
            'description' => $data['description'] ?? $template->description,
            'rates' => $data['rates'] ?? $template->rates,
            'min_stay' => $data['min_stay'] ?? $template->min_stay,
            'max_stay' => $data['max_stay'] ?? $template->max_stay,
            'is_active' => $data['is_active'] ?? $template->is_active,
            // Restrictions
            'close_to_arrival' => $data['close_to_arrival'] ?? $template->close_to_arrival,
            'close_to_departure' => $data['close_to_departure'] ?? $template->close_to_departure,
            'is_closed' => $data['is_closed'] ?? $template->is_closed,
        ]);

        return $template;
    }

    /**
     * Xóa template
     */
    public function deleteTemplate($hotelId, $templateId)
    {
        $template = RateTemplate::forHotel($hotelId)->findOrFail($templateId);
        return $template->delete();
    }

    /**
     * Lấy template theo ID
     */
    public function getTemplate($hotelId, $templateId)
    {
        return RateTemplate::forHotel($hotelId)
            ->with('roomtype')
            ->findOrFail($templateId);
    }

    /**
     * Áp dụng template cho khoảng thời gian
     */
    public function applyTemplate($hotelId, $templateId, $roomtypeId, $startDate, $endDate, $overwriteExisting = false)
    {
        $template = $this->getTemplate($hotelId, $templateId);

        if (!$template->is_active) {
            throw new \Exception('Template is not active');
        }

        // Ensure the roomtype matches the template's roomtype
        if ($template->roomtype_id != $roomtypeId) {
            throw new \Exception('Room type does not match template');
        }

        $rates = $template->rates;
        $appliedCount = 0;

        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);

        DB::beginTransaction();
        try {
            $currentDate = $startDate->copy();

            while ($currentDate->lte($endDate)) {
                $weekdayName = $this->getWeekdayName($currentDate->dayOfWeek);
                $rate = $rates[$weekdayName] ?? 0;

                // Apply template even if rate is 0, as long as there are restrictions or the template has meaningful data
                $hasRestrictions = $template->close_to_arrival || $template->close_to_departure || $template->is_closed;

                if ($rate > 0 || $hasRestrictions) {
                    $dateStr = $currentDate->format('Y-m-d');

                    // Check if rate already exists
                    $existingRate = RoomRate::getRateForDate($hotelId, $roomtypeId, $dateStr);

                    if (!$existingRate || $overwriteExisting) {
                        // Apply both rate and restrictions from template
                        RoomRate::updateOrCreate(
                            [
                                'hotel_id' => $hotelId,
                                'roomtype_id' => $roomtypeId,
                                'date' => $dateStr,
                            ],
                            [
                                'price' => $rate,
                                'min_stay' => $template->min_stay,
                                'max_stay' => $template->max_stay,
                                'close_to_arrival' => $template->close_to_arrival,
                                'close_to_departure' => $template->close_to_departure,
                                'is_closed' => $template->is_closed,
                            ]
                        );
                        $appliedCount++;
                    }
                }

                $currentDate->addDay();
            }

            DB::commit();
            return $appliedCount;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Lấy tên thứ trong tuần theo số
     */
    private function getWeekdayName($dayOfWeek)
    {
        $weekdays = [
            0 => 'sunday',    // Sunday
            1 => 'monday',    // Monday
            2 => 'tuesday',   // Tuesday
            3 => 'wednesday', // Wednesday
            4 => 'thursday',  // Thursday
            5 => 'friday',    // Friday
            6 => 'saturday',  // Saturday
        ];

        return $weekdays[$dayOfWeek] ?? 'monday';
    }
}
