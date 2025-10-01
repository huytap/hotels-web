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
        'children_ages',
        'additional_adult_price',
        'child_surcharge_price',
        'total_additional_charges',
        'additional_adult_count',
        'child_surcharge_count',
        'is_extra_bed_requested',
        'quantity',
        'price_per_night',
        'sub_total',
        'nights',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'promotion_id',
    ];

    /**
     * Các thuộc tính nên được chuyển đổi sang kiểu dữ liệu cụ thể.
     *
     * @var array
     */
    protected $casts = [
        'children_ages' => 'array',
        'additional_adult_price' => 'decimal:2',
        'child_surcharge_price' => 'decimal:2',
        'total_additional_charges' => 'decimal:2',
        'price_per_night' => 'decimal:2',
        'sub_total' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
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
    public function roomType()
    {
        return $this->belongsTo(Roomtype::class, 'roomtype_id');
    }

    /**
     * Quan hệ với Promotion.
     */
    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }
}
