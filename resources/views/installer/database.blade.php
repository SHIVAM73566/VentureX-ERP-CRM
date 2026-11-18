@extends('installer.layout')

@section('title', 'Database')

@section('content')
    <div class="card overflow-hidden">
        <div class="px-6 py-5 border-b border-ink-100 dark:border-ink-800">
            <h2 class="text-lg font-bold text-ink-900 dark:text-ink-50">Database Connection</h2>
            <p class="mt-1 text-sm text-ink-500 dark:text-ink-400">Enter the MySQL/MariaDB credentials created for this application.</p>
        </div>

        <form method="POST" action="{{ route('installer.database.store') }}" class="p-6 space-y-5">
            @csrf

            @error('db_connection')
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-500/10 dark:text-red-400">{{ $message }}</div>
            @enderror

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="db_host" class="label">Database Host</label>
                    <input id="db_host" name="db_host" type="text" value="{{ old('db_host', $db['db_host'] ?? '') }}" class="input" autocomplete="off" required>
                </div>
                <div>
                    <label for="db_port" class="label">Database Port</label>
                    <input id="db_port" name="db_port" type="number" min="1" max="65535" value="{{ old('db_port', $db['db_port'] ?? '3306') }}" class="input" required>
                </div>
            </div>

            <div>
                <label for="db_database" class="label">Database Name</label>
                <input id="db_database" name="db_database" type="text" value="{{ old('db_database', $db['db_database'] ?? '') }}" class="input" placeholder="venturex_erp" required>
                <p class="mt-1 text-xs text-ink-400 dark:text-ink-500">Letters, numbers and underscores only. The installer creates the database when your user is allowed to.</p>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="db_username" class="label">Database Username</label>
                    <input id="db_username" name="db_username" type="text" value="{{ old('db_username', $db['db_username'] ?? '') }}" class="input" autocomplete="username" required>
                </div>
                <div>
                    <label for="db_password" class="label">Database Password</label>
                    <input id="db_password" name="db_password" type="password" class="input" autocomplete="new-password">
                    @error('db_password')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-xs text-amber-800 dark:border-amber-800 dark:bg-amber-500/10 dark:text-amber-300">
                <p class="font-semibold">Shared hosting / cPanel</p>
                <p class="mt-1">If your host does not allow CREATE DATABASE, open <strong>MySQL Databases</strong> in cPanel, create the database and user there, grant <strong>ALL PRIVILEGES</strong>, then enter those exact values above.</p>
            </div>

            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('installer.mode') }}" class="btn-secondary">&larr; Back</a>
                <button type="submit" class="btn-accent">Test &amp; Continue &rarr;</button>
            </div>
        </form>
    </div>
@endsection
