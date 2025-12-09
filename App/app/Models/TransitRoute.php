<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Stop;
use App\Models\Vehicle;
use App\Models\Notice;
use App\Models\Arrival;

class TransitRoute extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id', 'name', 'on_time_rate'];

    public function stops(): HasMany
    {
        return $this->hasMany(Stop::class, 'route_id');
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'route_id');
    }

    public function notices(): HasMany
    {
        return $this->hasMany(Notice::class, 'route_id');
    }

    public function arrivals(): HasMany
    {
        return $this->hasMany(Arrival::class, 'route_id');
    }
}
