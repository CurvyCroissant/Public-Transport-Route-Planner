<?php

namespace App\Http\Controllers;

use App\Data\TransitRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoutePlannerController extends Controller
{
    public function __construct(private TransitRepository $repo) {}

    public function search(Request $request): JsonResponse
    {
        $from = $request->query('from');
        $to = $request->query('to');
        $routes = $this->repo->searchRoutes($from, $to);

        return response()->json([
            'routes' => $routes,
        ]);
    }

    public function show(string $routeId): JsonResponse
    {
        $route = $this->repo->getRoute($routeId);

        if (!$route) {
            return response()->json(['message' => 'Route not found'], 404);
        }

        return response()->json($route);
    }

    public function stops(string $routeId): JsonResponse
    {
        $stops = $this->repo->getStops($routeId);

        if (empty($stops)) {
            return response()->json(['message' => 'Route or stops not found'], 404);
        }

        return response()->json(['stops' => $stops]);
    }

    public function arrivals(string $routeId, string $stopId): JsonResponse
    {
        $arrivals = $this->repo->getArrivals($routeId, $stopId);

        if (empty($arrivals)) {
            return response()->json(['message' => 'No arrivals found'], 404);
        }

        return response()->json(['arrivals' => $arrivals]);
    }

    public function livePositions(string $routeId): JsonResponse
    {
        $vehicles = $this->repo->getLiveVehicles($routeId);

        if (empty($vehicles)) {
            return response()->json(['message' => 'No live vehicles'], 404);
        }

        return response()->json(['vehicles' => $vehicles]);
    }

    public function notices(string $routeId): JsonResponse
    {
        $notices = $this->repo->getNotices($routeId);

        return response()->json(['notices' => $notices]);
    }
}
