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
            $table->integer('booking_days_in_advance')->nullable()->after('end_date')->comment('Số ngày đặt phòng sớm/trễ so với ngày check-in. NULL nếu không áp dụng.');
            $table->integer('min_stay')->nullable()->after('booking_days_in_advance')->comment('Số đêm ở tối thiểu để áp dụng khuyến mãi.');
            $table->integer('max_stay')->nullable()->after('min_stay')->comment('Số đêm ở tối đa để áp dụng khuyến mãi.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->dropColumn('booking_days_in_advance');
            $table->dropColumn('min_stay');
            $table->dropColumn('max_stay');
        });
    }
};
