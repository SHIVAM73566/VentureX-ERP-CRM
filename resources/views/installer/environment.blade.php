@extends('installer.layout')

@section('title', 'Environment')

@section('content')
    <div class="card overflow-hidden">
        <div class="px-6 py-5 border-b border-ink-100 dark:border-ink-800">
            <h2 class="text-lg font-bold text-ink-900 dark:text-ink-50">Environment Settings</h2>
            <p class="mt-1 text-sm text-ink-500 dark:text-ink-400">Application, mail and optional AI provider configuration. Leave a key blank to keep its existing value.</p>
        </div>

        <form method="POST" action="{{ route('installer.environment.store') }}" class="p-6 space-y-5">
            @csrf

            @error('app_name')
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-500/10 dark:text-red-400">{{ $message }}</div>
            @enderror

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="app_name" class="label">Application Name</label>
                    <input id="app_name" name="app_name" type="text" value="{{ old('app_name', $envConfig['APP_NAME'] ?? '') }}" class="input" required>
                </div>
                <div>
                    <label for="app_url" class="label">Application URL</label>
                    <input id="app_url" name="app_url" type="url" value="{{ old('app_url', $envConfig['APP_URL'] ?? '') }}" class="input" required>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="app_debug" class="label">Debug Mode</label>
                    @php
                        $debugOld = old('app_debug');
                        $debugChecked = $debugOld === null
                            ? filter_var($envConfig['APP_DEBUG'] ?? 'false', FILTER_VALIDATE_BOOLEAN)
                            : filter_var($debugOld, FILTER_VALIDATE_BOOLEAN);
                    @endphp
                    <select id="app_debug" name="app_debug" class="input" required>
                        <option value="0" @selected(! $debugChecked)>Off (recommended)</option>
                        <option value="1" @selected($debugChecked)>On</option>
                    </select>
                </div>
                <div>
                    <label for="ai_provider" class="label">AI Provider</label>
                    <select id="ai_provider" name="ai_provider" class="input">
                        @foreach (['nvidia' => 'NVIDIA', 'gemini' => 'Gemini', 'deepseek' => 'DeepSeek', 'claude' => 'Claude / Anthropic', 'openai' => 'OpenAI', 'swift' => 'Swift'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('ai_provider', $envConfig['AI_PROVIDER'] ?? 'nvidia') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="rounded-xl border border-ink-200 bg-ink-50/60 p-5 dark:border-ink-700 dark:bg-ink-800/30">
                <h3 class="text-sm font-bold text-ink-800 dark:text-ink-100">Optional API keys</h3>
                <p class="mt-1 text-xs text-ink-500 dark:text-ink-400">Stored server-side only. Existing keys are never displayed here; "Configured" means a key is already present.</p>

                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    @foreach ([
                        'openai_api_key' => ['OpenAI', 'OPENAI_API_KEY'],
                        'gemini_api_key' => ['Gemini', 'GEMINI_API_KEY'],
                        'anthropic_api_key' => ['Anthropic', 'ANTHROPIC_API_KEY'],
                        'nvidia_api_key' => ['NVIDIA', 'NVIDIA_API_KEY'],
                        'rapidapi_key' => ['RapidAPI', 'RAPIDAPI_KEY'],
                    ] as $field => [$label, $key])
                        <div>
                            <label for="{{ $field }}" class="label">{{ $label }} @if ($configured[$key] ?? false)<span class="badge-green ml-1">Configured</span>@endif</label>
                            <input id="{{ $field }}" name="{{ $field }}" type="password" class="input" autocomplete="off" placeholder="Leave blank to keep existing value">
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-xl border border-ink-200 bg-ink-50/60 p-5 dark:border-ink-700 dark:bg-ink-800/30">
                <h3 class="text-sm font-bold text-ink-800 dark:text-ink-100">Mail</h3>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="mail_mailer" class="label">Mailer</label>
                        <input id="mail_mailer" name="mail_mailer" type="text" value="{{ old('mail_mailer', $envConfig['MAIL_MAILER'] ?? 'log') }}" class="input">
                    </div>
                    <div>
                        <label for="mail_host" class="label">Mail Host</label>
                        <input id="mail_host" name="mail_host" type="text" value="{{ old('mail_host', $envConfig['MAIL_HOST'] ?? '') }}" class="input">
                    </div>
                    <div>
                        <label for="mail_port" class="label">Mail Port</label>
                        <input id="mail_port" name="mail_port" type="number" min="1" max="65535" value="{{ old('mail_port', $envConfig['MAIL_PORT'] ?? '2525') }}" class="input">
                    </div>
                    <div>
                        <label for="mail_username" class="label">Mail Username</label>
                        <input id="mail_username" name="mail_username" type="text" value="{{ old('mail_username', $envConfig['MAIL_USERNAME'] ?? '') }}" class="input" autocomplete="off">
                    </div>
                    <div>
                        <label for="mail_password" class="label">Mail Password @if ($configured['MAIL_PASSWORD'] ?? false)<span class="badge-green ml-1">Configured</span>@endif</label>
                        <input id="mail_password" name="mail_password" type="password" class="input" autocomplete="new-password" placeholder="Leave blank to keep existing value">
                    </div>
                    <div>
                        <label for="mail_from_address" class="label">From Address</label>
                        <input id="mail_from_address" name="mail_from_address" type="email" value="{{ old('mail_from_address', $envConfig['MAIL_FROM_ADDRESS'] ?? '') }}" class="input">
                    </div>
                    <div class="md:col-span-2">
                        <label for="mail_from_name" class="label">From Name</label>
                        <input id="mail_from_name" name="mail_from_name" type="text" value="{{ old('mail_from_name', $envConfig['MAIL_FROM_NAME'] ?? '') }}" class="input">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('installer.database') }}" class="btn-secondary">&larr; Back</a>
                <button type="submit" class="btn-accent">Continue &rarr;</button>
            </div>
        </form>
    </div>
@endsection
