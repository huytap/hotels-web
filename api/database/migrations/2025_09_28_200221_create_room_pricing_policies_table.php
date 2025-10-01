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
        Schema::create('room_pricing_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('roomtype_id')->constrained('roomtypes')->onDelete('cascade');
            $table->integer('base_occupancy')->default(2)->comment('Sức chứa cơ bản (mặc định 2 người)');
            $table->decimal('additional_adult_price', 10, 2)->default(0)->comment('Giá thêm người lớn');
            $table->decimal('child_surcharge_price', 10, 2)->default(0)->comment('Giá phụ thu trẻ em');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('roomtype_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_pricing_policies');
    }
};
