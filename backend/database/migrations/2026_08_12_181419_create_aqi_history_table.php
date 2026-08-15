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
        Schema::create('aqi_history', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('lat', 8, 4)->nullable();
            $table->decimal('lon', 8, 4)->nullable();
            $table->unsignedSmallInteger('aqi')->nullable();
            $table->string('source', 16)->default('waqi'); // waqi | iqair
            $table->timestamp('recorded_at');
            $table->index(['name', 'recorded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aqi_history');
    }
};
