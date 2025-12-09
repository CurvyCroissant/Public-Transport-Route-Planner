import "./bootstrap";

import axios from "axios";

const plannerState = {
    routes: [],
    routesError: null,
    selectedRoute: null,
    selectedRouteData: null,
    selectedStop: null,
    stops: [],
    arrivals: [],
    vehicles: [],
    notices: [],
};

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

    try {
        const res = await axios.get("/api/routes/search", {
            params: { from, to },
        });
        plannerState.routes = res.data.routes || [];
        plannerState.routesError = null;
        console.info("[planner] routes search", {
            from,
            to,
            count: plannerState.routes.length,
            baseURL: axios.defaults.baseURL,
        });
    } catch (err) {
        console.error("[planner] routes search failed", err);
        plannerState.routes = [];
        plannerState.routesError = "Unable to load routes.";
    }

    renderRoutes();
    setVisible("[data-routes-wrap]", true);
}

async function fetchRouteDetails(routeId) {
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
    plannerState.arrivals = [];
    plannerState.vehicles = vehiclesRes.data.vehicles || [];
    plannerState.notices = noticesRes.data.notices || [];

    console.info("[planner] route details", {
        routeId,
        stops: plannerState.stops.length,
        arrivals: plannerState.arrivals.length,
        vehicles: plannerState.vehicles.length,
        notices: plannerState.notices.length,
    });

    renderStops();
    renderArrivals();
    renderVehicles();
    renderNotices();
    renderInsight();
    setVisible("[data-arrivals-wrap]", false);
    setVisible("[data-vehicles-wrap]", false);
    setVisible("[data-map-wrap]", false);
}

async function fetchArrivals(routeId, stopId) {
    const res = await axios
        .get(`/api/routes/${routeId}/stops/${stopId}/arrivals`)
        .catch(() => ({ data: { arrivals: [] } }));
    plannerState.arrivals = res.data.arrivals || [];
    plannerState.selectedStop = stopId;

    console.info("[planner] arrivals", {
        routeId,
        stopId,
        arrivals: plannerState.arrivals.length,
    });
    renderStops();
    renderArrivals();
    renderVehicles();
    renderNotices();
    renderInsight();
    const hasStop = !!plannerState.selectedStop;
    setVisible("[data-arrivals-wrap]", hasStop);
    setVisible("[data-vehicles-wrap]", hasStop);
    setVisible("[data-map-wrap]", hasStop);
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
        const btn = document.createElement("button");
        btn.className =
            "w-full text-left px-4 py-3 rounded-xl border transition flex items-center justify-between " +
            (active
                ? "border-emerald-400 bg-emerald-50 text-emerald-900 shadow-inner"
                : "border-slate-200 bg-white hover:border-emerald-300 text-slate-900");
        btn.innerHTML = `<span class="font-semibold">${
            route.name
        }</span><span class="text-xs text-slate-500">On-time ${(
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
        empty.textContent = "No routes yet. Search to load corridor options.";
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

    if (!hasStops) {
        return;
    }

    plannerState.stops.forEach((stop) => {
        const li = document.createElement("li");
        const active = plannerState.selectedStop === stop.id;
        li.className =
            "flex items-center justify-between rounded-xl border px-3 py-2 cursor-pointer transition " +
            (active
                ? "border-emerald-400 bg-emerald-50"
                : "border-slate-200 bg-white hover:border-emerald-300");
        li.innerHTML = `<div class="flex items-center gap-3"><span class="h-2.5 w-2.5 rounded-full ${
            active ? "bg-emerald-500" : "bg-slate-400"
        }"></span><div><p class="font-semibold text-slate-900">${
            stop.name
        }</p><p class="text-xs text-slate-500">Stop ID: ${
            stop.id
        }</p></div></div>`;
        li.addEventListener("click", () => {
            if (!plannerState.selectedRoute) return;
            fetchArrivals(plannerState.selectedRoute, stop.id);
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

    const insightAvailable = hasInsightData();

    if (!plannerState.selectedStop) {
        setVisible("[data-arrivals-wrap]", false);
        return;
    }

    setVisible("[data-arrivals-wrap]", true);

    if (!plannerState.arrivals.length) {
        const heading = document.createElement("p");
        heading.className = "text-xs font-semibold text-emerald-700";
        heading.textContent = "Arrivals";
        list.appendChild(heading);

        const empty = document.createElement("p");
        empty.className = "text-sm text-slate-500";
        empty.textContent = "No pending arrivals.";
        list.appendChild(empty);
        return;
    }

    setVisible("[data-arrivals-wrap]", true);

    const heading = document.createElement("p");
    heading.className = "text-xs font-semibold text-emerald-700";
    heading.textContent = "Arrivals";
    list.appendChild(heading);

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

    if (!plannerState.selectedStop) {
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
        div.innerHTML = `<p class="font-semibold">${veh.label}</p><p class="text-slate-600 text-xs">${coordsText}</p>`;
        list.appendChild(div);
    });
}

function renderNotices() {
    const list = document.querySelector("[data-notices]");
    if (!list) return;
    list.innerHTML = "";

    if (!plannerState.selectedStop) {
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

function renderInsight() {
    const container = document.querySelector("[data-insight]");
    if (!container) return;
    container.innerHTML = "";

    if (!plannerState.selectedStop) {
        setVisible("[data-arrivals-wrap]", false);
        return;
    }

    setVisible("[data-arrivals-wrap]", true);

    const hasInsight = hasInsightData();

    if (!hasInsight) {
        const heading = document.createElement("p");
        heading.className = "text-xs font-semibold text-emerald-700";
        heading.textContent = "On-time insight";
        container.appendChild(heading);

        const empty = document.createElement("p");
        empty.className = "text-sm text-slate-500";
        empty.textContent = "No on-time insight available.";
        container.appendChild(empty);
        return;
    }

    const rate = plannerState.selectedRouteData.on_time_rate;
    const percent = rate != null ? Math.round(rate * 100) : null;

    const heading = document.createElement("p");
    heading.className = "text-xs font-semibold text-emerald-700";
    heading.textContent = "On-time insight";
    container.appendChild(heading);

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

    fetchRoutes();
    setVisible("[data-stops-wrap]", false);
    setVisible("[data-arrivals-wrap]", false);
    setVisible("[data-vehicles-wrap]", false);
    setVisible("[data-map-wrap]", false);
});
