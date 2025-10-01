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
        Schema::create('child_age_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->onDelete('cascade');
            $table->integer('free_age_limit')->default(6)->comment('Tuổi miễn phí (ví dụ: dưới 6 tuổi)');
            $table->integer('surcharge_age_limit')->default(12)->comment('Tuổi phụ thu (ví dụ: dưới 12 tuổi)');
            $table->json('free_description')->nullable()->comment('Mô tả miễn phí đa ngôn ngữ');
            $table->json('surcharge_description')->nullable()->comment('Mô tả phụ thu đa ngôn ngữ');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('hotel_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('child_age_policies');
    }
};
