@extends('layouts.app')

@section('content')
    <main class="mx-auto w-full max-w-5xl px-4 pb-16">
        <div class="max-w-md mx-auto">
            <div class="border border-slate-200 rounded-2xl bg-white p-6 shadow-sm">
                <h1 class="text-2xl font-semibold mb-4">Create account</h1>

                @if($errors->any())
                    <div class="mb-4 text-sm text-red-600">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ url('/register') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="text-sm text-slate-700">Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="w-full mt-2 px-3 py-2 border rounded-md bg-transparent" />
                    </div>
                    <div>
                        <label class="text-sm text-slate-700">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required class="w-full mt-2 px-3 py-2 border rounded-md bg-transparent" />
                    </div>
                    <div>
                        <label class="text-sm text-slate-700">Password</label>
                        <input type="password" name="password" required class="w-full mt-2 px-3 py-2 border rounded-md bg-transparent" />
                    </div>
                    <div>
                        <label class="text-sm text-slate-700">Confirm password</label>
                        <input type="password" name="password_confirmation" required class="w-full mt-2 px-3 py-2 border rounded-md bg-transparent" />
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="rounded-xl px-4 py-2 bg-emerald-500 text-white">Register</button>
                    </div>
                </form>

                <p class="text-sm mt-4">Already have an account? <a href="{{ url('/login') }}" class="text-emerald-600">Sign in</a></p>
            </div>
        </div>
    </main>
@endsection
