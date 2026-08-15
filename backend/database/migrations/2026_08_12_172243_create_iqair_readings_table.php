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
        Schema::create('iqair_readings', function (Blueprint $table) {
            $table->id();
            // Coordinates rounded to ~1.1km precision (2 decimals) so nearby lookups
            // (GPS jitter) share the same row instead of each creating a new one.
            $table->decimal('lat', 8, 2);
            $table->decimal('lon', 8, 2);
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->integer('aqi')->nullable();
            $table->string('status')->nullable();
            $table->float('temp_c')->nullable();
            $table->integer('humidity_percent')->nullable();
            $table->integer('pressure_hpa')->nullable();
            $table->float('wind_ms')->nullable();
            $table->string('weather_description')->nullable();
            $table->string('weather_icon')->nullable();
            $table->float('uv_index')->nullable();
            $table->timestamp('fetched_at');
            $table->timestamps();

            $table->unique(['lat', 'lon']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iqair_readings');
    }
};
