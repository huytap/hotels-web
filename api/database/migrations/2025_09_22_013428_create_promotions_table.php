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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->onDelete('cascade');
            $table->string('promotion_code')->unique()->comment('Mã khuyến mãi');
            $table->json('name')->comment('Tên chương trình');
            $table->json('description')->nullable();
            $table->enum('type', ['Early Bird', 'Last minutes', 'Others'])->nullable()->comment('Loại khuyến mãi');
            $table->enum('value_type', ['percentage', 'fixed'])->default('percentage')->comment('Loại giảm giá: theo % hoặc cố định');
            $table->decimal('value', 10, 2)->comment('Giá trị giảm giá');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
