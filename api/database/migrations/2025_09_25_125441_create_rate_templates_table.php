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
        Schema::create('rate_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hotel_id');
            $table->unsignedBigInteger('roomtype_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('rates'); // Stores weekday rates as JSON
            $table->integer('min_stay')->default(1);
            $table->integer('max_stay')->default(30);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('hotel_id')->references('id')->on('hotels')->onDelete('cascade');
            $table->foreign('roomtype_id')->references('id')->on('roomtypes')->onDelete('cascade');

            // Indexes
            $table->index(['hotel_id', 'is_active']);
            $table->index(['roomtype_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rate_templates');
    }
};
