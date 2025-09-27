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
            // Stay limits
            $table->integer('min_stay')->default(1)->comment('Số đêm lưu trú tối thiểu');
            $table->integer('max_stay')->default(30)->comment('Số đêm lưu trú tối đa');

            // Restrictions
            $table->boolean('close_to_arrival')->default(false)->comment('Hạn chế check-in (CTA)');
            $table->boolean('close_to_departure')->default(false)->comment('Hạn chế check-out (CTD)');
            $table->boolean('is_closed')->default(false)->comment('Đóng bán hoàn toàn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_rates', function (Blueprint $table) {
            $table->dropColumn([
                'min_stay',
                'max_stay',
                'close_to_arrival',
                'close_to_departure',
                'is_closed'
            ]);
        });
    }
};
