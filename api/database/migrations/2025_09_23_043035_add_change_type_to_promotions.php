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
        Schema::table('promotions', function (Blueprint $table) {
            $table->string('type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Có thể cần chỉ rõ các giá trị ENUM ban đầu của bạn
        Schema::table('promotions', function (Blueprint $table) {
            $table->enum('type', ['Early Bird', 'Last minutes', 'Others'])->nullable()->change();
        });
    }
};
