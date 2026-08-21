<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Installing — VentureX ERP & CRM</title>
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
        <div class="w-full max-w-3xl">
            <div class="mb-8 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-navy-900 text-xl font-bold text-white shadow-lg">V</div>
                <h1 class="mt-5 text-2xl font-bold text-ink-900 dark:text-ink-50">VentureX ERP & CRM</h1>
                <p class="mt-1 text-sm text-ink-500 dark:text-ink-400">Installation Wizard</p>
            </div>

            <div class="mb-6 flex items-center justify-center gap-2 text-xs font-semibold text-ink-400 dark:text-ink-500">
                <span class="flex items-center gap-1.5">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-600 text-[11px] font-bold text-white">“</span>
                    Requirements
                </span>
                <span class="h-px w-6 bg-ink-200 dark:bg-ink-700"></span>
                <span class="flex items-center gap-1.5">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-600 text-[11px] font-bold text-white">“</span>
                    Database
                </span>
                <span class="h-px w-6 bg-ink-200 dark:bg-ink-700"></span>
                <span class="flex items-center gap-1.5">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-600 text-[11px] font-bold text-white">“</span>
                    Config
                </span>
                <span class="h-px w-6 bg-ink-200 dark:bg-ink-700"></span>
                <span class="flex items-center gap-1.5">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-600 text-[11px] font-bold text-white">“</span>
                    Admin
                </span>
                <span class="h-px w-6 bg-ink-200 dark:bg-ink-700"></span>
                <span class="flex items-center gap-1.5">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-navy-800 text-[11px] font-bold text-white">5</span>
                    Install
                </span>
            </div>

            <div class="card overflow-hidden">
                <div class="px-6 py-5 border-b border-ink-100 dark:border-ink-800">
                    <h2 class="text-lg font-bold text-ink-900 dark:text-ink-50">Installing VentureX ERP & CRM</h2>
                    <p class="mt-1 text-sm text-ink-500 dark:text-ink-400">Please do not close this page while installation is in progress.</p>
                </div>

                <div class="p-6">
                    <div id="steps" class="space-y-3">
                        <div class="flex items-center gap-3 py-2" id="step-init">
                            <div class="flex h-6 w-6 shrink-0 items-center justify-center">
                                <div class="h-4 w-4 animate-spin rounded-full border-2 border-navy-600 border-t-transparent"></div>
                            </div>
                            <span class="text-sm text-ink-700 dark:text-ink-300">Preparing installation...</span>
                        </div>
                    </div>

                    <div id="progress-bar" class="mt-6 hidden">
                        <div class="h-2 w-full overflow-hidden rounded-full bg-ink-100 dark:bg-ink-800">
                            <div id="progress-fill" class="h-full rounded-full bg-accent-600 transition-all duration-500" style="width: 0%"></div>
                        </div>
                        <p id="progress-text" class="mt-2 text-xs text-ink-400 dark:text-ink-500 text-center"></p>
                    </div>

                    <div id="error-box" class="mt-4 hidden rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-500/10 dark:text-red-400"></div>

                    <div id="success-box" class="mt-6 hidden text-center">
                        <a href="{{ route('installer.complete') }}" class="btn-accent">Continue to Dashboard •’</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const stepsContainer = document.getElementById('steps');
        const progressBar = document.getElementById('progress-bar');
        const progressFill = document.getElementById('progress-fill');
        const progressText = document.getElementById('progress-text');
        const errorBox = document.getElementById('error-box');
        const successBox = document.getElementById('success-box');

        const stepLabels = [
            'Running database migrations...',
            'Seeding demo data...',
            'Creating admin user...',
            'Setting directory permissions...',
            'Finalizing installation...'
        ];

        function renderStep(index, label, status) {
            let iconHtml = '';
            if (status === 'running') {
                iconHtml = '<div class="h-4 w-4 animate-spin rounded-full border-2 border-navy-600 border-t-transparent"></div>';
            } else if (status === 'done') {
                iconHtml = '<svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>';
            } else if (status === 'error') {
                iconHtml = '<svg class="h-5 w-5 text-red-600 dark:text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>';
            }

            const textClass = status === 'error'
                ? 'text-red-700 dark:text-red-400'
                : 'text-ink-700 dark:text-ink-300';

            let el = document.getElementById('step-' + index);
            if (!el) {
                el = document.createElement('div');
                el.id = 'step-' + index;
                el.className = 'flex items-center gap-3 py-2';
                stepsContainer.appendChild(el);
            }
            el.innerHTML = '<div class="flex h-6 w-6 shrink-0 items-center justify-center">' + iconHtml + '</div><span class="text-sm ' + textClass + '">' + label + '</span>';
        }

        async function runInstaller() {
            const token = document.querySelector('meta[name="csrf-token"]').content;

            progressBar.classList.remove('hidden');

            for (let i = 0; i < stepLabels.length; i++) {
                renderStep(i, stepLabels[i], 'running');
                const pct = Math.round(((i + 1) / (stepLabels.length + 1)) * 100);
                progressFill.style.width = pct + '%';
                progressText.textContent = pct + '%';
            }

            try {
                const response = await fetch('{{ route("installer.execute") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ step: 'all' }),
                });

                const data = await response.json();

                if (!response.ok || data.error) {
                    for (let i = 0; i < stepLabels.length; i++) {
                        renderStep(i, stepLabels[i] + (i === stepLabels.length - 1 ? ' — ' + (data.error || 'Failed') : ''), i === stepLabels.length - 1 ? 'error' : (i < stepLabels.length - 1 ? 'done' : 'running'));
                    }
                    errorBox.textContent = 'Installation failed: ' + (data.error || 'Unknown error');
                    errorBox.classList.remove('hidden');
                    return;
                }

                for (let i = 0; i < stepLabels.length; i++) {
                    renderStep(i, stepLabels[i], 'done');
                }

                progressFill.style.width = '100%';
                progressText.textContent = '100% — Complete!';
                successBox.classList.remove('hidden');

            } catch (err) {
                renderStep(stepLabels.length - 1, stepLabels[stepLabels.length - 1] + ' — ' + err.message, 'error');
                errorBox.textContent = 'Installation failed: ' + err.message;
                errorBox.classList.remove('hidden');
            }
        }

        runInstaller();
    });
    </script>
</body>
</html>
