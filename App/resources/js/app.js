import "./bootstrap";

import axios from "axios";

const plannerState = {
    routes: [],
    routesError: null,
    searchFrom: "",
    searchTo: "",
    lastAutoSelectKey: null,
    inFlightSearchKey: null,
    inFlightRouteId: null,
    lastSearchKey: null,
    lastSearchAt: 0,
    lastArrivalKey: null,
    lastArrivalAt: 0,
    lastLog: {
        searchKey: null,
        searchAt: 0,
        routeKey: null,
        routeAt: 0,
        arrivalKey: null,
        arrivalAt: 0,
    },
    focusStopCenter: null,
    selectedRoute: null,
    selectedRouteData: null,
    selectedStop: null,
    stops: [],
    arrivals: [],
    vehicles: [],
    notices: [],
    selectedVehicleId: null,
};

const defaultMapCenter = [-6.2, 106.8];
const defaultMapZoom = 12;
let mapInstance = null;
let stopLayer = null;
let vehicleLayer = null;
let routeLayer = null;
let lastBounds = null;
let routeCache = { key: null, coords: null };
let arrivalsPoll = null;
let vehiclesPoll = null;
let lastUserMoveAt = 0;

const stopIcon =
    typeof window !== "undefined" && window.L
        ? window.L.divIcon({
              className: "",
              html: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <circle cx="12" cy="12" r="10" fill="#1E40AF" opacity="0.12"/>
    <rect x="7" y="6" width="10" height="12" rx="2" fill="#1D4ED8" stroke="#0B3AAA" stroke-width="1.2"/>
    <rect x="9" y="8" width="6" height="4" rx="0.6" fill="white"/>
    <circle cx="10" cy="16" r="0.9" fill="white"/>
    <circle cx="14" cy="16" r="0.9" fill="white"/>
</svg>`,
              iconSize: [20, 20],
              iconAnchor: [10, 10],
          })
        : null;

const busIcon =
    typeof window !== "undefined" && window.L
        ? window.L.divIcon({
              className: "",
              html: `<svg width="26" height="26" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
  <rect x="4.5" y="3.5" width="15" height="15" rx="3.5" fill="#10B981" stroke="#065F46" stroke-width="1.3"/>
  <rect x="7" y="6" width="10" height="7" rx="1" fill="white"/>
  <circle cx="9" cy="16" r="1.05" fill="#065F46"/>
  <circle cx="15" cy="16" r="1.05" fill="#065F46"/>
  <rect x="9" y="18" width="6" height="1.8" rx="0.7" fill="#065F46"/>
</svg>`,
              iconSize: [26, 26],
              iconAnchor: [13, 13],
          })
        : null;

function invalidateMapSoon() {
    if (!mapInstance) return;
    requestAnimationFrame(() => mapInstance.invalidateSize());
}

function ensureMap() {
    const el = document.querySelector("[data-map]");
    if (!el || typeof window === "undefined" || !window.L) return null;
    if (!mapInstance) {
        mapInstance = window.L.map(el, {
            attributionControl: false,
            zoomControl: true,
            zoomAnimation: false,
            preferCanvas: true,
            markerZoomAnimation: false,
            fadeAnimation: false,
            zoomDelta: 0.15,
            zoomSnap: 0.15,
            wheelPxPerZoomLevel: 220,
            wheelDebounceTime: 180,
        }).setView(defaultMapCenter, defaultMapZoom);
        const tiles = window.L.tileLayer(
            "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
            {
                maxZoom: 19,
                attribution: "",
                updateWhenIdle: true,
                keepBuffer: 4,
                crossOrigin: true,
                updateWhenZooming: false,
                reuseTiles: true,
                tileSize: 256,
                updateInterval: 50,
            }
        );
        tiles.on("load", () => invalidateMapSoon());
        tiles.addTo(mapInstance);
        mapInstance.whenReady(() => invalidateMapSoon());
        stopLayer = window.L.layerGroup().addTo(mapInstance);
        vehicleLayer = window.L.layerGroup().addTo(mapInstance);
        routeLayer = window.L.layerGroup().addTo(mapInstance);

        mapInstance.on("movestart", () => {
            lastUserMoveAt = Date.now();
        });
        mapInstance.on("zoomstart", () => {
            lastUserMoveAt = Date.now();
        });
    }
    return mapInstance;
}

function applyMapView(map, bounds, primaryCenter, animate = false) {
    if (!map) return;
    if (bounds.length > 1) {
        map.fitBounds(bounds, {
            padding: [24, 24],
            animate,
            maxZoom: 16,
        });
        lastBounds = bounds;
        return;
    }
    if (primaryCenter) {
        map.setView(primaryCenter, 16, { animate });
        lastBounds = [primaryCenter];
        return;
    }
    if (lastBounds && lastBounds.length) {
        map.fitBounds(lastBounds, {
            padding: [24, 24],
            animate,
            maxZoom: 16,
        });
        return;
    }
    map.setView(defaultMapCenter, defaultMapZoom, { animate });
}

async function fetchRoadRoute(coords) {
    if (!coords || coords.length < 2) return [];
    const waypoints = coords.map(([lat, lng]) => `${lng},${lat}`).join(";");
    const url = `https://router.project-osrm.org/route/v1/driving/${waypoints}?overview=full&geometries=geojson`;
    const res = await fetch(url);
    if (!res.ok) throw new Error("route fetch failed");
    const data = await res.json();
    const first = data?.routes?.[0];
    if (!first?.geometry?.coordinates) throw new Error("route empty");
    return first.geometry.coordinates.map(([lng, lat]) => [lat, lng]);
}

function clearPolling() {
    if (arrivalsPoll) {
        clearInterval(arrivalsPoll);
        arrivalsPoll = null;
    }
    if (vehiclesPoll) {
        clearInterval(vehiclesPoll);
        vehiclesPoll = null;
    }
}

function startPolling(routeId, stopId) {
    clearPolling();

    arrivalsPoll = setInterval(async () => {
        try {
            const res = await axios
                .get(`/api/routes/${routeId}/stops/${stopId}/arrivals`)
                .catch(() => ({ data: { arrivals: [] } }));
            plannerState.arrivals = res.data.arrivals || [];
            renderArrivals();
            renderInsight();
        } catch (e) {
            /* ignore polling errors */
        }
    }, 15000);

    vehiclesPoll = setInterval(async () => {
        try {
            const res = await axios
                .get(`/api/routes/${routeId}/vehicles/live`)
                .catch(() => ({ data: { vehicles: [] } }));
            plannerState.vehicles = res.data.vehicles || [];
            renderVehicles();
            renderMap();
        } catch (e) {
            /* ignore polling errors */
        }
    }, 10000);
}

function setVisible(selector, show) {
    const el = document.querySelector(selector);
    if (!el) return;
    el.classList.toggle("hidden", !show);
}

function hasInsightData() {
    const rate = plannerState.selectedRouteData?.on_time_rate;
    return rate !== null && rate !== undefined;
}

async function fetchRoutes() {
    const from = document.querySelector("[data-from]")?.value || "";
    const to = document.querySelector("[data-to]")?.value || "";

    plannerState.searchFrom = from;
    plannerState.searchTo = to;
    const autoKey = `${from}||${to}`;
    const now = Date.now();

    if (
        plannerState.lastSearchKey === autoKey &&
        now - plannerState.lastSearchAt < 4000
    ) {
        return; // throttle identical searches within 4s
    }

    if (plannerState.inFlightSearchKey === autoKey) {
        return; // avoid duplicate identical in-flight search
    }
    plannerState.inFlightSearchKey = autoKey;
    plannerState.lastSearchKey = autoKey;
    plannerState.lastSearchAt = now;

    try {
        const res = await axios.get("/api/routes/search", {
            params: { from, to },
        });
        plannerState.routes = res.data.routes || [];
        plannerState.routesError = null;
        const logKey = `${from}||${to}||${plannerState.routes.length}`;
        const nowLog = Date.now();
        if (
            plannerState.lastLog.searchKey !== logKey ||
            nowLog - plannerState.lastLog.searchAt > 5000
        ) {
            console.info("[planner] routes search", {
                from,
                to,
                count: plannerState.routes.length,
                baseURL: axios.defaults.baseURL,
            });
            plannerState.lastLog.searchKey = logKey;
            plannerState.lastLog.searchAt = nowLog;
        }
    } catch (err) {
        console.error("[planner] routes search failed", err);
        plannerState.routes = [];
        plannerState.routesError = "Unable to load routes.";
    }

    // Reset selections so user explicitly chooses a route/stop after each search.
    plannerState.selectedRoute = null;
    plannerState.selectedRouteData = null;
    plannerState.selectedStop = null;
    plannerState.stops = [];
    plannerState.arrivals = [];
    plannerState.vehicles = [];
    plannerState.notices = [];

    renderRoutes();
    renderStops();
    renderArrivals();
    renderVehicles();
    renderNotices();
    renderInsight();
    renderMap();
    setVisible("[data-routes-wrap]", true);

    plannerState.inFlightSearchKey = null;
}

async function fetchRouteDetails(
    routeId,
    preferredStopId = null,
    autoSelectStop = false
) {
    if (plannerState.inFlightRouteId === routeId) {
        return; // already fetching this route
    }

    plannerState.inFlightRouteId = routeId;

    try {
        const [stopsRes, vehiclesRes, noticesRes] = await Promise.all([
            axios.get(`/api/routes/${routeId}/stops`),
            axios
                .get(`/api/routes/${routeId}/vehicles/live`)
                .catch(() => ({ data: { vehicles: [] } })),
            axios
                .get(`/api/routes/${routeId}/notices`)
                .catch(() => ({ data: { notices: [] } })),
        ]);

        plannerState.selectedRoute = routeId;
        plannerState.selectedRouteData =
            plannerState.routes.find((r) => r.id === routeId) || null;
        plannerState.stops = stopsRes.data.stops || [];
        plannerState.selectedStop = null;
        plannerState.selectedVehicleId = null;
        clearPolling();
        plannerState.arrivals = [];
        plannerState.vehicles = vehiclesRes.data.vehicles || [];
        plannerState.notices = noticesRes.data.notices || [];

        const routeLogKey = `${routeId}::${plannerState.stops.length}::${plannerState.vehicles.length}::${plannerState.notices.length}`;
        const nowRoute = Date.now();
        if (
            plannerState.lastLog.routeKey !== routeLogKey ||
            nowRoute - plannerState.lastLog.routeAt > 5000
        ) {
            console.info("[planner] route details", {
                routeId,
                stops: plannerState.stops.length,
                arrivals: plannerState.arrivals.length,
                vehicles: plannerState.vehicles.length,
                notices: plannerState.notices.length,
            });
            plannerState.lastLog.routeKey = routeLogKey;
            plannerState.lastLog.routeAt = nowRoute;
        }

        if (autoSelectStop) {
            let targetStopId = preferredStopId;
            const stopExists = (id) =>
                plannerState.stops.some((s) => s.id === id);

            if (!targetStopId && plannerState.stops.length) {
                targetStopId = plannerState.stops[0].id;
            }

            if (targetStopId && stopExists(targetStopId)) {
                await fetchArrivals(routeId, targetStopId);
                return;
            }
        }

        renderStops();
        renderArrivals();
        renderVehicles();
        renderNotices();
        renderInsight();
        renderMap();
    } finally {
        plannerState.inFlightRouteId = null;
    }
}

async function fetchArrivals(routeId, stopId, focusMap = false) {
    const now = Date.now();
    const arrivalKey = `${routeId}::${stopId}`;
    if (
        plannerState.lastArrivalKey === arrivalKey &&
        now - plannerState.lastArrivalAt < 4000
    ) {
        return; // throttle identical arrivals calls within 4s
    }

    const res = await axios
        .get(`/api/routes/${routeId}/stops/${stopId}/arrivals`)
        .catch(() => ({ data: { arrivals: [] } }));
    plannerState.arrivals = res.data.arrivals || [];
    plannerState.selectedStop = stopId;
    plannerState.selectedVehicleId = null;

    if (focusMap) {
        const stop = plannerState.stops.find((s) => s.id === stopId);
        const lat = Number(stop?.lat);
        const lng = Number(stop?.lng);
        if (Number.isFinite(lat) && Number.isFinite(lng)) {
            plannerState.focusStopCenter = [lat, lng];
        }
    }

    plannerState.lastArrivalKey = arrivalKey;
    plannerState.lastArrivalAt = now;

    const arrivalLogKey = `${routeId}::${stopId}::${plannerState.arrivals.length}`;
    const nowArr = Date.now();
    if (
        plannerState.lastLog.arrivalKey !== arrivalLogKey ||
        nowArr - plannerState.lastLog.arrivalAt > 5000
    ) {
        console.info("[planner] arrivals", {
            routeId,
            stopId,
            arrivals: plannerState.arrivals.length,
        });
        plannerState.lastLog.arrivalKey = arrivalLogKey;
        plannerState.lastLog.arrivalAt = nowArr;
    }
    renderStops();
    renderArrivals();
    renderVehicles();
    renderNotices();
    renderInsight();
    renderMap();

    startPolling(routeId, stopId);
    const hasStop = !!plannerState.selectedStop;
    setVisible("[data-arrivals-wrap]", true);
    setVisible("[data-vehicles-wrap]", true);
    setVisible("[data-map-wrap]", true);
}

function renderRoutes() {
    const list = document.querySelector("[data-routes]");
    if (!list) return;
    list.innerHTML = "";

    if (plannerState.routesError) {
        const err = document.createElement("p");
        err.className = "text-sm text-red-600";
        err.textContent = plannerState.routesError;
        list.appendChild(err);
    }

    plannerState.routes.forEach((route) => {
        const active = plannerState.selectedRoute === route.id;
        const fromLabel = route.from_stop?.name || route.from_stop?.id;
        const toLabel = route.to_stop?.name || route.to_stop?.id;
        const hasTermContext = fromLabel || toLabel;
        const btn = document.createElement("button");
        btn.className =
            "w-full text-left px-4 py-3 rounded-xl border transition flex items-center justify-between " +
            (active
                ? "border-emerald-400 bg-emerald-50 text-emerald-900 shadow-inner"
                : "border-slate-200 bg-white hover:border-emerald-300 text-slate-900");
        btn.innerHTML = `<div class="flex flex-col gap-1"><span class="font-semibold">${
            route.name
        }</span>${
            hasTermContext
                ? `<span class="text-xs text-slate-500">${
                      fromLabel ? `From ${fromLabel}` : ""
                  }${fromLabel && toLabel ? " → " : ""}${
                      toLabel ? `To ${toLabel}` : ""
                  }</span>`
                : ""
        }</div><span class="text-xs text-slate-500">On-time ${(
            route.on_time_rate * 100
        ).toFixed(0)}%</span>`;
        btn.addEventListener("click", () => {
            plannerState.selectedRoute = route.id;
            plannerState.selectedStop = null;
            renderRoutes();
            fetchRouteDetails(route.id);
        });
        list.appendChild(btn);
    });

    if (!plannerState.routes.length && !plannerState.routesError) {
        const empty = document.createElement("p");
        empty.className = "text-sm text-slate-500";
        const hasQuery = plannerState.searchFrom || plannerState.searchTo;
        empty.textContent = hasQuery
            ? `No demo routes match "${
                  plannerState.searchFrom || "(from)"
              }" → "${
                  plannerState.searchTo || "(to)"
              }". Try sample stops like Bundaran HI, Sarinah, Harmoni, Kota, Kalideres, Cengkareng, Grogol, Pasar Baru, Ragunan, Cijantung, or Dukuh Atas.`
            : "No routes yet. Search to load corridor options.";
        list.appendChild(empty);
    }
}

function renderStops() {
    const list = document.querySelector("[data-stops]");
    if (!list) return;
    list.innerHTML = "";

    const hasRoute = !!plannerState.selectedRoute;
    const hasStops = hasRoute && plannerState.stops.length > 0;
    setVisible("[data-stops-wrap]", hasStops);

    const fromId = plannerState.selectedRouteData?.from_stop?.id;
    const toId = plannerState.selectedRouteData?.to_stop?.id;

    if (!hasStops) {
        return;
    }

    plannerState.stops.forEach((stop) => {
        const li = document.createElement("li");
        const active = plannerState.selectedStop === stop.id;
        const isFromMatch = fromId && fromId === stop.id;
        const isToMatch = toId && toId === stop.id;
        li.className =
            "flex items-center justify-between rounded-xl border px-3 py-2 cursor-pointer transition " +
            (active
                ? "border-emerald-400 bg-emerald-50"
                : "border-slate-200 bg-white hover:border-emerald-300");
        li.innerHTML = `<div class="flex items-center gap-3"><span class="h-2.5 w-2.5 rounded-full ${
            active ? "bg-emerald-500" : "bg-slate-400"
        }"></span><div><p class="font-semibold text-slate-900">${
            stop.name
        }</p><p class="text-xs text-slate-500">Stop ID: ${stop.id}</p>${
            isFromMatch || isToMatch
                ? `<p class="text-[11px] font-semibold text-amber-600">${
                      isFromMatch ? "From match" : ""
                  }${isFromMatch && isToMatch ? " · " : ""}${
                      isToMatch ? "To match" : ""
                  }</p>`
                : ""
        }</div></div>`;
        li.addEventListener("click", () => {
            if (!plannerState.selectedRoute) return;
            fetchArrivals(plannerState.selectedRoute, stop.id, true);
        });
        list.appendChild(li);
    });

    if (!plannerState.stops.length) {
        const empty = document.createElement("p");
        empty.className = "text-sm text-slate-500";
        empty.textContent = "Select a route to see its stops.";
        list.appendChild(empty);
    }
}

function renderArrivals() {
    const list = document.querySelector("[data-arrivals]");
    if (!list) return;
    list.innerHTML = "";

    if (!plannerState.selectedRoute) {
        setVisible("[data-arrivals-wrap]", false);
        return;
    }

    setVisible("[data-arrivals-wrap]", true);

    const heading = document.createElement("p");
    heading.className = "text-xs font-semibold text-emerald-700";
    heading.textContent = "Arrivals";
    list.appendChild(heading);

    if (!plannerState.selectedStop) {
        const empty = document.createElement("p");
        empty.className = "text-sm text-slate-500";
        empty.textContent = "Select a stop to see arrivals.";
        list.appendChild(empty);
        return;
    }

    if (!plannerState.arrivals.length) {
        const empty = document.createElement("p");
        empty.className = "text-sm text-slate-500";
        empty.textContent = "No pending arrivals.";
        list.appendChild(empty);
        return;
    }

    plannerState.arrivals.forEach((arrival) => {
        const div = document.createElement("div");
        div.className =
            "flex items-center justify-between rounded-xl border border-slate-200 bg-white px-3 py-2";
        div.innerHTML = `<div class="flex items-center gap-3"><span class="h-2.5 w-2.5 rounded-full ${
            arrival.live ? "bg-emerald-500" : "bg-slate-300"
        }"></span><div><p class="font-semibold text-slate-900">Vehicle ${
            arrival.vehicle_id
        }</p><p class="text-xs text-slate-500">${
            arrival.live ? "Live GPS" : "Schedule"
        }</p></div></div><div class="text-right"><p class="text-sm font-semibold text-slate-900">${
            arrival.minutes
        } min</p></div>`;
        list.appendChild(div);
    });
}

function renderVehicles() {
    const list = document.querySelector("[data-vehicles]");
    if (!list) return;
    list.innerHTML = "";

    if (!plannerState.selectedRoute) {
        setVisible("[data-vehicles-wrap]", false);
        return;
    }

    setVisible("[data-vehicles-wrap]", true);

    const heading = document.createElement("p");
    heading.className = "text-xs font-semibold text-emerald-700";
    heading.textContent = "Live vehicles";
    list.appendChild(heading);

    if (!plannerState.vehicles.length) {
        const empty = document.createElement("p");
        empty.className = "text-sm text-slate-500";
        empty.textContent = "No live vehicle data available.";
        list.appendChild(empty);
        return;
    }

    plannerState.vehicles.forEach((veh) => {
        const div = document.createElement("div");
        div.className =
            "p-3 rounded-xl bg-white border border-slate-200 text-sm text-slate-800";
        const lat = Number(veh.lat);
        const lng = Number(veh.lng);
        const hasCoords = Number.isFinite(lat) && Number.isFinite(lng);
        const coordsText = hasCoords
            ? `Lat ${lat.toFixed(4)}, Lng ${lng.toFixed(4)}`
            : "Location unavailable";
        const isSelected = plannerState.selectedVehicleId === veh.id;
        div.innerHTML = `<p class="font-semibold">${veh.label}</p><p class="text-slate-600 text-xs">${coordsText}</p>`;
        if (hasCoords) {
            div.classList.add("cursor-pointer", "hover:border-emerald-300");
            if (isSelected)
                div.classList.add("border-emerald-400", "bg-emerald-50");
            div.addEventListener("click", () => {
                plannerState.selectedVehicleId = veh.id;
                const map = ensureMap();
                if (map) {
                    map.setView([lat, lng], 17, { animate: true });
                    invalidateMapSoon();
                }
                renderVehicles();
            });
        }
        list.appendChild(div);
    });
}

function renderNotices() {
    const list = document.querySelector("[data-notices]");
    if (!list) return;
    list.innerHTML = "";

    if (!plannerState.selectedRoute) {
        setVisible("[data-vehicles-wrap]", false);
        return;
    }

    setVisible("[data-vehicles-wrap]", true);

    const heading = document.createElement("p");
    heading.className = "text-xs font-semibold text-emerald-700";
    heading.textContent = "Notices";
    list.appendChild(heading);

    if (!plannerState.notices.length) {
        const empty = document.createElement("p");
        empty.className = "text-sm text-slate-500";
        empty.textContent = "No reported notices.";
        list.appendChild(empty);
        return;
    }

    plannerState.notices.forEach((notice) => {
        const div = document.createElement("div");
        div.className =
            "p-3 rounded-xl bg-white border border-slate-200 text-sm text-slate-900";
        div.innerHTML = `<p class="font-semibold text-emerald-700">${notice.title}</p><p class="text-xs text-slate-600">${notice.description}</p>`;
        list.appendChild(div);
    });
}

function renderMap() {
    const container = document.querySelector("[data-map]");
    if (!container) return;

    const hasRoute = !!plannerState.selectedRoute;
    setVisible("[data-map-wrap]", hasRoute);

    // If the map was previously hidden, force Leaflet to recalc size after showing
    if (hasRoute) {
        invalidateMapSoon();
    }

    const map = ensureMap();
    if (!map || !hasRoute) return;

    stopLayer.clearLayers();
    vehicleLayer.clearLayers();
    routeLayer.clearLayers();
    const bounds = [];
    let primaryCenter = null;
    const stopCoords = [];

    const fromId = plannerState.selectedRouteData?.from_stop?.id;
    const toId = plannerState.selectedRouteData?.to_stop?.id;

    plannerState.stops.forEach((stop) => {
        const lat = Number(stop.lat);
        const lng = Number(stop.lng);
        if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;
        const marker = window.L.marker([lat, lng], {
            icon: stopIcon || undefined,
            title: stop.name,
        }).bindPopup(`<strong>${stop.name}</strong><br/>Stop ID: ${stop.id}`);
        marker.on("click", () => {
            plannerState.selectedStop = stop.id;
            plannerState.focusStopCenter = [lat, lng];
            const zoomAndOpen = () => {
                map.setView([lat, lng], 17, { animate: true });
                invalidateMapSoon();
                marker.openPopup();
            };
            if (plannerState.selectedRoute) {
                fetchArrivals(plannerState.selectedRoute, stop.id, true).then(
                    () => {
                        zoomAndOpen();
                    }
                );
            } else {
                zoomAndOpen();
            }
        });
        marker.addTo(stopLayer);

        if (fromId === stop.id || toId === stop.id) {
            window.L.circleMarker([lat, lng], {
                radius: 9,
                color: fromId === stop.id ? "#f59e0b" : "#0ea5e9",
                weight: 2.5,
                fillColor: "white",
                fillOpacity: 0.7,
            }).addTo(stopLayer);
        }

        bounds.push([lat, lng]);
        if (!primaryCenter) primaryCenter = [lat, lng];
        stopCoords.push([lat, lng]);
    });

    plannerState.vehicles.forEach((veh) => {
        const lat = Number(veh.lat);
        const lng = Number(veh.lng);
        if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;
        const marker = window.L.marker([lat, lng], {
            title: veh.label,
            icon: busIcon || undefined,
        }).bindPopup(`<strong>${veh.label}</strong>`);
        marker.on("click", () => {
            plannerState.selectedVehicleId = veh.id;
            map.setView([lat, lng], 17, { animate: true });
            invalidateMapSoon();
            renderVehicles();
        });
        marker.addTo(vehicleLayer);
        bounds.push([lat, lng]);
        if (!primaryCenter) primaryCenter = [lat, lng];
    });

    let skipApplyView = false;
    const hasSelectedStop = !!plannerState.selectedStop;
    if (plannerState.focusStopCenter && hasSelectedStop) {
        const [lat, lng] = plannerState.focusStopCenter;
        map.setView([lat, lng], 17, { animate: true });
        lastBounds = [[lat, lng]];
        plannerState.focusStopCenter = null;
        invalidateMapSoon();
        skipApplyView = true;
    }

    const applyView = (animate = false) =>
        applyMapView(map, bounds, primaryCenter, animate);

    const drawRoute = (coords) => {
        if (!coords || !coords.length) return;
        const polyline = window.L.polyline(coords, {
            color: "#2563eb",
            weight: 4,
            opacity: 0.9,
        });
        polyline.addTo(routeLayer);
        coords.forEach((c) => bounds.push(c));
    };

    const shouldDrawRoute = stopCoords.length > 1;
    if (shouldDrawRoute) {
        const key = plannerState.selectedRoute
            ? `route::${plannerState.selectedRoute}`
            : null;
        if (routeCache.key === key && routeCache.coords) {
            drawRoute(routeCache.coords);
        } else {
            fetchRoadRoute(stopCoords)
                .then((coords) => {
                    routeCache = { key, coords };
                    drawRoute(coords);
                })
                .catch(() => {
                    routeCache = { key: null, coords: null };
                    drawRoute(stopCoords);
                });
        }
    }

    const userRecentlyMoved = Date.now() - lastUserMoveAt < 1500;

    if (!skipApplyView && !userRecentlyMoved) {
        applyView(false); // Initial view application after route drawing
        // Re-apply after render tick to avoid cropped tiles on first show
        requestAnimationFrame(() => {
            applyView();
            invalidateMapSoon();
        });
    }

    invalidateMapSoon();
}

function renderInsight() {
    const container = document.querySelector("[data-insight]");
    if (!container) return;
    container.innerHTML = "";

    if (!plannerState.selectedRoute) {
        setVisible("[data-arrivals-wrap]", false);
        return;
    }

    setVisible("[data-arrivals-wrap]", true);

    const hasInsight = hasInsightData();

    const heading = document.createElement("p");
    heading.className = "text-xs font-semibold text-emerald-700";
    heading.textContent = "On-time insight";
    container.appendChild(heading);

    if (!hasInsight) {
        const empty = document.createElement("p");
        empty.className = "text-sm text-slate-500";
        empty.textContent = "No on-time insight available.";
        container.appendChild(empty);
        return;
    }

    const rate = plannerState.selectedRouteData.on_time_rate;
    const percent = rate != null ? Math.round(rate * 100) : null;

    const status =
        percent == null
            ? "No data"
            : percent >= 90
            ? "Reliable"
            : percent >= 75
            ? "Moderate"
            : "Needs attention";

    const div = document.createElement("div");
    div.className =
        "flex items-center justify-between rounded-xl border border-slate-200 bg-white px-3 py-2";
    div.innerHTML = `<div><p class="text-sm text-slate-600">On-time rate</p><p class="text-lg font-semibold text-slate-900">${
        percent == null ? "–" : `${percent}%`
    }</p></div><div class="text-right"><p class="text-xs text-slate-500">${status}</p></div>`;
    container.appendChild(div);
}

document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector("[data-planner-form]");
    form?.addEventListener("submit", (e) => {
        e.preventDefault();
        fetchRoutes();
    });

    const resetBtn = document.querySelector("[data-map-reset]");
    resetBtn?.addEventListener("click", (e) => {
        e.preventDefault();
        renderMap();
    });

    ensureMap();
    fetchRoutes();
    setVisible("[data-stops-wrap]", false);
    setVisible("[data-arrivals-wrap]", false);
    setVisible("[data-vehicles-wrap]", false);
    setVisible("[data-map-wrap]", false);
});
