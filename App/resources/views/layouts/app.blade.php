<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Public Transport Route Planner') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-white text-slate-900 antialiased" style="font-family: 'DM Sans', sans-serif;">
    <div class="relative overflow-hidden min-h-screen">

        <header class="mx-auto w-full max-w-5xl px-4 py-8 flex items-center justify-between">
            <p class="text-emerald-700 text-sm font-semibold uppercase tracking-[0.16em]">Public Transport Route Planner</p>
            <div>
                @auth
                    <span class="text-sm text-slate-600">Hello, {{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ url('/logout') }}" class="inline-block ml-3">
                        @csrf
                        <button type="submit" class="text-sm px-3 py-1 rounded-lg border border-emerald-200 text-emerald-700 hover:bg-emerald-50">Logout</button>
                    </form>
                @else
                    <a href="{{ url('/login') }}" class="text-sm text-emerald-600 mr-3">Login</a>
                    <a href="{{ url('/register') }}" class="text-sm text-emerald-600">Register</a>
                @endauth
            </div>
        </header>

        <main>
            @yield('content')
        </main>

    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    @stack('scripts')
</body>

</html>
