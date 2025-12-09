<?php

namespace App\Data;

use App\Models\Arrival;
use App\Models\Notice;
use App\Models\Stop;
use App\Models\TransitRoute;
use App\Models\Vehicle;

class TransitRepository
{
    public function searchRoutes(string $from = null, string $to = null): array
    {
        $query = TransitRoute::query();

        if ($from || $to) {
            $query->where(function ($q) use ($from, $to) {
                if ($from) {
                    $q->where('name', 'like', "%{$from}%")
                        ->orWhereHas('stops', fn($s) => $s->where('name', 'like', "%{$from}%"));
                }
                if ($to) {
                    $q->orWhere('name', 'like', "%{$to}%")
                        ->orWhereHas('stops', fn($s) => $s->where('name', 'like', "%{$to}%"));
                }
            });
        }

        return $query
            ->with('stops')
            ->get()
            ->map(fn($route) => [
                'id' => $route->id,
                'name' => $route->name,
                'on_time_rate' => $route->on_time_rate,
            ])
            ->values()
            ->all();
    }

    public function getRoute(string $routeId): ?array
    {
        $route = TransitRoute::with(['stops', 'vehicles', 'notices'])->find($routeId);

        if (!$route) {
            return null;
        }

        return [
            'id' => $route->id,
            'name' => $route->name,
            'on_time_rate' => $route->on_time_rate,
            'stops' => $route->stops->map(fn($stop) => [
                'id' => $stop->stop_key,
                'name' => $stop->name,
                'lat' => $stop->lat,
                'lng' => $stop->lng,
            ])->all(),
            'vehicles' => $route->vehicles->map(fn($veh) => [
                'id' => $veh->vehicle_key,
                'label' => $veh->label,
                'lat' => $veh->lat,
                'lng' => $veh->lng,
                'live' => (bool) $veh->live,
            ])->all(),
            'notices' => $route->notices->map(fn($notice) => [
                'id' => $notice->id,
                'type' => $notice->type,
                'severity' => $notice->severity,
                'title' => $notice->title,
                'description' => $notice->description,
            ])->all(),
        ];
    }

    public function getStops(string $routeId): array
    {
        return Stop::where('route_id', $routeId)
            ->get()
            ->map(fn($stop) => [
                'id' => $stop->stop_key,
                'name' => $stop->name,
                'lat' => $stop->lat,
                'lng' => $stop->lng,
            ])
            ->all();
    }

    public function getArrivals(string $routeId, string $stopId): array
    {
        return Arrival::where('route_id', $routeId)
            ->where('stop_key', $stopId)
            ->orderBy('minutes')
            ->get()
            ->map(fn($arrival) => [
                'vehicle_id' => $arrival->vehicle_key,
                'minutes' => $arrival->minutes,
                'live' => (bool) $arrival->live,
            ])
            ->all();
    }

    public function getLiveVehicles(string $routeId): array
    {
        return Vehicle::where('route_id', $routeId)
            ->where('live', true)
            ->get()
            ->map(fn($veh) => [
                'id' => $veh->vehicle_key,
                'label' => $veh->label,
                'lat' => $veh->lat,
                'lng' => $veh->lng,
                'live' => (bool) $veh->live,
            ])
            ->all();
    }

    public function getNotices(string $routeId): array
    {
        return Notice::where('route_id', $routeId)
            ->get()
            ->map(fn($notice) => [
                'id' => $notice->id,
                'type' => $notice->type,
                'severity' => $notice->severity,
                'title' => $notice->title,
                'description' => $notice->description,
            ])
            ->all();
    }
}
