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
        Schema::create('hotels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wp_id')->unique();; // Đã đổi tên cột
            $table->json('name')->nullable();
            $table->json('address')->nullable();
            $table->json('phone')->nullable();
            $table->json('email')->nullable();
            $table->json('map')->nullable();
            $table->timestamp('wp_updated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotels');
    }
};
