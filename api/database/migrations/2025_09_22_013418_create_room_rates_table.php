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
        Schema::create('room_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->onDelete('cascade');
            $table->foreignId('roomtype_id')->constrained('roomtypes')->onDelete('cascade');
            $table->date('date')->comment('Ngày áp dụng mức giá');
            $table->decimal('price', 10, 2)->comment('Giá cơ bản cho ngày đó');
            $table->timestamps();

            // Thêm ràng buộc duy nhất cho cặp roomtype_id và date
            $table->unique(['roomtype_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_rates');
    }
};
