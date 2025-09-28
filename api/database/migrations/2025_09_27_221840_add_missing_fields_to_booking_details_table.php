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
        Schema::table('booking_details', function (Blueprint $table) {
            // Add important booking calculation fields
            $table->integer('nights')->comment('Số đêm ở');
            $table->decimal('tax_amount', 10, 2)->default(0)->comment('Tiền thuế');
            $table->decimal('discount_amount', 10, 2)->default(0)->comment('Tiền giảm giá');
            $table->decimal('total_amount', 10, 2)->comment('Tổng tiền cuối cùng');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_details', function (Blueprint $table) {
            $table->dropColumn(['nights', 'tax_amount', 'discount_amount', 'total_amount']);
        });
    }
};
