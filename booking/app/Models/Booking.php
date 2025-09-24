<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'hotel_id',
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'address',
        'notes',
        'check_in',
        'check_out',
        'status',
        'is_sentmail'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'is_sentmail' => 'boolean'
    ];

    /**
     * Get the hotel that the booking belongs to.
     */
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
    public function bookingDetails()
    {
        return $this->hasMany(BookingDetail::class);
    }
}
