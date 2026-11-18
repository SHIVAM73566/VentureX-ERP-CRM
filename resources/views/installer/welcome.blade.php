@extends('installer.layout')

@section('title', 'System Requirements')

@section('content')
    @php
        $failed = collect($requirements)->where('passed', false)->count();
    @endphp

    <div class="card overflow-hidden">
        <div class="px-6 py-5 border-b border-ink-100 dark:border-ink-800">
            <h2 class="text-lg font-bold text-ink-900 dark:text-ink-50">System Requirements</h2>
            <p class="mt-1 text-sm text-ink-500 dark:text-ink-400">Checking that your server meets every requirement.</p>
        </div>

        <div class="divide-y divide-ink-100 dark:divide-ink-800">
            @foreach ($requirements as $requirement)
                <div class="flex items-start gap-3 px-6 py-3">
                    @if ($requirement['passed'])
                        <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-500/20">
                            <svg class="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </span>
                    @else
                        <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-500/20">
                            <svg class="h-3.5 w-3.5 text-red-600 dark:text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </span>
                    @endif

                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-ink-800 dark:text-ink-200">{{ $requirement['label'] }}</p>
                        <p class="text-xs text-ink-400 dark:text-ink-500">{{ $requirement['detail'] }}</p>
                        @if (! $requirement['passed'] && ! empty($requirement['hint']))
                            <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">{{ $requirement['hint'] }}</p>
                        @endif
                    </div>

                    <span class="{{ $requirement['passed'] ? 'badge-green' : 'badge-red' }}">
                        {{ $requirement['passed'] ? 'Pass' : 'Fail' }}
                    </span>
                </div>
            @endforeach
        </div>

        <div class="px-6 py-4 border-t border-ink-100 dark:border-ink-800 bg-ink-50/50 dark:bg-ink-800/30">
            @if ($failed === 0)
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-2 text-sm text-emerald-700 dark:text-emerald-400">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        All requirements passed. You can continue.
                    </div>
                    <a href="{{ route('installer.mode') }}" class="btn-accent">Continue &rarr;</a>
                </div>
            @else
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-2 text-sm text-red-700 dark:text-red-400">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                        {{ $failed }} requirement(s) failed. Fix them before continuing.
                    </div>
                    <button type="button" onclick="location.reload()" class="btn-secondary">Re-check</button>
                </div>
            @endif
        </div>
    </div>

    <div class="mt-6 card p-5">
        <h3 class="text-sm font-bold text-ink-700 dark:text-ink-300">Prefer the command line?</h3>
        <p class="mt-1 text-xs text-ink-500 dark:text-ink-400">The wizard performs these steps for you. For a manual production install, run:</p>
        <pre class="mt-3 overflow-x-auto rounded-lg bg-ink-900 p-4 text-xs text-emerald-400"><code>cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan db:seed --class=PermissionSeeder --force
php artisan db:seed --class=RoleSeeder --force
touch storage/.installed</code></pre>
        <p class="mt-2 text-xs text-ink-400 dark:text-ink-500">Demo seeders and known demo credentials are only used when you explicitly choose Demo mode.</p>
    </div>
@endsection
