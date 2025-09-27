<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionRoomType extends Model
{
    use HasFactory;

    /**
     * Tên bảng liên kết với model.
     *
     * @var string
     */
    protected $table = 'promotion_roomtype';

    /**
     * Các thuộc tính có thể gán hàng loạt.
     *
     * @var array
     */
    protected $fillable = [
        'promotion_id',
        'roomtype_id',
    ];

    /**
     * Quan hệ với Promotion.
     */
    public function promotion()
    {
        return $this->belongsTo(Promotion::class, 'promotion_id');
    }

    /**
     * Quan hệ với Roomtype.
     */
    public function roomtype()
    {
        // Sử dụng tên model Roomtype bạn đã tạo trước đó
        return $this->belongsTo(Roomtype::class, 'roomtype_id');
    }
}
