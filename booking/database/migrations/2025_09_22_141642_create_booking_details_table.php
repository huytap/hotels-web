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
        Schema::create('booking_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->foreignId('roomtype_id')->constrained('roomtypes')->onDelete('cascade');
            $table->integer('adults')->comment('Số người lớn cho loại phòng này');
            $table->integer('children')->default(0)->comment('Số trẻ em cho loại phòng này');
            // Yêu cầu đặc biệt
            $table->boolean('is_extra_bed_requested')->default(false)->comment('Yêu cầu thêm giường phụ');
            // Thông tin giá và khuyến mãi riêng cho từng loại phòng
            $table->integer('quantity')->default(1)->comment('Số lượng phòng của loại này');
            $table->decimal('price_per_night', 10, 2)->comment('Giá mỗi đêm của loại phòng này');
            $table->decimal('sub_total', 10, 2)->comment('Tổng giá của loại phòng này trước khi giảm giá');
            $table->foreignId('promotion_id')->nullable()->constrained('promotions')->onDelete('set null')->comment('Mã khuyến mãi áp dụng cho loại phòng này');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_details', function (Blueprint $table) {
            Schema::dropIfExists('booking_details');
        });
    }
};
