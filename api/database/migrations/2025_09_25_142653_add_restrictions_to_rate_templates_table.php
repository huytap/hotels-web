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
        Schema::table('rate_templates', function (Blueprint $table) {
            // Restrictions fields
            $table->boolean('close_to_arrival')->default(false)->comment('Hạn chế check-in (CTA)');
            $table->boolean('close_to_departure')->default(false)->comment('Hạn chế check-out (CTD)');
            $table->boolean('is_closed')->default(false)->comment('Đóng bán hoàn toàn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rate_templates', function (Blueprint $table) {
            $table->dropColumn([
                'close_to_arrival',
                'close_to_departure',
                'is_closed'
            ]);
        });
    }
};
