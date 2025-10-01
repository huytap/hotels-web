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
        Schema::table('hotels', function (Blueprint $table) {
            $table->decimal('vat_rate', 5, 2)->default(10.00)->after('currency')->comment('VAT rate in percentage (%)');
            $table->decimal('service_charge_rate', 5, 2)->default(5.00)->after('vat_rate')->comment('Service charge rate in percentage (%)');
            $table->boolean('prices_include_tax')->default(false)->after('service_charge_rate')->comment('Whether displayed prices include VAT and service charge');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn(['vat_rate', 'service_charge_rate', 'prices_include_tax']);
        });
    }
};
