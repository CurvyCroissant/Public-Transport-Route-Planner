<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Public Transport Route Planner</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=space-grotesk:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-950 text-slate-50 antialiased" style="font-family: 'Space Grotesk', sans-serif;">
    <div class="relative overflow-hidden min-h-screen">
        <div class="absolute inset-0 -z-10 bg-gradient-to-br from-emerald-500/15 via-slate-900 to-slate-950"></div>
        <div class="absolute inset-0 -z-20 blur-3xl opacity-30"
            style="background: radial-gradient(circle at 30% 20%, #22d3ee55, transparent 35%), radial-gradient(circle at 70% 40%, #10b98155, transparent 32%), radial-gradient(circle at 50% 80%, #a855f755, transparent 30%);">
        </div>

        <header class="mx-auto w-full max-w-6xl px-4 py-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div
                    class="h-11 w-11 rounded-2xl bg-emerald-400/20 border border-emerald-300/40 flex items-center justify-center text-emerald-100 font-semibold">
                    PT</div>
                <div>
                    <p class="text-sm uppercase tracking-[0.18em] text-slate-300">SDG 11</p>
                    <p class="text-lg font-semibold text-white">Public Transport Route Planner</p>
                </div>
            </div>
            <div class="hidden sm:flex items-center gap-3 text-sm text-slate-300">
                <span class="px-3 py-1 rounded-full border border-slate-700/80 bg-slate-900/60">Real-time
                    tracking</span>
                <span class="px-3 py-1 rounded-full border border-slate-700/80 bg-slate-900/60">On-time insight</span>
                <span class="px-3 py-1 rounded-full border border-slate-700/80 bg-slate-900/60">Notices &
                    disruptions</span>
            </div>
        </header>

        <main class="mx-auto w-full max-w-6xl px-4 pb-14">
            <section class="grid gap-8 lg:grid-cols-[1.1fr_0.9fr] items-start">
                <div class="space-y-6">
                    <p class="text-emerald-300 text-sm font-semibold uppercase tracking-[0.2em]">Live city mobility</p>
                    <h1 class="text-4xl sm:text-5xl font-bold text-white leading-tight">Plan routes with real-time
                        vehicle positions, ETAs, and disruption notices.</h1>
                    <p class="text-lg text-slate-200/80 max-w-3xl">Built to cut waiting anxiety and keep commuters
                        moving. Combine schedule data with live feeds, see on-time reliability, and adapt quickly when
                        routes change.</p>

                    <div
                        class="bg-slate-900/70 backdrop-blur border border-slate-800 rounded-2xl p-6 shadow-2xl shadow-emerald-500/10">
                        <form class="grid gap-4 sm:grid-cols-2">
                            <div class="col-span-2 sm:col-span-1">
                                <label class="text-sm text-slate-300">From</label>
                                <div
                                    class="mt-2 flex items-center gap-2 rounded-xl border border-slate-800 bg-slate-900 px-3 py-2 focus-within:border-emerald-400/70 transition">
                                    <span class="text-emerald-300">●</span>
                                    <input type="text" placeholder="e.g., Bundaran HI"
                                        class="w-full bg-transparent outline-none text-white placeholder:text-slate-500" />
                                </div>
                            </div>
                            <div class="col-span-2 sm:col-span-1">
                                <label class="text-sm text-slate-300">To</label>
                                <div
                                    class="mt-2 flex items-center gap-2 rounded-xl border border-slate-800 bg-slate-900 px-3 py-2 focus-within:border-emerald-400/70 transition">
                                    <span class="text-sky-300">◆</span>
                                    <input type="text" placeholder="e.g., Kota Tua"
                                        class="w-full bg-transparent outline-none text-white placeholder:text-slate-500" />
                                </div>
                            </div>
                            <div class="col-span-2">
                                <label class="text-sm text-slate-300">Departure</label>
                                <div class="mt-2 grid gap-3 sm:grid-cols-[1fr_auto_auto] items-center">
                                    <input type="text" placeholder="Now"
                                        class="w-full rounded-xl border border-slate-800 bg-slate-900 px-3 py-2 text-white placeholder:text-slate-500 focus:border-emerald-400/70 outline-none" />
                                    <button type="button"
                                        class="rounded-xl px-4 py-2 border border-slate-800 bg-slate-900 text-slate-100 hover:border-emerald-400/70 transition">Live</button>
                                    <button type="submit"
                                        class="rounded-xl px-5 py-2 bg-emerald-400 text-slate-950 font-semibold hover:bg-emerald-300 transition shadow-lg shadow-emerald-500/25">Plan
                                        route</button>
                                </div>
                            </div>
                        </form>

                        <div class="mt-5 grid gap-4 sm:grid-cols-3 text-sm text-slate-200">
                            <div class="p-3 rounded-xl bg-slate-800/70 border border-slate-700/80">
                                <p class="text-xs uppercase tracking-wide text-slate-400">Next vehicles</p>
                                <p class="mt-1 text-lg font-semibold text-white">2 buses in 3 and 7 min</p>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-800/70 border border-slate-700/80">
                                <p class="text-xs uppercase tracking-wide text-slate-400">On-time rate</p>
                                <p class="mt-1 text-lg font-semibold text-white">92% last 30 days</p>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-800/70 border border-slate-700/80">
                                <p class="text-xs uppercase tracking-wide text-slate-400">Alerts</p>
                                <p class="mt-1 text-lg font-semibold text-white">1 disruption on Corridor 1</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-4">
                            <p class="text-sm text-emerald-300">Vehicle ETA</p>
                            <p class="text-sm text-slate-300 mt-2">Combine schedule plus live speeds for countdowns that
                                stay accurate.</p>
                        </div>
                        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-4">
                            <p class="text-sm text-sky-300">Live positions</p>
                            <p class="text-sm text-slate-300 mt-2">Track approaching vehicles on the map; fall back to
                                schedule-only when needed.</p>
                        </div>
                        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-4">
                            <p class="text-sm text-amber-300">Disruption notices</p>
                            <p class="text-sm text-slate-300 mt-2">Surface official and user-reported notices scoped to
                                routes and stops.</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-5">
                    <div class="rounded-3xl border border-slate-800 bg-slate-900/70 p-4 shadow-xl">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <p class="text-sm text-slate-300">Live route preview</p>
                                <p class="text-xl font-semibold text-white">Corridor 1: Blok M -> Kota</p>
                            </div>
                            <span
                                class="px-3 py-1 rounded-full bg-emerald-400/20 text-emerald-200 text-xs font-semibold">Realtime</span>
                        </div>
                        <div
                            class="aspect-[4/5] rounded-2xl border border-slate-800 bg-gradient-to-br from-slate-900 to-slate-950 relative overflow-hidden">
                            <div class="absolute inset-0" aria-hidden="true">
                                <div class="absolute inset-10 rounded-3xl border border-emerald-400/25 blur-3xl"></div>
                                <div class="absolute inset-16 rounded-2xl border border-slate-700/60 bg-slate-900/60">
                                </div>
                            </div>
                            <div class="relative p-4 flex flex-col gap-3 text-sm text-slate-100">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold">Stops</span>
                                    <span class="text-xs text-slate-400">Next 2 vehicles</span>
                                </div>
                                <div class="space-y-3">
                                    @foreach ([['name' => 'Bundaran HI', 'eta' => '3 min', 'live' => true], ['name' => 'Sarinah', 'eta' => '7 min', 'live' => true], ['name' => 'Harmoni', 'eta' => '12 min', 'live' => false], ['name' => 'Kota', 'eta' => '20 min', 'live' => false]] as $stop)
                                        <div
                                            class="flex items-center justify-between rounded-xl border border-slate-800/80 bg-slate-900/70 px-3 py-2">
                                            <div class="flex items-center gap-3">
                                                <span
                                                    class="h-2.5 w-2.5 rounded-full {{ $stop['live'] ? 'bg-emerald-400 animate-pulse' : 'bg-slate-600' }}"></span>
                                                <div>
                                                    <p class="font-semibold text-white">{{ $stop['name'] }}</p>
                                                    <p class="text-xs text-slate-400">Headway optimized</p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-sm font-semibold text-white">{{ $stop['eta'] }}</p>
                                                <p class="text-[11px] text-slate-400">
                                                    {{ $stop['live'] ? 'Live GPS' : 'Schedule' }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-slate-800 bg-slate-900/70 p-4">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-sm text-slate-300">On-time insight</p>
                            <span class="text-xs px-3 py-1 rounded-full bg-slate-800 text-slate-200">Rolling 30d</span>
                        </div>
                        <div class="grid grid-cols-3 gap-3 text-sm text-slate-200">
                            <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-3">
                                <p class="text-xs text-slate-400">Corridor 1</p>
                                <p class="text-lg font-semibold text-white">92%</p>
                                <p class="text-xs text-emerald-300 mt-1">+4% vs last week</p>
                            </div>
                            <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-3">
                                <p class="text-xs text-slate-400">Corridor 3</p>
                                <p class="text-lg font-semibold text-white">88%</p>
                                <p class="text-xs text-amber-300 mt-1">Minor delays</p>
                            </div>
                            <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-3">
                                <p class="text-xs text-slate-400">Corridor 6</p>
                                <p class="text-lg font-semibold text-white">75%</p>
                                <p class="text-xs text-rose-300 mt-1">Needs attention</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>

</html>
