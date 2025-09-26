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
        Schema::dropIfExists('room_inventories');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate room_inventories table if rollback needed
        Schema::create('room_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->onDelete('cascade');
            $table->foreignId('roomtype_id')->constrained('roomtypes')->onDelete('cascade');
            $table->date('date')->comment('Ngày áp dụng tồn kho');
            $table->integer('total_for_sale')->default(0)->comment('Tổng số phòng có thể bán');
            $table->integer('booked_rooms')->default(0)->comment('Số phòng đã được đặt');
            $table->boolean('is_available')->default(true)->comment('Trạng thái mở/đóng bán cho loại phòng này vào ngày này');
            $table->timestamps();
            $table->unique(['hotel_id', 'roomtype_id', 'date']);
        });
    }
};
