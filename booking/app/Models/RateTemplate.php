<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RateTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'roomtype_id',
        'name',
        'description',
        'rates',
        'min_stay',
        'max_stay',
        'is_active',
        // Restrictions
        'close_to_arrival',
        'close_to_departure',
        'is_closed',
    ];

    protected $casts = [
        'rates' => 'json',
        'min_stay' => 'integer',
        'max_stay' => 'integer',
        'is_active' => 'boolean',
        // Restrictions
        'close_to_arrival' => 'boolean',
        'close_to_departure' => 'boolean',
        'is_closed' => 'boolean',
    ];

    /**
     * Get the hotel that the rate template belongs to.
     */
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Get the room type that the rate template belongs to.
     */
    public function roomtype()
    {
        return $this->belongsTo(Roomtype::class, 'roomtype_id');
    }

    /**
     * Scope để lọc theo khách sạn
     */
    public function scopeForHotel($query, $hotelId)
    {
        return $query->where('hotel_id', $hotelId);
    }

    /**
     * Scope để lọc theo loại phòng
     */
    public function scopeForRoomtype($query, $roomtypeId)
    {
        return $query->where('roomtype_id', $roomtypeId);
    }

    /**
     * Scope để lọc mẫu đang hoạt động
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Lấy giá theo thứ trong tuần
     */
    public function getRateForWeekday($weekday)
    {
        $rates = $this->rates ?? [];
        return $rates[$weekday] ?? 0;
    }

    /**
     * Thiết lập giá cho các thứ trong tuần
     */
    public function setWeekdayRates($weekdayRates)
    {
        $this->rates = $weekdayRates;
        return $this;
    }

    /**
     * Lấy tất cả giá theo thứ có định dạng
     */
    public function getFormattedRatesAttribute()
    {
        $rates = $this->rates ?? [];
        $formatted = [];

        $weekdays = [
            'monday' => 'Thứ 2',
            'tuesday' => 'Thứ 3',
            'wednesday' => 'Thứ 4',
            'thursday' => 'Thứ 5',
            'friday' => 'Thứ 6',
            'saturday' => 'Thứ 7',
            'sunday' => 'Chủ nhật'
        ];

        foreach ($weekdays as $key => $label) {
            $price = $rates[$key] ?? 0;
            $formatted[$key] = [
                'label' => $label,
                'price' => $price,
                'formatted' => number_format($price, 0, ',', '.') . ' VND'
            ];
        }

        return $formatted;
    }

    /**
     * Kiểm tra có hạn chế không
     */
    public function hasRestrictions()
    {
        return $this->close_to_arrival || $this->close_to_departure || $this->is_closed;
    }

    /**
     * Lấy danh sách hạn chế
     */
    public function getRestrictionsAttribute()
    {
        $restrictions = [];
        if ($this->close_to_arrival) {
            $restrictions[] = 'CTA';
        }
        if ($this->close_to_departure) {
            $restrictions[] = 'CTD';
        }
        if ($this->is_closed) {
            $restrictions[] = 'CLOSED';
        }
        return $restrictions;
    }

    /**
     * Lấy nhãn hạn chế có định dạng
     */
    public function getFormattedRestrictionsAttribute()
    {
        $restrictions = [];
        if ($this->close_to_arrival) {
            $restrictions[] = 'Hạn chế check-in';
        }
        if ($this->close_to_departure) {
            $restrictions[] = 'Hạn chế check-out';
        }
        if ($this->is_closed) {
            $restrictions[] = 'Đóng bán';
        }
        return implode(', ', $restrictions);
    }
}