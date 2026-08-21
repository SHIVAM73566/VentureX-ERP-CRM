<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Installation Complete — VentureX ERP & CRM</title>
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
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-600 text-[11px] font-bold text-white">âœ“</span>
                    Requirements
                </span>
                <span class="h-px w-6 bg-ink-200 dark:bg-ink-700"></span>
                <span class="flex items-center gap-1.5">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-600 text-[11px] font-bold text-white">âœ“</span>
                    Database
                </span>
                <span class="h-px w-6 bg-ink-200 dark:bg-ink-700"></span>
                <span class="flex items-center gap-1.5">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-600 text-[11px] font-bold text-white">âœ“</span>
                    Config
                </span>
                <span class="h-px w-6 bg-ink-200 dark:bg-ink-700"></span>
                <span class="flex items-center gap-1.5">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-600 text-[11px] font-bold text-white">âœ“</span>
                    Admin
                </span>
                <span class="h-px w-6 bg-ink-200 dark:bg-ink-700"></span>
                <span class="flex items-center gap-1.5">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-600 text-[11px] font-bold text-white">âœ“</span>
                    Install
                </span>
            </div>

            <div class="card overflow-hidden text-center">
                <div class="p-8">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-500/20">
                        <svg class="h-10 w-10 text-emerald-600 dark:text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>

                    <h2 class="mt-6 text-2xl font-bold text-ink-900 dark:text-ink-50">Installation Complete!</h2>
                    <p class="mt-2 text-sm text-ink-500 dark:text-ink-400 max-w-md mx-auto">
                        VentureX ERP & CRM has been installed successfully. You can now sign in with the admin account you created.
                    </p>

                    <div class="mt-8 space-y-3">
                        <a href="{{ route('login') }}" class="btn-accent inline-flex items-center gap-2 px-6 py-2.5">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                            Sign in to Dashboard
                        </a>
                    </div>

                    <div class="mt-8 rounded-lg border border-amber-200 bg-amber-50 p-4 text-left dark:border-amber-800 dark:bg-amber-500/10">
                        <div class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-600 dark:text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                            <div>
                                <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">Security Recommendations</p>
                                <ul class="mt-1 space-y-1 text-xs text-amber-700 dark:text-amber-400">
                                    <li>Change the demo credentials (demo_admin@example.com) before production use.</li>
                                    <li>Set APP_DEBUG=false in production.</li>
                                    <li>Configure your mail settings for password resets.</li>
                                    <li>Set up your AI provider keys in Settings â†’ AI Configuration.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 text-xs text-ink-400 dark:text-ink-500">
                        <p>Installed: {{ now()->format('M d, Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
