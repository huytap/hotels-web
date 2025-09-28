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
        // Check guest capacity against adult_capacity
        if ($guests > $this->adult_capacity) {
            return false;
        }

        // For now, assume room type is available if it exists
        // In a real implementation, you would check against existing bookings
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

    // public function scopeActive($query)
    // {
    //     return $query->where('is_active', true);
    // }
}
