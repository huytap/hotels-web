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
        'name',
        'address',
        'phone',
        'email',
        'map'
    ];

    /**
     * Các trường có thể được gán hàng loạt.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'wp_id',
        'name',
        'address',
        'phone',
        'email',
        'map',
        'is_active',
        'wp_updated_at'
    ];

    /**
     * Các trường được chuyển đổi sang kiểu dữ liệu cụ thể.
     *
     * @var array
     */
    protected $casts = [
        'name'    => 'array',
        'address' => 'array',
        'phone'   => 'array',
        'email'   => 'array',
        'map'     => 'array'
    ];
    /**
     * Một khách sạn có thể có nhiều loại phòng.
     */
    public function roomtypes()
    {
        return $this->hasMany(Roomtype::class);
    }
}
