<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Server Error — <?php echo e(config('app.name', 'VentureX ERP & CRM')); ?></title>
    <link rel="icon" type="image/svg+xml" href="<?php echo e(asset('favicon.svg')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('build/assets/fonts-C9MNnjVw.css')); ?>">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css']); ?>
</head>
<body class="h-full bg-ink-100 flex items-center justify-center">
    <div class="text-center max-w-md mx-auto px-4">
        <div class="text-7xl font-bold text-ink-300 mb-4">500</div>
        <h1 class="text-2xl font-semibold text-ink-900 mb-2">Server Error</h1>
        <p class="text-ink-500 mb-8">Something went wrong. Please try again.</p>
        <button onclick="location.reload()" class="btn-primary inline-flex items-center px-6 py-3">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Try Again
        </button>
    </div>
</body>
</html>
<?php /**PATH C:\MY_ERP\resources\views/errors/500.blade.php ENDPATH**/ ?>