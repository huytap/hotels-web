<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Roomtype extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'wp_id',
        'hotel_id',
        'sync_id',
        'title',
        'name',
        'description',
        'area',
        'adult_capacity',
        'child_capacity',
        'max_guests',
        'bed_type',
        'is_extra_bed_available',
        'amenities',
        'room_amenities',
        'bathroom_amenities',
        'view',
        'gallery_images',
        'featured_image',
        'base_price',
        'inventory',
        'is_active',
        'last_updated'
    ];

    public $translatable = [
        'title',
        'description',
        'area',
        'bed_type',
        'amenities',
        'room_amenities',
        'bathroom_amenities',
        'view'
    ];

    protected $casts = [
        'gallery_images' => 'array',
        'amenities' => 'array',
        'room_amenities' => 'array',
        'bathroom_amenities' => 'array',
        'base_price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_extra_bed_available' => 'boolean',
    ];
    public function promotions()
    {
        return $this->belongsToMany(Promotion::class, 'promotion_roomtype');
    }

    /**
     * Get the hotel that owns the room type
     */
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Check if room type is available for given dates and guest count
     */
    public function isAvailable($checkIn, $checkOut, $guests = 1)
    {
        // Get pricing policy for this room type
        $pricingPolicy = $this->pricingPolicy;
        if (!$pricingPolicy) {
            // If no pricing policy, fall back to basic adult_capacity check
            return $guests <= $this->adult_capacity;
        }

        // Calculate effective capacity using same logic as BookingService
        $baseOccupancy = $pricingPolicy->base_occupancy ?? 2;
        $additionalAdultPrice = $pricingPolicy->additional_adult_price ?? 0;

        // Base capacity: số người lớn cơ bản
        $effectiveCapacity = $baseOccupancy;

        // Thêm capacity cho người lớn phụ thu (nếu có additional_adult_price)
        if ($additionalAdultPrice > 0) {
            $effectiveCapacity += 2; // Cho phép thêm tối đa 2 người lớn/phòng
        }

        // Thêm capacity cho trẻ em (nếu có extra bed hoặc child capacity)
        $childCapacity = $this->child_capacity ?? 0;
        $hasExtraBed = $this->is_extra_bed_available ?? false;

        if ($hasExtraBed || $childCapacity > 0) {
            // Nếu có giường phụ hoặc child capacity, cho phép thêm trẻ em
            $effectiveCapacity += max($childCapacity, 1); // Ít nhất 1 trẻ em
        }

        // Check if guests can be accommodated with effective capacity
        if ($guests > $effectiveCapacity) {
            return false;
        }

        // For now, assume room type is available if it exists and capacity allows
        // In a real implementation, you would check against existing bookings and room_rates
        return true;
    }

    /**
     * Get the name attribute (fallback to title if name is not set)
     */
    public function getNameAttribute()
    {
        return $this->attributes['name'] ?? $this->title;
    }

    /**
     * Get max guests (total of adult and child capacity)
     */
    public function getMaxGuestsAttribute()
    {
        return $this->adult_capacity + $this->child_capacity;
    }

    /**
     * Một loại phòng có một chính sách giá.
     */
    public function pricingPolicy()
    {
        return $this->hasOne(RoomPricingPolicy::class);
    }

    // public function scopeActive($query)
    // {
    //     return $query->where('is_active', true);
    // }
}
