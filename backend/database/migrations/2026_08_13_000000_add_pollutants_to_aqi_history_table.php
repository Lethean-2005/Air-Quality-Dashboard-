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
        Schema::table('aqi_history', function (Blueprint $table) {
            $table->decimal('pm25', 8, 2)->nullable()->after('aqi');
            $table->decimal('pm10', 8, 2)->nullable()->after('pm25');
            $table->decimal('co', 8, 2)->nullable()->after('pm10');
            $table->decimal('so2', 8, 2)->nullable()->after('co');
            $table->decimal('no2', 8, 2)->nullable()->after('so2');
            $table->decimal('o3', 8, 2)->nullable()->after('no2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aqi_history', function (Blueprint $table) {
            $table->dropColumn(['pm25', 'pm10', 'co', 'so2', 'no2', 'o3']);
        });
    }
};
