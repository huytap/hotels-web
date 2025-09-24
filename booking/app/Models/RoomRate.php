<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomRate extends Model
{
    use HasFactory;
    protected $fillable = [
        'hotel_id',
        'roomtype_id',
        'date',
        'price',
    ];

    protected $casts = [
        'date' => 'date',
        'price' => 'decimal:2',
    ];

    /**
     * Get the hotel that the room rate belongs to.
     */
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Get the room type that the room rate belongs to.
     */
    public function roomtype()
    {
        return $this->belongsTo(Roomtype::class);
    }
}
