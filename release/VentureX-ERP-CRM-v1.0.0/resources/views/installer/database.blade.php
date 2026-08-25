@inject('errors', 'Illuminate\Support\ViewErrorBag')
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Database Setup — VentureX ERP & CRM</title>
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
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-600 text-[11px] font-bold text-white">✓</span>
                    Requirements
                </span>
                <span class="h-px w-6 bg-ink-200 dark:bg-ink-700"></span>
                <span class="flex items-center gap-1.5">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-navy-800 text-[11px] font-bold text-white">2</span>
                    Database
                </span>
                <span class="h-px w-6 bg-ink-200 dark:bg-ink-700"></span>
                <span>3 Config</span>
                <span class="h-px w-6 bg-ink-200 dark:bg-ink-700"></span>
                <span>4 Admin</span>
                <span class="h-px w-6 bg-ink-200 dark:bg-ink-700"></span>
                <span>5 Install</span>
            </div>

            <div class="card overflow-hidden">
                <div class="px-6 py-5 border-b border-ink-100 dark:border-ink-800">
                    <h2 class="text-lg font-bold text-ink-900 dark:text-ink-50">Database Configuration</h2>
                    <p class="mt-1 text-sm text-ink-500 dark:text-ink-400">Enter your MySQL/MariaDB connection details. The database will be created if it doesn't exist.</p>
                </div>

                <form method="POST" action="{{ route('installer.database.store') }}" class="p-6 space-y-5">
                    @csrf

                    @if ($errors->has('db_connection'))
                        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-500/10 dark:text-red-400">
                            {{ $errors->first('db_connection') }}
                        </div>
                    @endif

                    <div class="grid gap-5 sm:grid-cols-3">
                        <div>
                            <label for="db_host" class="label">Host</label>
                            <input id="db_host" name="db_host" type="text" value="{{ old('db_host', '127.0.0.1') }}" class="input" required>
                        </div>
                        <div>
                            <label for="db_port" class="label">Port</label>
                            <input id="db_port" name="db_port" type="number" value="{{ old('db_port', '3306') }}" class="input" min="1" max="65535" required>
                        </div>
                        <div>
                            <label for="db_database" class="label">Database</label>
                            <input id="db_database" name="db_database" type="text" value="{{ old('db_database', 'VENTUREX_ERP') }}" class="input" required>
                        </div>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="db_username" class="label">Username</label>
                            <input id="db_username" name="db_username" type="text" value="{{ old('db_username', 'root') }}" class="input" required>
                        </div>
                        <div>
                            <label for="db_password" class="label">Password</label>
                            <input id="db_password" name="db_password" type="password" value="{{ old('db_password') }}" class="input" placeholder="Leave empty if no password">
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <a href="{{ route('installer.welcome') }}" class="btn-secondary">← Back</a>
                        <button type="submit" class="btn-accent">Test Connection & Continue →</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
