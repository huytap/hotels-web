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
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('booking_number')->after('hotel_id')->nullable();
            $table->datetime('confirmed_at')->nullable();
            $table->datetime('cancelled_at')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->integer('nights')->nullable();
            $table->integer('guests')->nullable();
            $table->decimal('total_amount', 16, 2)->nullable();
            $table->decimal('discount_amount', 10, 2)->nullable();
            $table->decimal('tax_amount', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['booking_number', 'confirmed_at', 'cancelled_at', 'completed_at', 'cancellation_reason', 'nights', 'guests', 'total_amount', 'discount_amount', 'tax_amount']);
        });
    }
};
