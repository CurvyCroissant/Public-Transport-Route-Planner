@extends('layouts.app')

@section('content')
    <div class="mx-auto w-full max-w-5xl px-4 pb-16">
        <div class="max-w-md mx-auto">
            <div class="border border-slate-200 rounded-2xl bg-white p-6 shadow-sm space-y-4">
                <div>
                    <h1 class="text-2xl font-semibold">User Profile</h1>
                </div>

                @if (session('status'))
                    <div class="text-sm text-emerald-700 border border-emerald-200 bg-emerald-50 rounded-xl px-3 py-2">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="text-sm text-red-600">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="text-sm text-slate-700">Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required maxlength="50"
                            class="w-full mt-2 px-3 py-2 border rounded-md bg-transparent" />
                    </div>

                    <div>
                        <label class="text-sm text-slate-700">Email</label>
                        <input type="email" value="{{ $user->email }}" disabled
                            class="w-full mt-2 px-3 py-2 border rounded-md bg-slate-50 text-slate-500" />
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="rounded-xl px-4 py-2 bg-emerald-500 text-white">
                            Save name
                        </button>
                    </div>
                </form>

                <form method="POST" action="{{ url('/logout') }}" class="pt-2">
                    @csrf
                    <button type="submit"
                        class="w-full text-sm px-4 py-2 rounded-xl border border-emerald-200 text-emerald-700 hover:bg-emerald-50 transition">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
