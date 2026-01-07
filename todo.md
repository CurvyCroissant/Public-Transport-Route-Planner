# Project Status (Final)

This app is complete as a **static demo** that showcases the proposed SDG-11-aligned features using seeded database data (no external GTFS / GTFS-RT integration).

## Completed

- Foundations: confirmed feature set and SDG 11 motivation; created a Laravel + Vite codebase.
- Data layer: implemented tables for transit routes, stops, vehicles, arrivals, and notices; added a demo seeder.
- Route search: implemented corridor search using stop-name matching for `from`/`to` queries.
- Arrivals/ETA: implemented stop-level arrivals (minutes + live/scheduled flag).
- Live vehicles: implemented live vehicle positions endpoint (returns vehicles where `live=true`).
- Notices: implemented route notices endpoint and demo data.
- On-time insight: implemented route on-time rate using arrival history with a simple “≤ 15 minutes = on-time” rule.
- API layer: implemented REST-style JSON endpoints under `/api/routes/...`.
- Web UI: implemented a single-page route planner UI at `/` with route list, stops, arrivals, vehicles, notices, on-time insight, and a Leaflet map.
- Testing: added feature tests for the API endpoints.

## Out of Scope (intentionally not implemented)

- External transit feed ingestion (GTFS static/GTFS-RT), subscriptions, background workers/queues, scheduled refresh jobs.
- Authentication/roles for admin moderation.
- Payments/ticketing, push notifications, and native mobile app features.
- Production deployment automation and monitoring beyond local demo setup.
