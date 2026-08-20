<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Too Many Requests â€” <?php echo e(config('app.name', 'VentureX ERP & CRM')); ?></title>
    <link rel="stylesheet" href="<?php echo e(asset('build/assets/fonts-C9MNnjVw.css')); ?>">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css']); ?>
</head>
<body class="h-full bg-ink-100 flex items-center justify-center">
    <div class="text-center max-w-md mx-auto px-4">
        <div class="text-7xl font-bold text-ink-300 mb-4">429</div>
        <h1 class="text-2xl font-semibold text-ink-900 mb-2">Too Many Requests</h1>
        <p class="text-ink-500 mb-8">You're making too many requests. Please wait a moment.</p>
        <button onclick="history.back()" class="btn-primary inline-flex items-center px-6 py-3">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Go Back
        </button>
    </div>
</body>
</html>
<?php /**PATH C:\MY_ERP\resources\views\errors\429.blade.php ENDPATH**/ ?>