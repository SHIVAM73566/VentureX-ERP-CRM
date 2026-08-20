<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Access Denied â€” <?php echo e(config('app.name', 'VentureX ERP & CRM')); ?></title>
    <link rel="stylesheet" href="<?php echo e(asset('build/assets/fonts-C9MNnjVw.css')); ?>">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css']); ?>
</head>
<body class="h-full bg-ink-100 flex items-center justify-center">
    <div class="text-center max-w-md mx-auto px-4">
        <div class="text-7xl font-bold text-ink-300 mb-4">403</div>
        <h1 class="text-2xl font-semibold text-ink-900 mb-2">Access Denied</h1>
        <p class="text-ink-500 mb-8">You don't have permission to access this page.</p>
        <a href="<?php echo e(url('/')); ?>" class="btn-primary inline-flex items-center px-6 py-3">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Back to Dashboard
        </a>
    </div>
</body>
</html>
<?php /**PATH C:\MY_ERP\resources\views\errors\403.blade.php ENDPATH**/ ?>