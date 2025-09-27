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
        Schema::create('roomtypes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->onDelete('cascade');
            $table->unsignedBigInteger('wp_id');
            $table->string('sync_id')->unique();
            $table->json('title');
            $table->string('featured_image')->nullable();
            $table->json('description')->nullable();
            $table->integer('room_number')->nullable();
            $table->integer('adult_capacity')->default(0);
            $table->integer('child_capacity')->default(0);
            $table->boolean('is_extra_bed_available')->default(false);
            $table->string('area')->nullable();
            $table->json('price')->nullable();
            $table->json('bed_type')->nullable();
            $table->json('view')->nullable(); // Lưu dưới dạng JSON
            $table->json('amenities')->nullable(); // Lưu dưới dạng JSON
            $table->json('room_amenities')->nullable(); // Lưu dưới dạng JSON
            $table->json('bathroom_amenities')->nullable(); // Lưu dưới dạng JSON
            $table->json('gallery_images')->nullable(); // Lưu dưới dạng JSON
            $table->timestamp('last_updated')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roomtypes');
    }
};
