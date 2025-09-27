<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomRate extends Model
{
    use HasFactory;
    protected $fillable = [
        'hotel_id',
        'roomtype_id',
        'date',
        'price',
        'min_stay',
        'max_stay',
        'close_to_arrival',
        'close_to_departure',
        'is_closed',
        // Inventory fields (merged from room_inventories)
        'total_for_sale',
        'booked_rooms',
        'is_available',
    ];

    protected $casts = [
        'date' => 'date',
        'price' => 'decimal:2',
        'min_stay' => 'integer',
        'max_stay' => 'integer',
        'close_to_arrival' => 'boolean',
        'close_to_departure' => 'boolean',
        'is_closed' => 'boolean',
        // Inventory casts
        'total_for_sale' => 'integer',
        'booked_rooms' => 'integer',
        'is_available' => 'boolean',
    ];

    /**
     * Get the hotel that the room rate belongs to.
     */
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Get the room type that the room rate belongs to.
     */
    public function roomtype()
    {
        return $this->belongsTo(Roomtype::class);
    }

    /**
     * Scope để lọc theo khách sạn
     */
    public function scopeForHotel($query, $hotelId)
    {
        return $query->where('hotel_id', $hotelId);
    }

    /**
     * Scope để lọc theo loại phòng
     */
    public function scopeForRoomtype($query, $roomtypeId)
    {
        return $query->where('roomtype_id', $roomtypeId);
    }

    /**
     * Scope để lọc theo khoảng thời gian
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Lấy giá phòng cho ngày cụ thể
     */
    public static function getRateForDate($hotelId, $roomtypeId, $date)
    {
        return self::where('hotel_id', $hotelId)
            ->where('roomtype_id', $roomtypeId)
            ->where('date', $date)
            ->first();
    }

    /**
     * Tạo hoặc cập nhật giá phòng
     */
    public static function setRate($hotelId, $roomtypeId, $date, $price)
    {
        return self::updateOrCreate(
            [
                'hotel_id' => $hotelId,
                'roomtype_id' => $roomtypeId,
                'date' => $date,
            ],
            [
                'price' => $price,
            ]
        );
    }

    /**
     * Format giá cho hiển thị
     */
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 0, ',', '.') . ' VND';
    }

    /**
     * Kiểm tra xem có giá cho ngày này không
     */
    public static function hasRateForDate($hotelId, $roomtypeId, $date)
    {
        return self::where('hotel_id', $hotelId)
            ->where('roomtype_id', $roomtypeId)
            ->where('date', $date)
            ->exists();
    }

    /**
     * Kiểm tra có hạn chế không
     */
    public function hasRestrictions()
    {
        return $this->close_to_arrival || $this->close_to_departure || $this->is_closed;
    }

    /**
     * Lấy danh sách hạn chế
     */
    public function getRestrictionsAttribute()
    {
        $restrictions = [];
        if ($this->close_to_arrival) {
            $restrictions[] = 'CTA';
        }
        if ($this->close_to_departure) {
            $restrictions[] = 'CTD';
        }
        if ($this->is_closed) {
            $restrictions[] = 'CLOSED';
        }
        return $restrictions;
    }

    /**
     * Kiểm tra có thể bán không (không bị đóng)
     */
    public function canSell()
    {
        return !$this->is_closed && $this->price > 0 && $this->is_available && $this->available_rooms > 0;
    }

    /**
     * Lấy số phòng available (from room_inventories logic)
     */
    public function getAvailableRoomsAttribute()
    {
        return max(0, $this->total_for_sale - $this->booked_rooms);
    }

    /**
     * Kiểm tra còn phòng trống không
     */
    public function hasAvailability()
    {
        return $this->is_available && $this->available_rooms > 0;
    }

    /**
     * Đặt phòng (tăng booked_rooms)
     */
    public function bookRooms($quantity)
    {
        if ($this->available_rooms >= $quantity) {
            $this->increment('booked_rooms', $quantity);
            return true;
        }
        return false;
    }

    /**
     * Hủy đặt phòng (giảm booked_rooms)
     */
    public function cancelRooms($quantity)
    {
        $this->decrement('booked_rooms', min($quantity, $this->booked_rooms));
    }

    /**
     * Cập nhật inventory
     */
    public static function updateInventory($hotelId, $roomtypeId, $date, $data)
    {
        return self::updateOrCreate(
            [
                'hotel_id' => $hotelId,
                'roomtype_id' => $roomtypeId,
                'date' => $date,
            ],
            $data
        );
    }
}
