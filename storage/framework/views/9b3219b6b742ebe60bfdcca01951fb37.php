<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Installer — VentureX ERP & CRM</title>
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
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-navy-900 text-xl font-bold text-white shadow-lg">T</div>
                <h1 class="mt-5 text-2xl font-bold text-ink-900 dark:text-ink-50">VentureX ERP & CRM</h1>
                <p class="mt-1 text-sm text-ink-500 dark:text-ink-400">Installation Wizard</p>
            </div>

            <div class="mb-6 flex items-center justify-center gap-2 text-xs font-semibold text-ink-400 dark:text-ink-500">
                <span class="flex items-center gap-1.5">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-navy-800 text-[11px] font-bold text-white">1</span>
                    Requirements
                </span>
                <span class="h-px w-6 bg-ink-200 dark:bg-ink-700"></span>
                <span>2 Database</span>
                <span class="h-px w-6 bg-ink-200 dark:bg-ink-700"></span>
                <span>3 Config</span>
                <span class="h-px w-6 bg-ink-200 dark:bg-ink-700"></span>
                <span>4 Admin</span>
                <span class="h-px w-6 bg-ink-200 dark:bg-ink-700"></span>
                <span>5 Install</span>
            </div>

            <div class="card overflow-hidden">
                <div class="px-6 py-5 border-b border-ink-100 dark:border-ink-800">
                    <h2 class="text-lg font-bold text-ink-900 dark:text-ink-50">System Requirements</h2>
                    <p class="mt-1 text-sm text-ink-500 dark:text-ink-400">Checking that your server meets all requirements.</p>
                </div>

                <div class="divide-y divide-ink-100 dark:divide-ink-800">
                    <?php $__currentLoopData = $requirements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $req): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center gap-3 px-6 py-3">
                            <?php if($req['passed']): ?>
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-500/20">
                                    <svg class="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </span>
                            <?php else: ?>
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-500/20">
                                    <svg class="h-3.5 w-3.5 text-red-600 dark:text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </span>
                            <?php endif; ?>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-ink-800 dark:text-ink-200"><?php echo e($req['label']); ?></p>
                                <p class="text-xs text-ink-400 dark:text-ink-500"><?php echo e($req['detail']); ?></p>
                            </div>
                            <?php if($req['passed']): ?>
                                <span class="badge-green">Pass</span>
                            <?php else: ?>
                                <span class="badge-red">Fail</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <?php
                    $failed = collect($requirements)->where('passed', false)->count();
                ?>

                <div class="px-6 py-4 border-t border-ink-100 dark:border-ink-800 bg-ink-50/50 dark:bg-ink-800/30">
                    <?php if($failed === 0): ?>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2 text-sm text-emerald-700 dark:text-emerald-400">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                All requirements passed! You can proceed with installation.
                            </div>
                            <a href="<?php echo e(route('installer.database')); ?>" class="btn-accent">Continue →</a>
                        </div>
                    <?php else: ?>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2 text-sm text-red-700 dark:text-red-400">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                                <?php echo e($failed); ?> requirement(s) failed. Please fix them before continuing.
                            </div>
                            <button onclick="location.reload()" class="btn-secondary">Re-check</button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mt-6 card p-5">
                <h3 class="text-sm font-bold text-ink-700 dark:text-ink-300">Prefer CLI?</h3>
                <p class="mt-1 text-xs text-ink-500 dark:text-ink-400">If the web installer is not accessible, run these commands manually:</p>
                <pre class="mt-3 overflow-x-auto rounded-lg bg-ink-900 p-4 text-xs text-emerald-400"><code>php artisan key:generate
php artisan migrate --force
php artisan db:seed --class=DemoCredentialSeeder
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
touch storage/.installed</code></pre>
            </div>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\MY_ERP\resources\views\installer\welcome.blade.php ENDPATH**/ ?>