<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomInventory extends Model
{
    use HasFactory;

    /**
     * Tên bảng liên kết với model.
     *
     * @var string
     */
    protected $table = 'room_inventories';

    /**
     * Các thuộc tính có thể gán hàng loạt.
     *
     * @var array
     */
    protected $fillable = [
        'hotel_id',
        'roomtype_id',
        'date',
        'total_for_sale',
        'booked_rooms',
        'is_available',
    ];

    /**
     * Các thuộc tính sẽ được ép kiểu tự động.
     *
     * @var array
     */
    protected $casts = [
        'date' => 'date'
    ];

    /**
     * Lấy khách sạn mà bản ghi tồn kho thuộc về.
     */
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Lấy loại phòng mà bản ghi tồn kho thuộc về.
     */
    public function roomType()
    {
        return $this->belongsTo(Roomtype::class, 'roomtype_id');
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
     * Lấy inventory cho ngày cụ thể
     */
    public static function getInventoryForDate($hotelId, $roomtypeId, $date)
    {
        return self::where('hotel_id', $hotelId)
            ->where('roomtype_id', $roomtypeId)
            ->where('date', $date)
            ->first();
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

    /**
     * Lấy số phòng available
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
     * Đóng/mở bán phòng
     */
    public function toggleAvailability()
    {
        $this->update(['is_available' => !$this->is_available]);
    }
}
