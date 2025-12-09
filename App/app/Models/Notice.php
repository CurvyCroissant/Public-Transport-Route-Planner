<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notice extends Model
{
    use HasFactory;

    protected $fillable = ['route_id', 'type', 'severity', 'title', 'description', 'starts_at', 'ends_at'];

    public function route(): BelongsTo
    {
        return $this->belongsTo(TransitRoute::class, 'route_id');
    }
}
