<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reset Password â€” {{ config('app.name', 'VentureX ERP & CRM') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-ink-100">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="grid w-full max-w-5xl overflow-hidden rounded-2xl border border-ink-200 bg-white shadow-xl lg:grid-cols-2">
            <div class="hidden flex-col justify-between bg-navy-900 p-10 text-white lg:flex">
                <div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-accent-600 text-xl font-bold">T</div>
                    <h1 class="mt-8 text-2xl font-bold leading-tight">{{ config('app.name') }}</h1>
                    <p class="mt-2 text-sm leading-relaxed text-slate-400">Universal CRM + ERP + AI Business Operating System. One platform for sales, procurement, inventory, finance, logistics and intelligence.</p>
                </div>
                <div class="space-y-3 text-sm text-slate-300">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-white/10"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></span>
                        Multi-company, multi-branch enterprise architecture
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-white/10"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></span>
                        AI-powered procurement intelligence
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-white/10"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></span>
                        Role-based security with full audit trail
                    </div>
                </div>
            </div>

            <div class="p-8 sm:p-10">
                <div class="mb-8 lg:hidden">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-accent-600 font-bold text-white">T</div>
                    <h1 class="mt-4 text-xl font-bold text-ink-900">{{ config('app.name') }}</h1>
                </div>

                <h2 class="text-lg font-bold text-ink-900">Reset password</h2>
                <p class="mt-1 text-sm text-ink-500">Enter your new password below.</p>

                @if (session('status'))
                    <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}" class="mt-8 space-y-5">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div>
                        <label for="email" class="label">Email address</label>
                        <input id="email" type="email" name="email" value="{{ $email ?? old('email') }}" required autofocus autocomplete="email" class="input">
                        @error('email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="label">New password</label>
                        <input id="password" type="password" name="password" required autocomplete="new-password" placeholder="â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢" class="input">
                        <p class="mt-1 text-xs text-ink-500">Min 10 chars, uppercase, lowercase, number, and special character.</p>
                        @error('password')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="label">Confirm password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢" class="input">
                    </div>

                    @if ($errors->any())
                        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <button type="submit" class="btn-primary w-full justify-center py-2.5">
                        Reset Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
