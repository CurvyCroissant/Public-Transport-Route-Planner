@extends('layouts.app')

@section('content')
    <div class="mx-auto w-full max-w-5xl px-4 pb-16">
        <div class="max-w-md mx-auto">
            <div class="border border-slate-200 rounded-2xl bg-white p-6 shadow-sm">
                <h1 class="text-2xl font-semibold mb-4">Login</h1>

                @if ($errors->any())
                    <div class="mb-4 text-sm text-red-600">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ url('/login') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="text-sm text-slate-700">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                            class="w-full mt-2 px-3 py-2 border rounded-md bg-transparent" />
                    </div>
                    <div>
                        <label class="text-sm text-slate-700">Password</label>
                        <div class="mt-2 flex items-center gap-2">
                            <input type="password" name="password" value="{{ old('password') }}" required
                                class="w-full px-3 py-2 border rounded-md bg-transparent" />
                            <button type="button" data-toggle-password="password" aria-label="Show password"
                                class="shrink-0 px-3 py-2 rounded-md border border-slate-200 text-slate-600 hover:bg-slate-50">
                                <span data-eye-open aria-hidden="true">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M2.5 12C4.7 7.7 8.1 5 12 5C15.9 5 19.3 7.7 21.5 12C19.3 16.3 15.9 19 12 19C8.1 19 4.7 16.3 2.5 12Z"
                                            stroke="currentColor" stroke-width="1.6" />
                                        <circle cx="12" cy="12" r="3" stroke="currentColor"
                                            stroke-width="1.6" />
                                    </svg>
                                </span>
                                <span data-eye-closed aria-hidden="true" class="hidden">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M3 3L21 21" stroke="currentColor" stroke-width="1.6" />
                                        <path d="M2.5 12C4.7 7.7 8.1 5 12 5C13.5 5 14.9 5.4 16.2 6.1" stroke="currentColor"
                                            stroke-width="1.6" />
                                        <path d="M21.5 12C19.3 16.3 15.9 19 12 19C10.2 19 8.5 18.4 7 17.4"
                                            stroke="currentColor" stroke-width="1.6" />
                                        <path
                                            d="M10.2 10.2C9.5 10.9 9.3 12 9.7 12.9C10.1 13.8 11 14.4 12 14.4C12.4 14.4 12.8 14.3 13.1 14.1"
                                            stroke="currentColor" stroke-width="1.6" />
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="inline-flex items-center text-sm text-slate-700"><input type="checkbox"
                                name="remember" class="mr-2">Remember Me</label>
                        <button type="submit" class="ml-auto rounded-xl px-4 py-2 bg-emerald-500 text-white">Login</button>
                    </div>
                </form>

                <p class="text-sm mt-4">Don't have an account? <a href="{{ url('/register') }}"
                        class="text-emerald-600 hover:underline">Register</a></p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            const prefix = 'authForm:' + window.location.pathname + ':';
            const hasErrors = @json($errors->any());
            // Never persist passwords in browser storage.
            const fieldNames = ['email'];

            // Always wire password toggles (independent of validation errors).
            document.querySelectorAll('[data-toggle-password]').forEach((btn) => {
                const target = btn.getAttribute('data-toggle-password');
                const input = document.querySelector(`[name="${target}"]`);
                if (!input) return;

                const openIcon = btn.querySelector('[data-eye-open]');
                const closedIcon = btn.querySelector('[data-eye-closed]');

                function sync() {
                    const isText = input.type === 'text';
                    btn.setAttribute('aria-label', isText ? 'Hide password' : 'Show password');
                    if (openIcon) openIcon.classList.toggle('hidden', isText);
                    if (closedIcon) closedIcon.classList.toggle('hidden', !isText);
                }

                btn.addEventListener('click', () => {
                    input.type = input.type === 'password' ? 'text' : 'password';
                    sync();
                });

                sync();
            });

            // If this is a clean visit (not a validation-error redirect), clear any stored values.
            if (!hasErrors) {
                fieldNames.forEach((name) => sessionStorage.removeItem(prefix + name));
            } else {
                fieldNames.forEach((name) => {
                    const input = document.querySelector(`[name="${name}"]`);
                    if (!input) return;
                    const stored = sessionStorage.getItem(prefix + name);
                    if (!input.value && stored) input.value = stored;
                    input.addEventListener('input', () => sessionStorage.setItem(prefix + name, input.value));
                });
            }
        })();
    </script>
@endpush
