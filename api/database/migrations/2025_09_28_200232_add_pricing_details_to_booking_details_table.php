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
        Schema::table('booking_details', function (Blueprint $table) {
            $table->json('children_ages')->nullable()->comment('Mảng tuổi trẻ em cho phòng này [5, 10]');
            $table->decimal('additional_adult_price', 10, 2)->default(0)->comment('Giá người lớn thêm cho phòng này');
            $table->decimal('child_surcharge_price', 10, 2)->default(0)->comment('Giá phụ thu trẻ em cho phòng này');
            $table->decimal('total_additional_charges', 10, 2)->default(0)->comment('Tổng phụ thu cho phòng này');
            $table->integer('additional_adult_count')->default(0)->comment('Số người lớn thêm trong phòng này');
            $table->integer('child_surcharge_count')->default(0)->comment('Số trẻ em bị phụ thu trong phòng này');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_details', function (Blueprint $table) {
            $table->dropColumn([
                'children_ages',
                'additional_adult_price',
                'child_surcharge_price',
                'total_additional_charges',
                'additional_adult_count',
                'child_surcharge_count'
            ]);
        });
    }
};
