<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            // Cập nhật các trường hiện tại từ JSON thành đơn lẻ và thêm JSON cho đa ngôn ngữ

            // Các trường đa ngôn ngữ (JSON) - cập nhật và thêm mới
            $table->json('policy')->nullable()->after('address'); // Quy định check-in/check-out
            $table->json('description')->nullable()->after('policy'); // Mô tả chi tiết
            $table->json('short_description')->nullable()->after('description'); // Mô tả ngắn
            $table->json('amenities')->nullable()->after('short_description'); // Tiện nghi
            $table->json('facilities')->nullable()->after('amenities'); // Cơ sở vật chất
            $table->json('services')->nullable()->after('facilities'); // Dịch vụ
            $table->json('nearby_attractions')->nullable()->after('services'); // Điểm tham quan
            $table->json('transportation')->nullable()->after('nearby_attractions'); // Phương tiện di chuyển
            $table->json('dining_options')->nullable()->after('transportation'); // Lựa chọn ăn uống
            $table->json('room_features')->nullable()->after('dining_options'); // Đặc điểm phòng
            $table->json('cancellation_policy')->nullable()->after('room_features'); // Chính sách hủy
            $table->json('terms_conditions')->nullable()->after('cancellation_policy'); // Điều khoản
            $table->json('special_notes')->nullable()->after('terms_conditions'); // Ghi chú đặc biệt

            // Thay đổi các trường hiện tại từ JSON sang text (vì chúng không cần đa ngôn ngữ)
            $table->string('phone_number')->nullable()->after('special_notes'); // Số điện thoại mới
            $table->string('email_address')->nullable()->after('phone_number'); // Email mới
            $table->text('google_map')->nullable()->after('email_address'); // Map mới

            // Các trường chung mới (không đa ngôn ngữ)
            $table->string('domain_name')->nullable()->after('google_map');
            $table->string('fax')->nullable()->after('domain_name');
            $table->string('website')->nullable()->after('fax');
            $table->string('tax_code')->nullable()->after('website');
            $table->string('business_license')->nullable()->after('tax_code');
            $table->tinyInteger('star_rating')->nullable()->after('business_license');
            $table->year('established_year')->nullable()->after('star_rating');
            $table->integer('total_rooms')->nullable()->after('established_year');
            $table->time('check_in_time')->default('14:00')->after('total_rooms');
            $table->time('check_out_time')->default('12:00')->after('check_in_time');
            $table->string('currency', 3)->default('VND')->after('check_out_time');
            $table->string('timezone')->default('Asia/Ho_Chi_Minh')->after('currency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            // Xóa các trường đã thêm (theo thứ tự ngược lại)
            $table->dropColumn([
                'timezone', 'currency', 'check_out_time', 'check_in_time',
                'total_rooms', 'established_year', 'star_rating', 'business_license',
                'tax_code', 'website', 'fax', 'domain_name',
                'google_map', 'email_address', 'phone_number',
                'special_notes', 'terms_conditions', 'cancellation_policy',
                'room_features', 'dining_options', 'transportation',
                'nearby_attractions', 'services', 'facilities', 'amenities',
                'short_description', 'description', 'policy'
            ]);
        });
    }
};
