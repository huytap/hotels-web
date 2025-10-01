<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomPricingPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'roomtype_id',
        'base_occupancy',
        'additional_adult_price',
        'child_surcharge_price',
        'is_active'
    ];

    protected $casts = [
        'additional_adult_price' => 'decimal:2',
        'child_surcharge_price' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    /**
     * Quan hệ với Roomtype
     */
    public function roomtype(): BelongsTo
    {
        return $this->belongsTo(Roomtype::class);
    }

    /**
     * Tính giá cho người lớn thêm
     */
    public function calculateAdditionalAdultPrice(int $additionalAdults): float
    {
        return $additionalAdults * $this->additional_adult_price;
    }

    /**
     * Tính giá phụ thu trẻ em
     */
    public function calculateChildSurchargePrice(int $surchargeChildren): float
    {
        return $surchargeChildren * $this->child_surcharge_price;
    }

    /**
     * Tính tổng giá phụ thu
     */
    public function calculateTotalSurcharge(int $additionalAdults, int $surchargeChildren): float
    {
        return $this->calculateAdditionalAdultPrice($additionalAdults) +
               $this->calculateChildSurchargePrice($surchargeChildren);
    }
}
