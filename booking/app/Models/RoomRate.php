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
    ];

    protected $casts = [
        'date' => 'date',
        'price' => 'decimal:2',
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
}
