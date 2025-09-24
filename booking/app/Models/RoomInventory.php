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
        'date' => 'date',
        'is_available' => 'boolean',
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
        return $this->belongsTo(Roomtype::class);
    }
}
