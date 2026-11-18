@extends('installer.layout')

@section('title', 'Install')

@section('content')
    <div class="card overflow-hidden">
        <div class="px-6 py-5 border-b border-ink-100 dark:border-ink-800">
            <h2 class="text-lg font-bold text-ink-900 dark:text-ink-50">Ready to Install</h2>
            <p class="mt-1 text-sm text-ink-500 dark:text-ink-400">Final step. Migrations and the selected seed chain will run now.</p>
        </div>

        <div class="p-6">
            @if (($mode ?? 'production') === 'demo')
                <div class="mb-5 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-800 dark:border-amber-800 dark:bg-amber-500/10 dark:text-amber-300">
                    Demo mode: the full sample-data chain and demo accounts will be created.
                </div>
            @else
                <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs text-emerald-800 dark:border-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-300">
                    Production mode: no demo seeders and no public demo accounts will be created.
                </div>
            @endif

            <div id="install-status" class="rounded-lg bg-ink-50 p-4 text-sm text-ink-600 dark:bg-ink-800/40 dark:text-ink-300">
                Press the button below to start. Do not close this page until installation finishes.
            </div>

            @error('install')
                <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</div>
            @enderror

            <div class="mt-6 flex items-center justify-between">
                <a href="{{ route('installer.company') }}" class="btn-secondary">&larr; Back</a>
                <button type="button" id="install-button" class="btn-accent">Install Now</button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const button = document.getElementById('install-button');
            const status = document.getElementById('install-status');
            if (!button || !status) return;

            button.addEventListener('click', async function () {
                button.disabled = true;
                status.textContent = 'Installing… This can take a minute.';

                try {
                    const response = await fetch(@json(route('installer.execute')), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({}),
                    });

                    const data = await response.json().catch(() => ({}));
                    if (!response.ok || !data.success) {
                        status.textContent = data.error || data.message || 'Installation failed. Check the application log and try again.';
                        button.disabled = false;

                        return;
                    }

                    window.location.assign(data.redirect);
                } catch (error) {
                    status.textContent = 'The request could not be completed. Check your connection and try again.';
                    button.disabled = false;
                }
            });
        });
    </script>
@endsection
