<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh --seed');
    }

    public function test_routes_search_returns_on_time_rate(): void
    {
        $resp = $this->getJson('/api/routes/search');
        $resp->assertOk()
            ->assertJsonStructure([
                'routes' => [
                    ['id', 'name', 'on_time_rate', 'from_stop', 'to_stop', 'match_score'],
                ],
            ]);
    }

    public function test_routes_search_filters_by_from_and_to(): void
    {
        $resp = $this->getJson('/api/routes/search?from=Bundaran%20HI&to=Kota');
        $resp->assertOk()->assertJsonCount(1, 'routes');

        $route = $resp->json('routes.0');
        $this->assertEquals('corridor-1', $route['id']);
        $this->assertEquals('bundaran-hi', $route['from_stop']['id']);
        $this->assertEquals('kota', $route['to_stop']['id']);
    }

    public function test_routes_search_supports_route_name_match_when_only_one_term(): void
    {
        $resp = $this->getJson('/api/routes/search?from=Corridor%206');
        $resp->assertOk();

        $ids = array_column($resp->json('routes'), 'id');
        $this->assertContains('corridor-6', $ids);
    }

    public function test_routes_search_empty_when_terms_do_not_match(): void
    {
        $resp = $this->getJson('/api/routes/search?from=Alpha&to=Beta');
        $resp->assertOk()->assertJson(['routes' => []]);
    }

    public function test_route_stops_and_arrivals(): void
    {
        $routeId = 'corridor-1';
        $stopId = 'bundaran-hi';

        $this->getJson("/api/routes/{$routeId}/stops")
            ->assertOk()
            ->assertJsonStructure(['stops' => [['id', 'name', 'lat', 'lng']]]);

        $this->getJson("/api/routes/{$routeId}/stops/{$stopId}/arrivals")
            ->assertOk()
            ->assertJsonStructure(['arrivals']);
    }

    public function test_live_vehicles_and_notices(): void
    {
        $routeId = 'corridor-1';

        $this->getJson("/api/routes/{$routeId}/vehicles/live")
            ->assertOk()
            ->assertJsonStructure(['vehicles']);

        $this->getJson("/api/routes/{$routeId}/notices")
            ->assertOk()
            ->assertJsonStructure(['notices']);
    }
}