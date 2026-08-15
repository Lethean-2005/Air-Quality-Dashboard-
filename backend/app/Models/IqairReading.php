<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IqairReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'lat', 'lon', 'city', 'state', 'country', 'aqi', 'status',
        'pm25', 'pm10', 'pm_estimated',
        'temp_c', 'humidity_percent', 'pressure_hpa', 'wind_ms',
        'weather_description', 'weather_icon', 'uv_index', 'fetched_at',
    ];

    protected $casts = [
        'fetched_at' => 'datetime',
    ];
}
