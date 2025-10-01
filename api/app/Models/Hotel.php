<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Hotel extends Model
{
    use HasFactory, HasTranslations;

    /**
     * Tên bảng trong database.
     *
     * @var string
     */
    protected $table = 'hotels';

    /**
     * Primary key của model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Tắt tự động tăng ID, vì ID được cung cấp từ WordPress.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Khai báo các thuộc tính có thể dịch được.
     *
     * @var array
     */
    public $translatable = [
        // Các trường đa ngôn ngữ
        'name',
        'address',
        'policy',
        'description',
        'short_description',
        'amenities',
        'facilities',
        'services',
        'nearby_attractions',
        'transportation',
        'dining_options',
        'room_features',
        'cancellation_policy',
        'terms_conditions',
        'special_notes'
    ];

    /**
     * Các trường có thể được gán hàng loạt.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'wp_id',
        // Các trường đa ngôn ngữ
        'name',
        'address',
        'policy',
        'description',
        'short_description',
        'amenities',
        'facilities',
        'services',
        'nearby_attractions',
        'transportation',
        'dining_options',
        'room_features',
        'cancellation_policy',
        'terms_conditions',
        'special_notes',
        // Các trường chung (không đa ngôn ngữ)
        'phone_number',
        'email_address',
        'google_map',
        'domain_name',
        'fax',
        'website',
        'tax_code',
        'business_license',
        'star_rating',
        'established_year',
        'total_rooms',
        'check_in_time',
        'check_out_time',
        'currency',
        'timezone',
        'is_active',
        'vat_rate',
        'service_charge_rate',
        'prices_include_tax',
        'wp_updated_at'
    ];

    /**
     * Các trường được chuyển đổi sang kiểu dữ liệu cụ thể.
     *
     * @var array
     */
    protected $casts = [
        // Các trường đa ngôn ngữ (JSON)
        'name' => 'array',
        'address' => 'array',
        'policy' => 'array',
        'description' => 'array',
        'short_description' => 'array',
        'amenities' => 'array',
        'facilities' => 'array',
        'services' => 'array',
        'nearby_attractions' => 'array',
        'transportation' => 'array',
        'dining_options' => 'array',
        'room_features' => 'array',
        'cancellation_policy' => 'array',
        'terms_conditions' => 'array',
        'special_notes' => 'array',
        // Các trường khác
        'star_rating' => 'integer',
        'established_year' => 'integer',
        'total_rooms' => 'integer',
        'vat_rate' => 'decimal:2',
        'service_charge_rate' => 'decimal:2',
        'prices_include_tax' => 'boolean',
        'wp_updated_at' => 'datetime'
    ];
    /**
     * Một khách sạn có thể có nhiều loại phòng.
     */
    public function roomtypes()
    {
        return $this->hasMany(Roomtype::class);
    }

    /**
     * Một khách sạn có một chính sách độ tuổi trẻ em.
     */
    public function childAgePolicy()
    {
        return $this->hasOne(ChildAgePolicy::class);
    }
}
