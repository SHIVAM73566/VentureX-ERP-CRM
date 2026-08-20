<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Trusted Devices','breadcrumbs' => [['label' => 'Account', 'url' => route('devices.index')], ['label' => 'Trusted Devices']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Trusted Devices'),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Account', 'url' => route('devices.index')], ['label' => 'Trusted Devices']])]); ?>

    <div class="mx-auto max-w-3xl space-y-4">
        <div class="card">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-ink-900">Your trusted devices</h2>
                    <p class="text-sm text-ink-500">Devices you have explicitly trusted. Removing a trusted device will require re-verification on next login from that device.</p>
                </div>
            </div>

            <div class="divide-y divide-ink-100">
                <?php $__empty_1 = true; $__currentLoopData = $devices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $device): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="flex items-center justify-between gap-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg <?php echo e($device->is_trusted ? 'bg-emerald-100 text-emerald-600' : 'bg-ink-100 text-ink-500'); ?>">
                                <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-ink-800">
                                    <?php echo e($device->device_name); ?>

                                    <?php if($device->is_trusted): ?>
                                        <span class="badge badge-green ml-1">Trusted</span>
                                    <?php endif; ?>
                                </p>
                                <p class="text-xs text-ink-400">IP: <?php echo e($device->last_ip ?? $device->ip_address); ?></p>
                                <p class="text-xs text-ink-400">Last seen <?php echo e($device->last_seen_at?->diffForHumans() ?? 'Never'); ?></p>
                            </div>
                        </div>
                        <form method="POST" action="<?php echo e(route('devices.remove', $device->id)); ?>" onsubmit="return confirm('Remove this trusted device? You will need to re-verify on next login.')">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn-danger">Remove</button>
                        </form>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="py-8 text-center text-sm text-ink-400">No trusted devices found.</p>
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
<?php /**PATH C:\MY_ERP\resources\views/auth/trusted-devices.blade.php ENDPATH**/ ?>