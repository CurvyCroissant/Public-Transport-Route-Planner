@extends('layouts.app')

@section('content')
    <div class="mx-auto w-full max-w-5xl px-4 pb-16">
        <div class="max-w-md mx-auto">
            <div class="border border-slate-200 rounded-2xl bg-white p-6 shadow-sm">
                <h1 class="text-2xl font-semibold mb-4">Sign in</h1>

                @if($errors->any())
                    <div class="mb-4 text-sm text-red-600">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ url('/login') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="text-sm text-slate-700">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required class="w-full mt-2 px-3 py-2 border rounded-md bg-transparent" />
                    </div>
                    <div>
                        <label class="text-sm text-slate-700">Password</label>
                        <input type="password" name="password" required class="w-full mt-2 px-3 py-2 border rounded-md bg-transparent" />
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="inline-flex items-center text-sm text-slate-700"><input type="checkbox" name="remember" class="mr-2"> Remember</label>
                        <button type="submit" class="ml-auto rounded-xl px-4 py-2 bg-emerald-500 text-white">Login</button>
                    </div>
                </form>

                <p class="text-sm mt-4">Don't have an account? <a href="{{ url('/register') }}" class="text-emerald-600">Register</a></p>
            </div>
        </div>
    </div>
@endsection
