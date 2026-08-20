<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Environment Setup â€” VentureX ERP & CRM</title>
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
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
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-navy-800 text-[11px] font-bold text-white">3</span>
                    Config
                </span>
                <span class="h-px w-6 bg-ink-200 dark:bg-ink-700"></span>
                <span>4 Admin</span>
                <span class="h-px w-6 bg-ink-200 dark:bg-ink-700"></span>
                <span>5 Install</span>
            </div>

            <form method="POST" action="<?php echo e(route('installer.environment.store')); ?>" class="space-y-5">
                <?php echo csrf_field(); ?>

                <div class="card overflow-hidden">
                    <div class="px-6 py-5 border-b border-ink-100 dark:border-ink-800">
                        <h2 class="text-lg font-bold text-ink-900 dark:text-ink-50">Application Settings</h2>
                        <p class="mt-1 text-sm text-ink-500 dark:text-ink-400">Configure the basic application settings. APP_KEY will be generated automatically.</p>
                    </div>
                    <div class="p-6 space-y-5">
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="app_name" class="label">Application Name</label>
                                <input id="app_name" name="app_name" type="text" value="<?php echo e(old('app_name', $envConfig['APP_NAME'] ?? 'VentureX ERP & CRM')); ?>" class="input" required>
                            </div>
                            <div>
                                <label for="app_url" class="label">Application URL</label>
                                <input id="app_url" name="app_url" type="url" value="<?php echo e(old('app_url', $envConfig['APP_URL'] ?? url('/'))); ?>" class="input" required>
                            </div>
                        </div>
                        <div>
                            <label for="app_debug" class="label">Debug Mode</label>
                            <select id="app_debug" name="app_debug" class="input">
                                <option value="0" <?php echo e(($envConfig['APP_DEBUG'] ?? 'false') === '0' || ($envConfig['APP_DEBUG'] ?? 'false') === 'false' ? 'selected' : ''); ?>>Disabled (Recommended for production)</option>
                                <option value="1" <?php echo e(($envConfig['APP_DEBUG'] ?? 'false') === '1' || ($envConfig['APP_DEBUG'] ?? 'false') === 'true' ? 'selected' : ''); ?>>Enabled (Development only)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card overflow-hidden">
                    <div class="px-6 py-5 border-b border-ink-100 dark:border-ink-800">
                        <h2 class="text-lg font-bold text-ink-900 dark:text-ink-50">AI Provider Keys <span class="badge-gray ml-2">Optional</span></h2>
                        <p class="mt-1 text-sm text-ink-500 dark:text-ink-400">VentureX works without AI keys. Add them here if you want AI features.</p>
                    </div>
                    <div class="p-6 space-y-5">
                        <div>
                            <label for="ai_provider" class="label">Default AI Provider</label>
                            <select id="ai_provider" name="ai_provider" class="input">
                                <option value="swift" <?php echo e(($envConfig['AI_PROVIDER'] ?? 'swift') === 'swift' ? 'selected' : ''); ?>>Swift AI (RapidAPI)</option>
                                <option value="gemini" <?php echo e(($envConfig['AI_PROVIDER'] ?? '') === 'gemini' ? 'selected' : ''); ?>>Gemini (RapidAPI)</option>
                                <option value="deepseek" <?php echo e(($envConfig['AI_PROVIDER'] ?? '') === 'deepseek' ? 'selected' : ''); ?>>DeepSeek (RapidAPI)</option>
                                <option value="nvidia" <?php echo e(($envConfig['AI_PROVIDER'] ?? '') === 'nvidia' ? 'selected' : ''); ?>>NVIDIA</option>
                                <option value="claude" <?php echo e(($envConfig['AI_PROVIDER'] ?? '') === 'claude' ? 'selected' : ''); ?>>Claude (RapidAPI)</option>
                            </select>
                        </div>
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="openai_api_key" class="label">OpenAI API Key</label>
                                <input id="openai_api_key" name="openai_api_key" type="password" value="<?php echo e(old('openai_api_key', $envConfig['OPENAI_API_KEY'] ?? '')); ?>" class="input" placeholder="sk-...">
                            </div>
                            <div>
                                <label for="gemini_api_key" class="label">Gemini API Key</label>
                                <input id="gemini_api_key" name="gemini_api_key" type="password" value="<?php echo e(old('gemini_api_key', $envConfig['GEMINI_API_KEY'] ?? '')); ?>" class="input" placeholder="RapidAPI key">
                            </div>
                            <div>
                                <label for="anthropic_api_key" class="label">Anthropic API Key</label>
                                <input id="anthropic_api_key" name="anthropic_api_key" type="password" value="<?php echo e(old('anthropic_api_key', $envConfig['ANTHROPIC_API_KEY'] ?? '')); ?>" class="input" placeholder="sk-ant-...">
                            </div>
                            <div>
                                <label for="nvidia_api_key" class="label">NVIDIA API Key</label>
                                <input id="nvidia_api_key" name="nvidia_api_key" type="password" value="<?php echo e(old('nvidia_api_key', $envConfig['NVIDIA_API_KEY'] ?? '')); ?>" class="input" placeholder="nvapi-...">
                            </div>
                        </div>
                        <div>
                            <label for="rapidapi_key" class="label">RapidAPI Key</label>
                            <input id="rapidapi_key" name="rapidapi_key" type="password" value="<?php echo e(old('rapidapi_key', $envConfig['RAPIDAPI_KEY'] ?? '')); ?>" class="input" placeholder="Used by Swift, Gemini, DeepSeek, Claude">
                        </div>
                    </div>
                </div>

                <div class="card overflow-hidden">
                    <div class="px-6 py-5 border-b border-ink-100 dark:border-ink-800">
                        <h2 class="text-lg font-bold text-ink-900 dark:text-ink-50">Mail Settings <span class="badge-gray ml-2">Optional</span></h2>
                        <p class="mt-1 text-sm text-ink-500 dark:text-ink-400">Configure email for password resets and notifications.</p>
                    </div>
                    <div class="p-6 space-y-5">
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="mail_mailer" class="label">Mailer</label>
                                <select id="mail_mailer" name="mail_mailer" class="input">
                                    <option value="log" <?php echo e(($envConfig['MAIL_MAILER'] ?? 'log') === 'log' ? 'selected' : ''); ?>>Log (testing)</option>
                                    <option value="smtp" <?php echo e(($envConfig['MAIL_MAILER'] ?? '') === 'smtp' ? 'selected' : ''); ?>>SMTP</option>
                                    <option value="sendmail" <?php echo e(($envConfig['MAIL_MAILER'] ?? '') === 'sendmail' ? 'selected' : ''); ?>>Sendmail</option>
                                </select>
                            </div>
                            <div>
                                <label for="mail_host" class="label">SMTP Host</label>
                                <input id="mail_host" name="mail_host" type="text" value="<?php echo e(old('mail_host', $envConfig['MAIL_HOST'] ?? '127.0.0.1')); ?>" class="input">
                            </div>
                            <div>
                                <label for="mail_port" class="label">SMTP Port</label>
                                <input id="mail_port" name="mail_port" type="number" value="<?php echo e(old('mail_port', $envConfig['MAIL_PORT'] ?? '2525')); ?>" class="input">
                            </div>
                            <div>
                                <label for="mail_username" class="label">SMTP Username</label>
                                <input id="mail_username" name="mail_username" type="text" value="<?php echo e(old('mail_username', $envConfig['MAIL_USERNAME'] ?? '')); ?>" class="input" placeholder="Leave empty if none">
                            </div>
                            <div>
                                <label for="mail_password" class="label">SMTP Password</label>
                                <input id="mail_password" name="mail_password" type="password" value="<?php echo e(old('mail_password', $envConfig['MAIL_PASSWORD'] ?? '')); ?>" class="input">
                            </div>
                            <div>
                                <label for="mail_from_address" class="label">From Address</label>
                                <input id="mail_from_address" name="mail_from_address" type="email" value="<?php echo e(old('mail_from_address', $envConfig['MAIL_FROM_ADDRESS'] ?? 'hello@example.com')); ?>" class="input">
                            </div>
                        </div>
                        <div>
                            <label for="mail_from_name" class="label">From Name</label>
                            <input id="mail_from_name" name="mail_from_name" type="text" value="<?php echo e(old('mail_from_name', $envConfig['MAIL_FROM_NAME'] ?? 'VentureX ERP & CRM')); ?>" class="input">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <a href="<?php echo e(route('installer.database')); ?>" class="btn-secondary">â† Back</a>
                    <button type="submit" class="btn-accent">Save & Continue â†’</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\MY_ERP\resources\views/installer/environment.blade.php ENDPATH**/ ?>