<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = ['route_id', 'vehicle_key', 'label', 'lat', 'lng', 'live'];

    public function route(): BelongsTo
    {
        return $this->belongsTo(TransitRoute::class, 'route_id');
    }
}
