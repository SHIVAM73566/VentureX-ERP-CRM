@extends('installer.layout')

@section('title', 'Installation Complete')

@section('content')
    <div class="card overflow-hidden text-center">
        <div class="p-8">
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-500/20">
                <svg class="h-10 w-10 text-emerald-600 dark:text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>

            <h2 class="mt-6 text-2xl font-bold text-ink-900 dark:text-ink-50">Installation Complete!</h2>
            <p class="mx-auto mt-2 max-w-md text-sm text-ink-500 dark:text-ink-400">
                VentureX ERP &amp; CRM is installed. Sign in with the super admin account you created.
            </p>

            <div class="mt-8">
                <a href="{{ route('login') }}" class="btn-accent inline-flex items-center gap-2 px-6 py-2.5">Sign in to Dashboard &rarr;</a>
            </div>

            <div class="mt-8 rounded-lg border border-amber-200 bg-amber-50 p-4 text-left dark:border-amber-800 dark:bg-amber-500/10">
                <div class="flex items-start gap-3">
                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-600 dark:text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                    <div>
                        <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">Security Recommendations</p>
                        <ul class="mt-1 space-y-1 text-xs text-amber-700 dark:text-amber-400">
                            <li>Keep <code>APP_DEBUG=false</code> in production.</li>
                            <li>Enable MFA for the super admin account.</li>
                            <li>Configure mail settings so password resets work.</li>
                            <li>If you installed in Demo mode, wipe demo data from Administration &rarr; Import Data before using live data.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="mt-6 text-xs text-ink-400 dark:text-ink-500">
                <p>Installed: {{ now()->format('M d, Y H:i') }}</p>
            </div>
        </div>
    </div>
@endsection
