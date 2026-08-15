<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AqiHistory extends Model
{
    protected $table = 'aqi_history';

    public $timestamps = false;

    protected $fillable = ['name', 'lat', 'lon', 'aqi', 'pm25', 'pm10', 'co', 'so2', 'no2', 'o3', 'source', 'recorded_at'];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];
}
