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
        Schema::create('promotion_roomtype', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained('promotions')->onDelete('cascade');

            // Corrected foreign key to reference your 'roomtypes' table
            $table->foreignId('roomtype_id')->constrained('roomtypes')->onDelete('cascade');

            $table->timestamps();

            // Ensure no duplicate records
            $table->unique(['promotion_id', 'roomtype_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotion_roomtype');
    }
};
