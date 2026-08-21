<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign in —" {{ config('app.name', 'VentureX ERP & CRM') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-ink-100 dark:bg-ink-950">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="grid w-full max-w-5xl overflow-hidden rounded-2xl border border-ink-200 bg-white shadow-xl lg:grid-cols-2 dark:border-ink-800 dark:bg-ink-900">
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
                    <h1 class="mt-4 text-xl font-bold text-ink-900 dark:text-ink-50">{{ config('app.name') }}</h1>
                </div>

                <h2 class="text-lg font-bold text-ink-900 dark:text-ink-50">Welcome back</h2>
                <p class="mt-1 text-sm text-ink-500 dark:text-ink-400">Sign in to your workspace.</p>

                <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="label">Email address</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="you@company.com" class="input">
                    </div>

                    <div>
                        <label for="password" class="label">Password</label>
                        <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="—¢—¢—¢—¢—¢—¢—¢—¢" class="input">
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 text-sm text-ink-600 dark:text-ink-400">
                            <input type="checkbox" name="remember" class="h-4 w-4 rounded border-ink-300 text-accent-600 focus:ring-accent-500 dark:border-ink-600 dark:bg-ink-800">
                            Remember me
                        </label>
                        <a href="{{ route('password.request') }}" class="text-sm text-accent-600 hover:text-accent-700 dark:text-accent-400 dark:hover:text-accent-300">Forgot password?</a>
                    </div>

                    @if ($errors->any())
                        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-500/10 dark:text-red-400">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <button type="submit" class="btn-primary w-full justify-center py-2.5">
                        Sign in
                    </button>
                </form>

                <p class="mt-8 text-center text-xs text-ink-400 dark:text-ink-500">
                    Protected system. Unauthorized access is prohibited and monitored.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
