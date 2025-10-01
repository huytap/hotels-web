<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class ChildAgePolicy extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'hotel_id',
        'free_age_limit',
        'surcharge_age_limit',
        'free_description',
        'surcharge_description',
        'is_active'
    ];

    protected $casts = [
        'free_description' => 'array',
        'surcharge_description' => 'array',
        'is_active' => 'boolean'
    ];

    public $translatable = [
        'free_description',
        'surcharge_description'
    ];

    /**
     * Quan hệ với Hotel
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Kiểm tra trẻ em có miễn phí không
     */
    public function isChildFree(int $age): bool
    {
        return $age < $this->free_age_limit;
    }

    /**
     * Kiểm tra trẻ em có bị phụ thu không
     */
    public function isChildSurcharge(int $age): bool
    {
        return $age >= $this->free_age_limit && $age < $this->surcharge_age_limit;
    }

    /**
     * Kiểm tra trẻ em có tính như người lớn không
     */
    public function isChildAsAdult(int $age): bool
    {
        return $age >= $this->surcharge_age_limit;
    }
}
