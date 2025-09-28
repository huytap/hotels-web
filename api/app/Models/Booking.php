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
        'booking_number',
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'nationality',
        'check_in',
        'check_out',
        'nights',
        'guests',
        'total_amount',
        'discount_amount',
        'tax_amount',
        'status',
        'notes',
        'confirmed_at',
        'cancelled_at',
        'completed_at',
        'cancellation_reason',
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
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'completed_at' => 'datetime',
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

    /**
     * Boot method to auto-generate booking number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->booking_number)) {
                $booking->booking_number = 'BK' . now()->format('YmdHis') . rand(100, 999);
            }
        });
    }

    /**
     * Check if booking can be modified
     */
    public function canBeModified()
    {
        return in_array($this->status, ['pending']);
    }

    /**
     * Check if booking can be cancelled
     */
    public function canBeCancelled()
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }
}
