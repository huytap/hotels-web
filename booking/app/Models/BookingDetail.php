<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingDetail extends Model
{
    use HasFactory;

    /**
     * Tên bảng liên kết với model.
     *
     * @var string
     */
    protected $table = 'booking_details';

    /**
     * Các thuộc tính có thể gán hàng loạt.
     *
     * @var array
     */
    protected $fillable = [
        'booking_id',
        'roomtype_id',
        'adults',
        'children',
        'is_extra_bed_requested',
        'quantity',
        'price_per_night',
        'sub_total',
        'promotion_id',
    ];

    /**
     * Các thuộc tính nên được chuyển đổi sang kiểu dữ liệu cụ thể.
     *
     * @var array
     */
    protected $casts = [
        'is_extra_bed_requested' => 'boolean',
    ];

    /**
     * Quan hệ với Booking.
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Quan hệ với Roomtype.
     */
    public function roomtype()
    {
        return $this->belongsTo(Roomtype::class);
    }

    /**
     * Quan hệ với Promotion.
     */
    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }
}
