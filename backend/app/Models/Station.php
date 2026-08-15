<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    use HasFactory;

    protected $fillable = [
        'uid', 'name', 'lat', 'lon', 'aqi', 'pm25', 'pm10', 'no2', 'co', 'o3', 'so2',
        'wind_speed', 'wind_dir', 'temperature', 'humidity', 'pressure', 'flag',
    ];
}
