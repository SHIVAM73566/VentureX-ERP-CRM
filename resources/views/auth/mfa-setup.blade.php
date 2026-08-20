<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $enabled ? 'Security — ' : 'Set up MFA — ' }}{{ config('app.name', 'MyERP') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-ink-100">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="w-full max-w-xl overflow-hidden rounded-2xl border border-ink-200 bg-white p-8 shadow-xl sm:p-10">
            <h1 class="text-lg font-bold text-ink-900">Two-Factor Authentication</h1>

            @if ($enabled)
                <p class="mt-2 text-sm text-ink-500">Two-factor authentication is <span class="font-semibold text-emerald-600">enabled</span> on your account.</p>
                <form method="POST" action="{{ route('mfa.verify') }}" class="mt-6">
                    @csrf
                    <p class="text-sm text-ink-600">Enter a code from your authenticator app to continue, or use the verification prompt to complete sign-in.</p>
                    <div class="mt-4">
                        <label for="code" class="label">6-digit code</label>
                        <input id="code" name="code" inputmode="numeric" autocomplete="one-time-code" class="input" required autofocus>
                    </div>
                    @if ($errors->any())
                        <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
                    @endif
                    <button type="submit" class="btn-primary mt-5 w-full justify-center py-2.5">Verify</button>
                </form>
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="mt-4 block text-center text-xs text-ink-400 hover:text-ink-600">Sign out</a>
            @else
                <p class="mt-2 text-sm text-ink-500">
                    {{ $mandatory ? 'Your role requires two-factor authentication. ' : '' }}Add an extra layer of security to your account using your authenticator app.
                </p>

                <div class="mt-6 rounded-xl border border-ink-200 bg-ink-50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Step 1 — Add the account</p>
                    <p class="mt-2 text-sm text-ink-600">Open your authenticator app (Google Authenticator, Microsoft Authenticator, etc.) and add the account manually using this key:</p>
                    <div class="mt-3 rounded-lg border border-dashed border-navy-300 bg-white px-4 py-3 text-center font-mono text-sm font-bold tracking-widest text-navy-800 select-all">{{ $secret }}</div>
                    <p class="mt-2 text-xs text-ink-400">Or use the setup URI: <code class="break-all rounded bg-white px-1.5 py-0.5 text-[11px]">{{ $otp_uri }}</code></p>
                </div>

                <form method="POST" action="{{ route('mfa.enable') }}" class="mt-5">
                    @csrf
                    <div>
                        <label for="code" class="label">Step 2 — Enter the 6-digit code from your authenticator app</label>
                        <input id="code" name="code" inputmode="numeric" autocomplete="one-time-code" class="input" required autofocus>
                    </div>
                    @if ($errors->any())
                        <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
                    @endif
                    <button type="submit" class="btn-primary mt-5 w-full justify-center py-2.5">Enable two-factor authentication</button>
                </form>
            @endif
        </div>
    </div>
</body>
</html>
