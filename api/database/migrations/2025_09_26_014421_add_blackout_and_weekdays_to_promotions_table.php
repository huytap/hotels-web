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
        Schema::table('promotions', function (Blueprint $table) {
            // Blackout dates - Không áp dụng khuyến mãi trong khoảng này
            $table->date('blackout_start_date')->nullable()->comment('Ngày bắt đầu blackout');
            $table->date('blackout_end_date')->nullable()->comment('Ngày kết thúc blackout');

            // Valid days of week - Ngày trong tuần hợp lệ để áp dụng khuyến mãi
            $table->boolean('valid_monday')->default(true)->comment('Hợp lệ vào thứ 2');
            $table->boolean('valid_tuesday')->default(true)->comment('Hợp lệ vào thứ 3');
            $table->boolean('valid_wednesday')->default(true)->comment('Hợp lệ vào thứ 4');
            $table->boolean('valid_thursday')->default(true)->comment('Hợp lệ vào thứ 5');
            $table->boolean('valid_friday')->default(true)->comment('Hợp lệ vào thứ 6');
            $table->boolean('valid_saturday')->default(true)->comment('Hợp lệ vào thứ 7');
            $table->boolean('valid_sunday')->default(true)->comment('Hợp lệ vào chủ nhật');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->dropColumn([
                'blackout_start_date',
                'blackout_end_date',
                'valid_monday',
                'valid_tuesday',
                'valid_wednesday',
                'valid_thursday',
                'valid_friday',
                'valid_saturday',
                'valid_sunday'
            ]);
        });
    }
};
