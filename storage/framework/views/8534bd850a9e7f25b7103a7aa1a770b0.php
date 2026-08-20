<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Re-authenticate — <?php echo e(config('app.name', 'MyERP')); ?></title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="h-full bg-ink-100">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="w-full max-w-md overflow-hidden rounded-2xl border border-ink-200 bg-white p-8 shadow-xl sm:p-10">
            <h1 class="text-lg font-bold text-ink-900">Re-authentication required</h1>
            <p class="mt-2 text-sm text-ink-500">This is a sensitive action. Confirm it is you by entering your password (or an MFA code if enabled).</p>

            <form method="POST" action="<?php echo e(route('step-up.verify')); ?>" class="mt-6">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="intended" value="<?php echo e($intended); ?>">
                <div>
                    <label for="password" class="label">Current password</label>
                    <input id="password" type="password" name="password" autocomplete="current-password" class="input" required autofocus>
                </div>
                <?php if(auth()->user()?->hasMfa()): ?>
                    <div class="mt-4">
                        <label for="code" class="label">Or MFA code</label>
                        <input id="code" name="code" inputmode="numeric" autocomplete="one-time-code" class="input">
                    </div>
                <?php endif; ?>
                <?php if($errors->any()): ?>
                    <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><?php echo e($errors->first()); ?></div>
                <?php endif; ?>
                <button type="submit" class="btn-primary mt-5 w-full justify-center py-2.5">Verify identity</button>
            </form>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\MY_ERP\resources\views\auth\step-up.blade.php ENDPATH**/ ?>