<?php

namespace App\Data;

use App\Models\Arrival;
use App\Models\Notice;
use App\Models\Stop;
use App\Models\TransitRoute;
use App\Models\Vehicle;

class TransitRepository
{
    private const ON_TIME_MINUTES = 15;

    private function normalize(?string $value): ?string
    {
        $trimmed = $value ? trim(mb_strtolower($value)) : null;
        return $trimmed !== '' ? $trimmed : null;
    }

    private function bestStopMatch($stops, string $term): ?array
    {
        $term = $this->normalize($term);
        if (!$term) {
            return null;
        }

        $best = null;
        foreach ($stops as $stop) {
            $name = mb_strtolower($stop->name);
            if (str_contains($name, $term)) {
                $score = $name === $term ? 3 : 2;
                if (!$best || $score > $best['score']) {
                    $best = [
                        'stop' => [
                            'id' => $stop->stop_key,
                            'name' => $stop->name,
                            'lat' => $stop->lat,
                            'lng' => $stop->lng,
                        ],
                        'score' => $score,
                    ];
                }
            }
        }

        return $best;
    }

    private function computeOnTimeRate(string $routeId): float
    {
        $arrivals = Arrival::where('route_id', $routeId)->get();
        $total = $arrivals->count();
        if ($total === 0) {
            return 0.0;
        }

        $onTime = $arrivals
            ->filter(fn($a) => (int) $a->minutes <= self::ON_TIME_MINUTES)
            ->count();

        return $onTime / $total;
    }

    public function searchRoutes(string $from = null, string $to = null): array
    {
        $fromTerm = $this->normalize($from);
        $toTerm = $this->normalize($to);

        $routes = TransitRoute::with('stops')->get();
        $results = [];

        foreach ($routes as $route) {
            $fromMatch = $fromTerm ? $this->bestStopMatch($route->stops, $fromTerm) : null;
            $toMatch = $toTerm ? $this->bestStopMatch($route->stops, $toTerm) : null;

            $fromNameScore = $fromTerm && str_contains(mb_strtolower($route->name), $fromTerm) ? 1 : 0;
            $toNameScore = $toTerm && str_contains(mb_strtolower($route->name), $toTerm) ? 1 : 0;

            $hasFrom = $fromTerm ? ((bool) $fromMatch || $fromNameScore > 0) : true;
            $hasTo = $toTerm ? ((bool) $toMatch || $toNameScore > 0) : true;

            if ($fromTerm && $toTerm && (!$hasFrom || !$hasTo)) {
                continue; // require both when both queries are present
            }

            if (($fromTerm || $toTerm) && !$hasFrom && !$hasTo) {
                continue; // nothing matches
            }

            $computedRate = $this->computeOnTimeRate($route->id);

            $results[] = [
                'id' => $route->id,
                'name' => $route->name,
                'on_time_rate' => $computedRate ?: $route->on_time_rate,
                'from_stop' => $fromMatch['stop'] ?? null,
                'to_stop' => $toMatch['stop'] ?? null,
                'match_score' => ($fromMatch['score'] ?? 0) + ($toMatch['score'] ?? 0) + $fromNameScore + $toNameScore,
            ];
        }

        usort($results, function ($a, $b) {
            if ($a['match_score'] === $b['match_score']) {
                return strcmp($a['name'], $b['name']);
            }
            return $a['match_score'] < $b['match_score'] ? 1 : -1;
        });

        return $results;
    }

    public function getRoute(string $routeId): ?array
    {
        $route = TransitRoute::with(['stops', 'vehicles', 'notices'])->find($routeId);

        if (!$route) {
            return null;
        }

        $computedRate = $this->computeOnTimeRate($routeId);

        return [
            'id' => $route->id,
            'name' => $route->name,
            'on_time_rate' => $computedRate ?: $route->on_time_rate,
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
