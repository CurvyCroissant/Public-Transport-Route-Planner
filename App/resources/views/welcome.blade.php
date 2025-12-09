<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Public Transport Route Planner</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-white text-slate-900 antialiased" style="font-family: 'DM Sans', sans-serif;">
    <div class="relative overflow-hidden min-h-screen">

        <header class="mx-auto w-full max-w-5xl px-4 py-8">
            <p class="text-emerald-700 text-sm font-semibold uppercase tracking-[0.16em]">Public Transport Route Planner
            </p>
        </header>

        <main class="mx-auto w-full max-w-5xl px-4 pb-16">
            <div class="grid gap-8 lg:grid-cols-[0.8fr_1.2fr]">
                <div class="space-y-6">
                    <div class="border border-slate-200 rounded-2xl bg-white p-4 space-y-4">
                        <p class="text-xs font-semibold text-emerald-700">Trip inputs</p>
                        <form class="grid gap-4" data-planner-form>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <label class="text-sm text-slate-700">From</label>
                                    <div
                                        class="mt-2 flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 focus-within:border-emerald-400 transition">
                                        <span class="text-emerald-500">●</span>
                                        <input type="text" data-from placeholder="e.g., Bundaran HI"
                                            class="w-full bg-transparent outline-none text-slate-900 placeholder:text-slate-400" />
                                    </div>
                                </div>
                                <div>
                                    <label class="text-sm text-slate-700">To</label>
                                    <div
                                        class="mt-2 flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 focus-within:border-emerald-400 transition">
                                        <span class="text-sky-500">◆</span>
                                        <input type="text" data-to placeholder="e.g., Kota Tua"
                                            class="w-full bg-transparent outline-none text-slate-900 placeholder:text-slate-400" />
                                    </div>
                                </div>
                            </div>
                            <div class="flex gap-3 items-center">
                                <button type="submit"
                                    class="rounded-xl px-5 py-2 bg-emerald-500 text-white font-semibold hover:bg-emerald-400 transition shadow-md shadow-emerald-200/60">Search</button>
                                <span class="text-xs text-slate-500">Static demo data.</span>
                            </div>
                        </form>
                    </div>

                    <div class="border border-slate-200 rounded-2xl bg-white p-4 space-y-2" data-routes-wrap>
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-semibold text-emerald-700">Route options</p>
                            <p class="text-xs text-slate-500">Pick a corridor to load stops.</p>
                        </div>
                        <div class="space-y-2" data-routes></div>
                    </div>

                    <div class="border border-slate-200 rounded-2xl bg-white p-4 space-y-2" data-stops-wrap>
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-semibold text-emerald-700">Stops</p>
                            <p class="text-xs text-slate-500">Tap a stop to see arrivals.</p>
                        </div>
                        <ul class="space-y-2" data-stops></ul>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="border border-slate-200 rounded-2xl bg-white p-4 space-y-4" data-arrivals-wrap>
                        <div class="grid gap-6 sm:grid-cols-2">
                            <div class="space-y-2" data-arrivals></div>
                            <div class="space-y-2" data-insight></div>
                        </div>
                    </div>

                    <div class="border border-slate-200 rounded-2xl bg-white p-4 space-y-4" data-vehicles-wrap>
                        <div class="grid gap-6 sm:grid-cols-2">
                            <div class="space-y-2" data-vehicles></div>
                            <div class="space-y-2" data-notices></div>
                        </div>
                    </div>

                    <div class="border border-slate-200 rounded-2xl bg-white p-4 space-y-2" data-map-wrap>
                        <p class="text-xs font-semibold text-emerald-700">Map</p>
                        <div class="text-sm text-slate-600 border border-emerald-100 rounded-xl bg-white p-3">
                            Map is currently not available.
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>

</html>
