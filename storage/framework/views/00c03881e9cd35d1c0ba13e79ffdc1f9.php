<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'System Health','breadcrumbs' => [['label' => 'Administration'], ['label' => 'System Health']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('System Health'),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Administration'], ['label' => 'System Health']])]); ?>

    <div class="space-y-6">
        
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="card flex items-center gap-4">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl <?php echo e($score >= 80 ? 'bg-emerald-50 text-emerald-600' : ($score >= 50 ? 'bg-amber-50 text-amber-600' : 'bg-red-50 text-red-600')); ?>">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-ink-900"><?php echo e($score); ?><span class="text-sm font-semibold text-ink-400">%</span></p>
                    <p class="text-xs text-ink-500">Overall Health Score</p>
                </div>
            </div>

            <div class="card flex items-center gap-4">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-emerald-600"><?php echo e($passed); ?></p>
                    <p class="text-xs text-ink-500">Checks Passed</p>
                </div>
            </div>

            <div class="card flex items-center gap-4">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-amber-600"><?php echo e($warned); ?></p>
                    <p class="text-xs text-ink-500">Warnings</p>
                </div>
            </div>

            <div class="card flex items-center gap-4">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl <?php echo e($failed > 0 ? 'bg-red-50 text-red-600' : 'bg-ink-100 text-ink-500'); ?>">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold <?php echo e($failed > 0 ? 'text-red-600' : 'text-ink-900'); ?>"><?php echo e($failed); ?></p>
                    <p class="text-xs text-ink-500">Failures</p>
                </div>
            </div>
        </div>

        
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="card px-4 py-3">
                <p class="text-[10px] font-bold uppercase tracking-widest text-ink-400">PHP</p>
                <p class="mt-1 text-sm font-semibold text-ink-800"><?php echo e($phpVersion); ?></p>
            </div>
            <div class="card px-4 py-3">
                <p class="text-[10px] font-bold uppercase tracking-widest text-ink-400">Laravel</p>
                <p class="mt-1 text-sm font-semibold text-ink-800"><?php echo e($laravelVersion); ?></p>
            </div>
            <div class="card px-4 py-3">
                <p class="text-[10px] font-bold uppercase tracking-widest text-ink-400">Server Time</p>
                <p class="mt-1 text-sm font-semibold text-ink-800"><?php echo e($serverTime); ?> <?php echo e($timezone); ?></p>
            </div>
            <div class="card px-4 py-3">
                <p class="text-[10px] font-bold uppercase tracking-widest text-ink-400">Uptime</p>
                <p class="mt-1 text-sm font-semibold text-ink-800"><?php echo e($uptime); ?></p>
            </div>
        </div>

        
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <?php $__currentLoopData = $checks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $check): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="card group">
                    <div class="mb-3 flex items-start justify-between gap-2">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <?php if($check['status'] === 'pass'): ?>
                                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    </span>
                                <?php elseif($check['status'] === 'warning'): ?>
                                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01"/></svg>
                                    </span>
                                <?php else: ?>
                                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-700">
                                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </span>
                                <?php endif; ?>
                                <h3 class="truncate text-sm font-semibold text-ink-800 dark:text-ink-100"><?php echo e($check['name']); ?></h3>
                            </div>
                            <p class="mt-1 text-xs text-ink-400 line-clamp-2"><?php echo e($check['description']); ?></p>
                        </div>
                        <span class="badge shrink-0
                            <?php echo e($check['status'] === 'pass' ? 'badge-green' : ($check['status'] === 'warning' ? 'badge-amber' : 'badge-red')); ?>">
                            <?php echo e($check['status'] === 'pass' ? 'Pass' : ($check['status'] === 'warning' ? 'Warning' : 'Fail')); ?>

                        </span>
                    </div>

                    <div class="rounded-lg bg-ink-50 px-3 py-2 dark:bg-ink-800">
                        <p class="text-xs font-medium text-ink-600 dark:text-ink-300"><?php echo e($check['value']); ?></p>
                        <?php if($check['detail']): ?>
                            <p class="mt-0.5 text-[11px] text-ink-400 truncate"><?php echo e($check['detail']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        
        <div class="card">
            <h2 class="mb-3 text-sm font-bold text-ink-800 dark:text-ink-100">Check Categories</h2>
            <div class="flex flex-wrap gap-2">
                <?php
                    $categories = collect($checks)->pluck('category')->unique()->sort()->values();
                ?>
                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $catTotal = collect($checks)->where('category', $cat)->count();
                        $catPassed = collect($checks)->where('category', $cat)->where('status', 'pass')->count();
                        $catFails = collect($checks)->where('category', $cat)->where('status', 'fail')->count();
                    ?>
                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium
                        <?php echo e($catFails > 0 ? 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20' : 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20'); ?>">
                        <span class="h-1.5 w-1.5 rounded-full <?php echo e($catFails > 0 ? 'bg-red-500' : 'bg-emerald-500'); ?>"></span>
                        <?php echo e(ucfirst($cat)); ?> (<?php echo e($catPassed); ?>/<?php echo e($catTotal); ?>)
                    </span>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $attributes = $__attributesOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__attributesOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $component = $__componentOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__componentOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>
<?php /**PATH C:\MY_ERP\resources\views/admin/system-health/index.blade.php ENDPATH**/ ?>