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
        'description', // Thêm trường mô tả
        'area',
        'adult_capacity',
        'child_capacity',
        'bed_type',
        'is_extra_bed_available',
        'amenities',
        'room_amenities',
        'bathroom_amenities',
        'view',
        'gallery_images',
        'featured_image',
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
    ];
    public function promotions()
    {
        return $this->belongsToMany(Promotion::class, 'promotion_roomtype');
    }
}
