<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Recovery codes — {{ config('app.name', 'MyERP') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-ink-100">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="w-full max-w-xl overflow-hidden rounded-2xl border border-ink-200 bg-white p-8 shadow-xl sm:p-10">
            <h1 class="text-lg font-bold text-ink-900">Save your recovery codes</h1>
            <p class="mt-2 text-sm text-ink-500">These codes are shown <strong>once</strong>. Store them somewhere safe. Each code can be used a single time to sign in if you lose your authenticator app.</p>

            <div class="mt-6 grid grid-cols-2 gap-3">
                @foreach ($codes as $code)
                    <div class="rounded-lg border border-ink-200 bg-ink-50 px-4 py-3 text-center font-mono text-sm font-bold tracking-widest text-navy-800 select-all">{{ $code }}</div>
                @endforeach
            </div>

            <a href="{{ route('dashboard') }}" class="btn-primary mt-6 w-full justify-center py-2.5">I saved my recovery codes — continue</a>
        </div>
    </div>
</body>
</html>
