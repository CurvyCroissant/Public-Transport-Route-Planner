# Project Todo (from proposal)

- Foundations: review proposal features; confirm transit data sources (GTFS static + GTFS-RT or custom feed); define target city/routes; set env secrets in `.env` (API keys, map provider, geocoder).
- Data layer: add tables for routes, stops, trips, vehicles, service notices, on-time stats; write seeders/importer for scheduled data; set up cron/queue worker for periodic GTFS updates.
- Realtime ingestion: create job/service to poll or subscribe to vehicle positions and delays; normalize to internal model; cache latest positions/ETA inputs; handle fallback to schedule-only.
- ETA & on-time insight: implement ETA calculator combining schedule and realtime (speed, headway, delays); store trip history to compute on-time percentages; expose metrics endpoint.
- Notices: build CRUD and moderation for official/user-reported disruptions; associate notices to routes/stops; add expiry/active windows.
- API layer: design REST/JSON endpoints for route search, stop details (next 2 vehicles), ETA, live positions, notices, on-time metrics; add auth rate-limiting and validation.
- Web UI (Laravel + Vite/Tailwind): add map view with start/end selection and best route listing; stop markers show next 2 vehicles with ETA/live position; panels for notices and on-time percent; loading/error/“schedule-only” states.
- UX polish: display SDG11 context (about/help); accessibility pass (contrast, keyboard map nav); mobile-first layout and offline/slow-network hints.
- Background processing: configure queues (`redis` or `database`) for realtime jobs; schedule commands for data refresh and on-time computation; add health checks.
- Testing & quality: feature tests for routing/ETA responses, notices, permissions; unit tests for ETA calculator and on-time stats; browser smoke via Laravel Dusk or Playwright-lite; lint/format (Pint) and CI pipeline.
- Deployment: create env templates and queue/scheduler setup docs; choose hosting (Laravel Sail/Docker) and set up build; monitor/logging for ingestion errors.
