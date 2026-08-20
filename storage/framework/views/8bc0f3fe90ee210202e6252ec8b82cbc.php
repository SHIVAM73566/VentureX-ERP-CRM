<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Recovery codes — <?php echo e(config('app.name', 'MyERP')); ?></title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="h-full bg-ink-100">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="w-full max-w-xl overflow-hidden rounded-2xl border border-ink-200 bg-white p-8 shadow-xl sm:p-10">
            <h1 class="text-lg font-bold text-ink-900">Save your recovery codes</h1>
            <p class="mt-2 text-sm text-ink-500">These codes are shown <strong>once</strong>. Store them somewhere safe. Each code can be used a single time to sign in if you lose your authenticator app.</p>

            <div class="mt-6 grid grid-cols-2 gap-3">
                <?php $__currentLoopData = $codes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="rounded-lg border border-ink-200 bg-ink-50 px-4 py-3 text-center font-mono text-sm font-bold tracking-widest text-navy-800 select-all"><?php echo e($code); ?></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <a href="<?php echo e(route('dashboard')); ?>" class="btn-primary mt-6 w-full justify-center py-2.5">I saved my recovery codes — continue</a>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\MY_ERP\resources\views\auth\mfa-recovery.blade.php ENDPATH**/ ?>