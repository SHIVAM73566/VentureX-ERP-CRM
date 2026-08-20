<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Security Center','breadcrumbs' => [['label' => 'Administration'], ['label' => 'Security Center']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Security Center'),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Administration'], ['label' => 'Security Center']])]); ?>

    <div class="mx-auto max-w-5xl space-y-6">
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="card flex items-center gap-4">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6l8-3z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-ink-900"><?php echo e($score['score']); ?><span class="text-sm font-semibold text-ink-400">/100</span></p>
                    <p class="text-xs text-ink-500">Security score</p>
                </div>
            </div>

            <div class="card flex items-center gap-4">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl <?php echo e($alerts > 0 ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-600'); ?>">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold <?php echo e($alerts > 0 ? 'text-red-600' : 'text-ink-900'); ?>"><?php echo e($alerts); ?></p>
                    <p class="text-xs text-ink-500">High/Critical alerts (24h)</p>
                </div>
            </div>

            <div class="card flex items-center gap-4">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl <?php echo e($lockdown ? 'bg-red-50 text-red-600' : 'bg-ink-100 text-ink-500'); ?>">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold <?php echo e($lockdown ? 'text-red-600' : 'text-ink-900'); ?>"><?php echo e($lockdown ? 'ACTIVE' : 'Off'); ?></p>
                    <p class="text-xs text-ink-500">Emergency lockdown</p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-ink-900">Emergency lockdown</h2>
                    <p class="text-sm text-ink-500">Blocks all non-administrative access immediately. Super admins can always reach this page to lift it.</p>
                </div>
                <?php if($lockdown): ?>
                    <form method="POST" action="<?php echo e(route('security.lockdown')); ?>" onsubmit="return confirm('Lift emergency lockdown?')">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="active" value="0">
                        <button type="submit" class="btn-secondary">Lift lockdown</button>
                    </form>
                <?php else: ?>
                    <form method="POST" action="<?php echo e(route('security.lockdown')); ?>" onsubmit="return confirm('Activate emergency lockdown? All normal access will be blocked.')">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="active" value="1">
                        <button type="submit" class="btn-danger">Activate lockdown</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <h2 class="mb-4 text-lg font-bold text-ink-900">Security checkpoints</h2>
            <div class="divide-y divide-ink-100">
                <?php $__currentLoopData = $score['checkpoints']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $checkpoint): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-center justify-between gap-3 py-2.5">
                        <div class="flex items-center gap-3">
                            <?php if($checkpoint['passed']): ?>
                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </span>
                            <?php else: ?>
                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01"/></svg>
                                </span>
                            <?php endif; ?>
                            <span class="text-sm <?php echo e($checkpoint['passed'] ? 'text-ink-800' : 'text-ink-600'); ?>"><?php echo e($checkpoint['label']); ?></span>
                        </div>
                        <span class="text-xs font-semibold <?php echo e($checkpoint['passed'] ? 'text-emerald-600' : 'text-amber-600'); ?>">+<?php echo e($checkpoint['weight']); ?> pts</span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <div class="card">
            <h2 class="mb-4 text-lg font-bold text-ink-900">Recent security events</h2>
            <div class="divide-y divide-ink-100">
                <?php $__empty_1 = true; $__currentLoopData = $recentEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="flex items-center justify-between gap-3 py-2.5">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-ink-800"><?php echo e($event->title); ?></p>
                            <p class="text-xs text-ink-400"><?php echo e($event->event); ?> · <?php echo e($event->ip ?? '—'); ?> · <?php echo e($event->user?->email ?? 'system'); ?> · <?php echo e($event->created_at->diffForHumans()); ?></p>
                        </div>
                        <span class="badge <?php echo e($event->severity === 'high' || $event->severity === 'critical' ? 'badge-red' : ($event->severity === 'medium' ? 'badge-amber' : 'badge-gray')); ?>"><?php echo e($event->severity); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="py-6 text-center text-sm text-ink-400">No security events recorded yet.</p>
                <?php endif; ?>
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
<?php /**PATH C:\MY_ERP\resources\views/admin/security/index.blade.php ENDPATH**/ ?>