<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Arrival extends Model
{
    use HasFactory;

    protected $fillable = ['route_id', 'stop_key', 'vehicle_key', 'minutes', 'live'];

    public function route(): BelongsTo
    {
        return $this->belongsTo(TransitRoute::class, 'route_id');
    }
}
