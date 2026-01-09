# Public-Transport-Route-Planner

Laravel + Vite demo for a public transport route planner using **seeded demo data** (corridors, stops, arrivals/ETAs, live vehicles, notices, on-time insight, map).

The Laravel app lives in `App/`.

## Prerequisites

- PHP 8.2+
- Composer
- Node 18+
- MySQL (default; you can adjust `.env` if needed)

## Setup

1. Go to the Laravel app folder

```pwsh
Set-Location App
```

1. Install dependencies

```pwsh
composer install
npm install
```

1. Environment

```pwsh
Copy-Item .env.example .env
php artisan key:generate
```

1. Database (MySQL defaults in `.env.example`)

```pwsh
php artisan migrate --seed
```

1. Storage symlink (for public files if needed)

```pwsh
php artisan storage:link
```

## Run

Recommended (single command; runs Laravel + Vite):

```pwsh
composer run dev
```

Alternative (two terminals):

```pwsh
php artisan serve
```

```pwsh
npm run dev
```

Build + serve (no Vite dev server):

```pwsh
npm run build
php artisan serve
```

Then open `http://127.0.0.1:8000/`.

## Implemented Features

- Web UI (`/`): search routes by “From/To”, select corridor, view stops, arrivals (next vehicles), on-time insight, notices, and a Leaflet map.
- JSON API:
  - `GET /api/routes/search?from=...&to=...`
  - `GET /api/routes/{routeId}`
  - `GET /api/routes/{routeId}/stops`
  - `GET /api/routes/{routeId}/stops/{stopId}/arrivals`
  - `GET /api/routes/{routeId}/vehicles/live`
  - `GET /api/routes/{routeId}/notices`
- Auth demo pages (optional): `/login`, `/register`, `/profile`

## Tests

```pwsh
php artisan test
```

## Notes

- Keep `.env` out of git; only commit `.env.example`.
- If you change DB creds or host/port, update `.env` accordingly.
- Map uses Leaflet + OpenStreetMap tiles via CDN; no API key required.
- This project is intentionally a **static demo** (no external GTFS / GTFS-RT integration).
