<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Arrival;

class Stop extends Model
{
    use HasFactory;

    protected $fillable = ['route_id', 'stop_key', 'name', 'lat', 'lng'];

    public function route(): BelongsTo
    {
        return $this->belongsTo(TransitRoute::class, 'route_id');
    }

    public function arrivals(): HasMany
    {
        return $this->hasMany(Arrival::class, 'stop_key', 'stop_key');
    }
}
