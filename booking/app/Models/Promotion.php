<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Promotion extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'hotel_id',
        'promotion_code',
        'name',
        'description',
        'type',
        'value_type',
        'value',
        'start_date',
        'end_date',
        'is_active',
        'booking_days_in_advance',
        'min_stay',                // Thêm trường này
        'max_stay',                // Và trường này
    ];

    public $translatable = ['name', 'description'];

    // Quan hệ một-một với Hotel
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    // Quan hệ nhiều-nhiều với Roomtype
    public function roomtypes()
    {
        return $this->belongsToMany(Roomtype::class, 'promotion_roomtype');
    }
}
