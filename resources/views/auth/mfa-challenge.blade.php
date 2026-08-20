<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verify â€” {{ config('app.name', 'VentureX ERP & CRM') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-ink-100">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="w-full max-w-md overflow-hidden rounded-2xl border border-ink-200 bg-white p-8 shadow-xl sm:p-10">
            <h1 class="text-lg font-bold text-ink-900">Two-factor verification</h1>
            <p class="mt-2 text-sm text-ink-500">Enter the 6-digit code from your authenticator app, or one of your recovery codes.</p>

            <form method="POST" action="{{ route('mfa.verify') }}" class="mt-6">
                @csrf
                <div>
                    <label for="code" class="label">Authentication code</label>
                    <input id="code" name="code" inputmode="numeric" autocomplete="one-time-code" class="input" required autofocus>
                </div>
                @if ($errors->any())
                    <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
                @endif
                <button type="submit" class="btn-primary mt-5 w-full justify-center py-2.5">Verify</button>
            </form>

            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="mt-4 block text-center text-xs text-ink-400 hover:text-ink-600">Sign out</a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
        </div>
    </div>
</body>
</html>
