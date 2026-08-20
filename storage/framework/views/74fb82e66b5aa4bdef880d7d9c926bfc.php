<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>App Lock â€” <?php echo e(config('app.name', 'VentureX ERP & CRM')); ?></title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="h-full bg-ink-100" x-data="lockApp()">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="w-full max-w-sm rounded-2xl border border-ink-200 bg-white p-8 shadow-xl">

            <!-- Lock Icon -->
            <div class="text-center">
                <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-full bg-ink-50 border-2 border-ink-200">
                    <svg class="h-12 w-12 text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h1 class="mt-6 text-2xl font-bold text-ink-900">App Locked</h1>
                <p class="mt-2 text-sm text-ink-500">Enter your 6-digit PIN to unlock</p>
            </div>

            <!-- PIN Input -->
            <div class="mt-8 flex justify-center space-x-3">
                <template x-for="(_, i) in 6" :key="i">
                    <input type="password" maxlength="1" class="input h-14 w-12 text-center text-2xl font-bold"
                           :id="'pin-' + i"
                           @input="handlePinInput($event, i)"
                           @keydown.backspace="handleBackspace($event, i)"
                           @paste="handlePaste($event)">
                </template>
            </div>

            <!-- Error -->
            <div x-show="error" class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" x-text="error"></div>

            <!-- Attempts -->
            <p x-show="attempts > 0" class="mt-4 text-center text-xs text-yellow-600" x-text="attempts + ' failed attempts. Lockout after 5.'"></p>

            <!-- Unlock Button -->
            <button @click="unlock()"
                    class="btn-primary mt-6 w-full justify-center py-2.5"
                    :disabled="pin.length !== 6 || loading">
                <span x-show="!loading">Unlock</span>
                <span x-show="loading">Verifying...</span>
            </button>

            <!-- Security Info -->
            <div class="mt-8 rounded-xl border border-ink-200 bg-ink-50 p-4">
                <h3 class="mb-2 text-sm font-bold text-ink-700">Security Features Active</h3>
                <ul class="space-y-1 text-xs text-ink-500">
                    <li>PIN-based app lock</li>
                    <li>Device fingerprinting</li>
                    <li>Brute force protection (5 attempts max)</li>
                    <li>15-minute lockout after failed attempts</li>
                    <li>All data encrypted at rest</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
    function lockApp() {
        return {
            pin: '',
            error: null,
            loading: false,
            attempts: 0,

            handlePinInput(event, index) {
                const value = event.target.value;
                if (value && index < 5) {
                    document.getElementById('pin-' + (index + 1)).focus();
                }
                this.pin = Array.from({length: 6}, (_, i) => document.getElementById('pin-' + i)?.value || '').join('');
            },

            handleBackspace(event, index) {
                if (!event.target.value && index > 0) {
                    document.getElementById('pin-' + (index - 1)).focus();
                }
            },

            handlePaste(event) {
                const paste = (event.clipboardData || window.clipboardData).getData('text');
                if (/^\d{6}$/.test(paste)) {
                    for (let i = 0; i < 6; i++) {
                        document.getElementById('pin-' + i).value = paste[i];
                    }
                    this.pin = paste;
                    document.getElementById('pin-5').focus();
                }
            },

            async unlock() {
                this.loading = true;
                this.error = null;
                try {
                    const res = await fetch('/auth/unlock', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify({ pin: this.pin })
                    });
                    const data = await res.json();
                    if (data.redirect) { window.location.href = data.redirect; }
                    else { this.error = 'Invalid PIN'; this.attempts++; }
                } catch(e) { this.error = 'Network error'; }
                this.loading = false;
            }
        }
    }
    </script>
</body>
</html>
<?php /**PATH C:\MY_ERP\resources\views\auth\lock-screen.blade.php ENDPATH**/ ?>