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
        Schema::table('iqair_readings', function (Blueprint $table) {
            // IQAir's free-tier endpoints only give the aggregate AQI + which pollutant is
            // dominant, never real PM2.5/PM10 concentrations. These are EPA-formula estimates
            // derived from the AQI value, not real sensor readings — pm_estimated says so.
            $table->float('pm25')->nullable()->after('aqi');
            $table->float('pm10')->nullable()->after('pm25');
            $table->boolean('pm_estimated')->default(false)->after('pm10');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('iqair_readings', function (Blueprint $table) {
            $table->dropColumn(['pm25', 'pm10', 'pm_estimated']);
        });
    }
};
