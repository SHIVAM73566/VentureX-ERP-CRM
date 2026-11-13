<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recovered — {{ config('app.name', 'VentureX ERP & CRM') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="stylesheet" href="{{ asset('build/assets/fonts-C9MNnjVw.css') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="h-full bg-ink-100 flex items-center justify-center">
    <div class="text-center max-w-md mx-auto px-4">
        <div class="text-7xl font-bold text-ink-300 mb-4">{{ $status ?? 500 }}</div>
        <h1 class="text-2xl font-semibold text-ink-900 mb-2">We recovered this page</h1>
        <p class="text-ink-500 mb-2">{{ $message ?? 'Something went wrong. A safe fallback was used while the issue was logged.' }}</p>
        @if (! empty($reference))
            <p class="text-xs text-ink-400 mb-8">Reference: <span class="font-mono">{{ $reference }}</span></p>
        @else
            <div class="mb-6"></div>
        @endif
        <div class="flex items-center justify-center gap-3">
            <button onclick="location.reload()" class="btn-primary inline-flex items-center px-5 py-2.5">Try Again</button>
            <a href="{{ url('/') }}" class="btn-secondary inline-flex items-center px-5 py-2.5">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
