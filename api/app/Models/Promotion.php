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
