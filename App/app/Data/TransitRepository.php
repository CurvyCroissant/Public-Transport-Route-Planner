<?php

namespace App\Data;

use App\Models\Arrival;
use App\Models\Notice;
use App\Models\Stop;
use App\Models\TransitRoute;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TransitRepository
{
    private const ON_TIME_MINUTES = 15;
    private const OSRM_BASE_URL = 'https://router.project-osrm.org';

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

    private function clamp(float $value, float $min, float $max): float
    {
        return max($min, min($max, $value));
    }

    private function approxDistanceMeters(array $a, array $b): float
    {
        $lat1 = deg2rad((float) $a[0]);
        $lng1 = deg2rad((float) $a[1]);
        $lat2 = deg2rad((float) $b[0]);
        $lng2 = deg2rad((float) $b[1]);

        $x = ($lng2 - $lng1) * cos(($lat1 + $lat2) / 2);
        $y = ($lat2 - $lat1);

        return sqrt(($x * $x) + ($y * $y)) * 6371000;
    }

    private function pointAlongPolyline(array $polyline, float $t): ?array
    {
        $count = count($polyline);
        if ($count === 0) {
            return null;
        }
        if ($count === 1) {
            return ['lat' => $polyline[0][0], 'lng' => $polyline[0][1]];
        }

        $t = $this->clamp($t, 0.0, 1.0);

        $segments = [];
        $total = 0.0;
        for ($i = 0; $i < $count - 1; $i++) {
            $d = $this->approxDistanceMeters($polyline[$i], $polyline[$i + 1]);
            $segments[] = $d;
            $total += $d;
        }

        if ($total <= 0) {
            return ['lat' => $polyline[0][0], 'lng' => $polyline[0][1]];
        }

        $target = $total * $t;
        $acc = 0.0;
        for ($i = 0; $i < count($segments); $i++) {
            $d = $segments[$i];
            if ($acc + $d >= $target) {
                $localT = $d > 0 ? (($target - $acc) / $d) : 0.0;
                $a = $polyline[$i];
                $b = $polyline[$i + 1];
                return [
                    'lat' => (float) $a[0] + ((float) $b[0] - (float) $a[0]) * $localT,
                    'lng' => (float) $a[1] + ((float) $b[1] - (float) $a[1]) * $localT,
                ];
            }
            $acc += $d;
        }

        return ['lat' => $polyline[$count - 1][0], 'lng' => $polyline[$count - 1][1]];
    }

    private function getRoadPolylineForRoute(string $routeId): array
    {
        $stops = Stop::where('route_id', $routeId)
            ->orderBy('id')
            ->get(['lat', 'lng']);

        $coords = $stops
            ->map(fn($s) => [
                is_null($s->lat) ? null : (float) $s->lat,
                is_null($s->lng) ? null : (float) $s->lng,
            ])
            ->filter(fn($p) => is_finite($p[0]) && is_finite($p[1]))
            ->values()
            ->all();

        if (count($coords) < 2) {
            return [];
        }

        $waypoints = implode(';', array_map(fn($p) => $p[1] . ',' . $p[0], $coords));
        $fingerprint = md5($waypoints);
        $cacheKey = "osrm:route-polyline:$routeId:$fingerprint";

        return Cache::remember($cacheKey, now()->addHours(12), function () use ($waypoints) {
            try {
                $url = self::OSRM_BASE_URL . "/route/v1/driving/$waypoints";
                $res = Http::timeout(4)
                    ->retry(1, 150)
                    ->get($url, [
                        'overview' => 'full',
                        'geometries' => 'geojson',
                    ]);

                if (!$res->ok()) {
                    return [];
                }

                $data = $res->json();
                $coords = $data['routes'][0]['geometry']['coordinates'] ?? null;
                if (!is_array($coords) || count($coords) < 2) {
                    return [];
                }

                // OSRM returns [lng, lat]
                return array_values(array_map(
                    fn($pair) => [(float) $pair[1], (float) $pair[0]],
                    $coords
                ));
            } catch (\Throwable $e) {
                return [];
            }
        });
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
        $vehicles = Vehicle::where('route_id', $routeId)
            ->where('live', true)
            ->get();

        $vehiclesCount = max(1, $vehicles->count());

        $polyline = $this->getRoadPolylineForRoute($routeId);
        $polyCount = count($polyline);

        return $vehicles
            ->values()
            ->map(function ($veh, int $idx) use ($polyline, $polyCount, $vehiclesCount) {
                $lat = $veh->lat;
                $lng = $veh->lng;

                if ($polyCount >= 2) {
                    $hash = abs((int) crc32($veh->vehicle_key));
                    $base = ($hash % 10000) / 10000; // 0..0.9999
                    $centeredIdx = $idx - (($vehiclesCount - 1) / 2);
                    $offset = ($centeredIdx / $vehiclesCount) * 0.10;
                    $t = 0.15 + ($base * 0.70) + $offset;
                    $t = $this->clamp($t, 0.08, 0.92);

                    $p = $this->pointAlongPolyline($polyline, $t);
                    if ($p) {
                        $lat = round((float) $p['lat'], 6);
                        $lng = round((float) $p['lng'], 6);
                    }
                }

                return [
                    'id' => $veh->vehicle_key,
                    'label' => $veh->label,
                    'lat' => $lat,
                    'lng' => $lng,
                    'live' => (bool) $veh->live,
                ];
            })
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
