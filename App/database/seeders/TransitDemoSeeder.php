<?php

namespace Database\Seeders;

use App\Models\Arrival;
use App\Models\Notice;
use App\Models\Stop;
use App\Models\TransitRoute;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransitDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            Arrival::query()->delete();
            Notice::query()->delete();
            Vehicle::query()->delete();
            Stop::query()->delete();
            TransitRoute::query()->delete();

            $routes = [
                [
                    'id' => 'corridor-1',
                    'name' => 'Corridor 1: Blok M -> Kota',
                    'on_time_rate' => 0.92,
                    'stops' => [
                        ['stop_key' => 'bundaran-hi', 'name' => 'Bundaran HI', 'lat' => -6.1931, 'lng' => 106.8205],
                        ['stop_key' => 'sarinah', 'name' => 'Sarinah', 'lat' => -6.1870, 'lng' => 106.8237],
                        ['stop_key' => 'harmoni', 'name' => 'Harmoni', 'lat' => -6.1699, 'lng' => 106.8274],
                        ['stop_key' => 'kota', 'name' => 'Kota', 'lat' => -6.1352, 'lng' => 106.8133],
                    ],
                    'vehicles' => [
                        ['vehicle_key' => 'bus-101', 'label' => 'TJ 101', 'lat' => -6.1850, 'lng' => 106.8245, 'live' => true],
                        ['vehicle_key' => 'bus-102', 'label' => 'TJ 102', 'lat' => -6.1780, 'lng' => 106.8260, 'live' => true],
                    ],
                    'notices' => [
                        [
                            'type' => 'disruption',
                            'severity' => 'minor',
                            'title' => 'Slow traffic near Sarinah',
                            'description' => 'Expect +4 minutes due to congestion near Sarinah.',
                        ],
                    ],
                    'arrivals' => [
                        'bundaran-hi' => [
                            ['vehicle_key' => 'bus-101', 'minutes' => 3, 'live' => true],
                            ['vehicle_key' => 'bus-102', 'minutes' => 8, 'live' => true],
                        ],
                        'sarinah' => [
                            ['vehicle_key' => 'bus-101', 'minutes' => 7, 'live' => true],
                            ['vehicle_key' => 'bus-102', 'minutes' => 12, 'live' => true],
                        ],
                        'harmoni' => [
                            ['vehicle_key' => 'bus-101', 'minutes' => 12, 'live' => true],
                            ['vehicle_key' => 'bus-102', 'minutes' => 18, 'live' => true],
                        ],
                        'kota' => [
                            ['vehicle_key' => 'bus-101', 'minutes' => 20, 'live' => true],
                            ['vehicle_key' => 'bus-102', 'minutes' => 28, 'live' => true],
                        ],
                    ],
                ],
                [
                    'id' => 'corridor-3',
                    'name' => 'Corridor 3: Kalideres -> Pasar Baru',
                    'on_time_rate' => 0.88,
                    'stops' => [
                        ['stop_key' => 'kalideres', 'name' => 'Kalideres', 'lat' => -6.1518, 'lng' => 106.6950],
                        ['stop_key' => 'cengkareng', 'name' => 'Cengkareng', 'lat' => -6.1447, 'lng' => 106.7376],
                        ['stop_key' => 'grogol', 'name' => 'Grogol', 'lat' => -6.1595, 'lng' => 106.7907],
                        ['stop_key' => 'pasar-baru', 'name' => 'Pasar Baru', 'lat' => -6.1676, 'lng' => 106.8348],
                    ],
                    'vehicles' => [
                        ['vehicle_key' => 'bus-301', 'label' => 'TJ 301', 'lat' => -6.1500, 'lng' => 106.7050, 'live' => true],
                    ],
                    'notices' => [],
                    'arrivals' => [
                        'kalideres' => [['vehicle_key' => 'bus-301', 'minutes' => 4, 'live' => true]],
                        'cengkareng' => [['vehicle_key' => 'bus-301', 'minutes' => 10, 'live' => true]],
                        'grogol' => [['vehicle_key' => 'bus-301', 'minutes' => 18, 'live' => true]],
                        'pasar-baru' => [['vehicle_key' => 'bus-301', 'minutes' => 30, 'live' => true]],
                    ],
                ],
                [
                    'id' => 'corridor-6',
                    'name' => 'Corridor 6: Ragunan -> Dukuh Atas',
                    'on_time_rate' => 0.75,
                    'stops' => [
                        ['stop_key' => 'ragunan', 'name' => 'Ragunan', 'lat' => -6.3047, 'lng' => 106.8205],
                        ['stop_key' => 'cijantung', 'name' => 'Cijantung', 'lat' => -6.3145, 'lng' => 106.8500],
                        ['stop_key' => 'dukuh-atas', 'name' => 'Dukuh Atas', 'lat' => -6.1994, 'lng' => 106.8228],
                    ],
                    'vehicles' => [
                        ['vehicle_key' => 'bus-601', 'label' => 'TJ 601', 'lat' => -6.2500, 'lng' => 106.8300, 'live' => false],
                    ],
                    'notices' => [
                        [
                            'type' => 'advisory',
                            'severity' => 'info',
                            'title' => 'Short-turn at Cijantung after 9pm',
                            'description' => 'Evening trips short-turn at Cijantung due to maintenance.',
                        ],
                    ],
                    'arrivals' => [
                        'ragunan' => [['vehicle_key' => 'bus-601', 'minutes' => 6, 'live' => false]],
                        'cijantung' => [['vehicle_key' => 'bus-601', 'minutes' => 16, 'live' => false]],
                        'dukuh-atas' => [['vehicle_key' => 'bus-601', 'minutes' => 35, 'live' => false]],
                    ],
                ],
            ];

            foreach ($routes as $routeData) {
                $route = TransitRoute::create([
                    'id' => $routeData['id'],
                    'name' => $routeData['name'],
                    'on_time_rate' => $routeData['on_time_rate'],
                ]);

                foreach ($routeData['stops'] as $stop) {
                    Stop::create([
                        'route_id' => $route->id,
                        'stop_key' => $stop['stop_key'],
                        'name' => $stop['name'],
                        'lat' => $stop['lat'],
                        'lng' => $stop['lng'],
                    ]);
                }

                foreach ($routeData['vehicles'] as $vehicle) {
                    Vehicle::create([
                        'route_id' => $route->id,
                        'vehicle_key' => $vehicle['vehicle_key'],
                        'label' => $vehicle['label'],
                        'lat' => $vehicle['lat'],
                        'lng' => $vehicle['lng'],
                        'live' => $vehicle['live'],
                    ]);
                }

                foreach ($routeData['notices'] as $notice) {
                    Notice::create([
                        'route_id' => $route->id,
                        'type' => $notice['type'] ?? null,
                        'severity' => $notice['severity'] ?? null,
                        'title' => $notice['title'],
                        'description' => $notice['description'] ?? null,
                    ]);
                }

                foreach ($routeData['arrivals'] as $stopKey => $arrivals) {
                    foreach ($arrivals as $arrival) {
                        Arrival::create([
                            'route_id' => $route->id,
                            'stop_key' => $stopKey,
                            'vehicle_key' => $arrival['vehicle_key'],
                            'minutes' => $arrival['minutes'],
                            'live' => $arrival['live'],
                        ]);
                    }
                }
            }
        });
    }
}
