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
            <a href="{{ url('/') }}"
                class="text-emerald-700 text-sm font-semibold uppercase tracking-[0.16em] hover:underline {{ request()->routeIs('home') ? 'underline font-bold' : '' }}">Public
                Transport Route
                Planner</a>
            <div>
                @auth
                    @php
                        $fullName = (string) auth()->user()->name;
                        $trimmedName = trim($fullName);
                        $parts = preg_split('/\s+/', $trimmedName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                        $firstWord = $parts[0] ?? '';
                        $displayName = $firstWord;
                        if (mb_strlen($firstWord) > 10) {
                            $displayName = mb_substr($firstWord, 0, 10) . '-';
                        }
                    @endphp
                    <span class="text-sm text-slate-600">Hello, </span>
                    <a href="{{ route('profile.show') }}"
                        class="text-sm text-emerald-700 hover:underline {{ request()->routeIs('profile.*') ? 'underline font-semibold' : '' }}">{{ $displayName }}</a>
                @else
                    <a href="{{ url('/login') }}"
                        class="text-sm text-emerald-600 mr-3 hover:underline {{ request()->routeIs('login') ? 'text-emerald-700 underline font-semibold' : '' }}">Login</a>
                    <a href="{{ url('/register') }}"
                        class="text-sm text-emerald-600 hover:underline {{ request()->routeIs('register') ? 'text-emerald-700 underline font-semibold' : '' }}">Register</a>
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
