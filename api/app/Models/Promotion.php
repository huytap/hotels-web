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
        'min_stay',
        'max_stay',
        // Blackout dates
        'blackout_start_date',
        'blackout_end_date',
        // Valid weekdays
        'valid_monday',
        'valid_tuesday',
        'valid_wednesday',
        'valid_thursday',
        'valid_friday',
        'valid_saturday',
        'valid_sunday',
    ];

    public $translatable = ['name', 'description'];

    /**
     * Scope to get only active promotions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('start_date')
                          ->orWhere('start_date', '<=', now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('end_date')
                          ->orWhere('end_date', '>=', now());
                    });
    }

    /**
     * Scope to filter promotions by hotel
     */
    public function scopeForHotel($query, $hotelId)
    {
        return $query->where('hotel_id', $hotelId);
    }

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

    /**
     * Check if promotion is valid for given date
     */
    public function isValidForDate($date)
    {
        $checkDate = is_string($date) ? \Carbon\Carbon::parse($date) : $date;

        // Check if date is within blackout period
        if ($this->blackout_start_date && $this->blackout_end_date) {
            $blackoutStart = \Carbon\Carbon::parse($this->blackout_start_date);
            $blackoutEnd = \Carbon\Carbon::parse($this->blackout_end_date);

            if ($checkDate->between($blackoutStart, $blackoutEnd)) {
                return false;
            }
        }

        // Check if day of week is valid
        $dayOfWeek = strtolower($checkDate->format('l')); // monday, tuesday, etc.
        $validDayField = 'valid_' . $dayOfWeek;

        return $this->getAttribute($validDayField) ?? true;
    }

    /**
     * Validate promotion for booking dates and room type
     */
    public function isValid($checkIn, $checkOut, $roomTypeId = null)
    {
        $checkInDate = is_string($checkIn) ? \Carbon\Carbon::parse($checkIn) : $checkIn;
        $checkOutDate = is_string($checkOut) ? \Carbon\Carbon::parse($checkOut) : $checkOut;

        // Check if promotion is active
        if (!$this->is_active) {
            return [
                'valid' => false,
                'reason' => 'Promotion is not active'
            ];
        }

        // Check promotion date range
        if ($this->start_date && $checkInDate->lt(\Carbon\Carbon::parse($this->start_date))) {
            return [
                'valid' => false,
                'reason' => 'Check-in date is before promotion start date'
            ];
        }

        if ($this->end_date && $checkOutDate->gt(\Carbon\Carbon::parse($this->end_date))) {
            return [
                'valid' => false,
                'reason' => 'Check-out date is after promotion end date'
            ];
        }

        // Check each date in the stay period
        $currentDate = $checkInDate->copy();
        while ($currentDate->lt($checkOutDate)) {
            if (!$this->isValidForDate($currentDate)) {
                return [
                    'valid' => false,
                    'reason' => 'Promotion not valid for date: ' . $currentDate->format('Y-m-d')
                ];
            }
            $currentDate->addDay();
        }

        // Check room type eligibility if specified
        if ($roomTypeId) {
            $isEligible = $this->roomtypes()->where('roomtypes.id', $roomTypeId)->exists();
            if (!$isEligible) {
                return [
                    'valid' => false,
                    'reason' => 'Promotion not applicable to this room type'
                ];
            }
        }

        // Check minimum/maximum stay requirements
        $nights = $checkInDate->diffInDays($checkOutDate);
        if ($this->min_stay && $nights < $this->min_stay) {
            return [
                'valid' => false,
                'reason' => "Minimum stay requirement: {$this->min_stay} nights"
            ];
        }

        if ($this->max_stay && $nights > $this->max_stay) {
            return [
                'valid' => false,
                'reason' => "Maximum stay limit: {$this->max_stay} nights"
            ];
        }

        return [
            'valid' => true,
            'reason' => null
        ];
    }

    /**
     * Calculate discount amount based on promotion type and value
     */
    public function calculateDiscount($subtotal, $nights = 1)
    {
        switch ($this->type) {
            case 'percentage':
                // Percentage discount
                $discountAmount = ($subtotal * $this->value) / 100;
                break;

            case 'fixed':
                // Fixed amount discount
                $discountAmount = $this->value;
                break;

            case 'fixed_per_night':
                // Fixed amount per night
                $discountAmount = $this->value * $nights;
                break;

            case 'buy_x_get_y':
                // Buy X nights get Y nights free (simplified calculation)
                if ($nights >= 3) { // Example: stay 3+ nights get 1 night free
                    $freeNights = floor($nights / 3);
                    $nightlyRate = $subtotal / $nights;
                    $discountAmount = $freeNights * $nightlyRate;
                } else {
                    $discountAmount = 0;
                }
                break;

            default:
                $discountAmount = 0;
                break;
        }

        // Ensure discount doesn't exceed subtotal
        return min($discountAmount, $subtotal);
    }

    /**
     * Get valid weekdays as array
     */
    public function getValidWeekdaysAttribute()
    {
        $validDays = [];
        $weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        foreach ($weekdays as $day) {
            if ($this->getAttribute('valid_' . $day)) {
                $validDays[] = $day;
            }
        }

        return $validDays;
    }

    /**
     * Check if promotion has blackout dates
     */
    public function hasBlackoutDates()
    {
        return $this->blackout_start_date && $this->blackout_end_date;
    }

    /**
     * Get blackout period as formatted string
     */
    public function getBlackoutPeriodAttribute()
    {
        if (!$this->hasBlackoutDates()) {
            return null;
        }

        return $this->blackout_start_date . ' - ' . $this->blackout_end_date;
    }
}
