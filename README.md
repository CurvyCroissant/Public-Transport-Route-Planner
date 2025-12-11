# Public-Transport-Route-Planner

Laravel + Vite demo for a public transport route planner (corridors, stops, arrivals, live vehicles, notices, on-time insight, map placeholder).

## Prerequisites

- PHP 8.2+
- Composer
- Node 18+
- MySQL (or adjust `.env`)

## Setup

1. Install dependencies

```pwsh
composer install
npm install
```

1. Environment

```pwsh
Copy-Item App/.env.example App/.env
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

```pwsh
npm run dev   # or: npm run build && php artisan serve
```

## Notes

- Keep `.env` out of git; only commit `.env.example`.
- If you change DB creds or host/port, update `App/.env` accordingly.
- Map uses Leaflet + OpenStreetMap tiles via CDN; no API key required.
