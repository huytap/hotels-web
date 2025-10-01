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
        Schema::table('bookings', function (Blueprint $table) {
            $table->json('children_ages')->nullable()->comment('Mảng tuổi trẻ em [5, 10, 12]');
            $table->integer('additional_adult_count')->default(0)->comment('Số người lớn thêm');
            $table->integer('child_surcharge_count')->default(0)->comment('Số trẻ em bị phụ thu');
            $table->decimal('additional_adult_total', 10, 2)->default(0)->comment('Tổng tiền người lớn thêm');
            $table->decimal('child_surcharge_total', 10, 2)->default(0)->comment('Tổng tiền phụ thu trẻ em');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'children_ages',
                'additional_adult_count',
                'child_surcharge_count',
                'additional_adult_total',
                'child_surcharge_total'
            ]);
        });
    }
};
