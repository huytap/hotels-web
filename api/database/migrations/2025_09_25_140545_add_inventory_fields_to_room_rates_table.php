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
        Schema::table('room_rates', function (Blueprint $table) {
            // Inventory fields from room_inventories table
            $table->integer('total_for_sale')->default(0)->comment('Tổng số phòng có thể bán');
            $table->integer('booked_rooms')->default(0)->comment('Số phòng đã được đặt');
            $table->boolean('is_available')->default(true)->comment('Trạng thái mở/đóng bán cho loại phòng này vào ngày này');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_rates', function (Blueprint $table) {
            $table->dropColumn([
                'total_for_sale',
                'booked_rooms',
                'is_available'
            ]);
        });
    }
};
