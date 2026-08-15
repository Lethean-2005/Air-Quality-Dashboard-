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
        Schema::create('stations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('uid')->unique(); // WAQI station uid
            $table->string('name');
            $table->double('lat')->nullable();
            $table->double('lon')->nullable();
            $table->string('aqi')->nullable(); // WAQI sometimes returns "-" for unavailable
            $table->float('pm25')->nullable();
            $table->float('pm10')->nullable();
            $table->float('no2')->nullable();
            $table->float('co')->nullable();
            $table->float('o3')->nullable();
            $table->float('so2')->nullable();
            $table->float('wind_speed')->nullable();
            $table->float('wind_dir')->nullable();
            $table->float('temperature')->nullable();
            $table->float('humidity')->nullable();
            $table->float('pressure')->nullable();
            $table->string('flag')->nullable();
            $table->timestamps();

            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stations');
    }
};
