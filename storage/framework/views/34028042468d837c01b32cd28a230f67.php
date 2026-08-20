<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'License Information','breadcrumbs' => [['label' => 'Administration', 'url' => route('security.dashboard')], ['label' => 'License Information']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('License Information'),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Administration', 'url' => route('security.dashboard')], ['label' => 'License Information']])]); ?>

    <div class="mx-auto max-w-3xl space-y-6">
        <div class="card">
            <h2 class="mb-4 text-lg font-bold text-ink-900">License Details</h2>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-xs font-medium text-ink-500">Reference Number</p>
                    <p class="font-mono text-sm text-ink-900"><?php echo e($license['reference'] ?? 'N/A'); ?></p>
                </div>
                <div>
                    <p class="text-xs font-medium text-ink-500">Version</p>
                    <p class="text-sm text-ink-900"><?php echo e($license['version'] ?? 'N/A'); ?></p>
                </div>
                <div>
                    <p class="text-xs font-medium text-ink-500">Status</p>
                    <?php if($license['licensed'] ?? false): ?>
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Licensed
                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-semibold text-red-700">
                            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            Unlicensed
                        </span>
                    <?php endif; ?>
                </div>
                <div>
                    <p class="text-xs font-medium text-ink-500">License Tier</p>
                    <p class="text-sm font-semibold text-ink-900"><?php echo e(ucfirst($license['tier'] ?? 'none')); ?></p>
                </div>
            </div>

            <?php if($license['message'] ?? null): ?>
                <div class="mt-4 rounded-lg bg-amber-50 p-3 text-sm text-amber-800">
                    <?php echo e($license['message']); ?>

                </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2 class="mb-2 text-lg font-bold text-ink-900">Activate License</h2>
            <p class="mb-4 text-sm text-ink-500">Enter your license key to activate your purchase and receive updates.</p>

            <form method="POST" action="<?php echo e(route('security.license.activate')); ?>">
                <?php echo csrf_field(); ?>
                <div class="flex gap-3">
                    <input
                        type="text"
                        name="license_key"
                        placeholder="TWIT-ERP-XXXX-XXXX-XXXX"
                        class="input flex-1"
                        value="<?php echo e(old('license_key')); ?>"
                    >
                    <button type="submit" class="btn-primary">Activate</button>
                </div>
            </form>
        </div>

        <?php if($license['purchase_url'] ?? null): ?>
            <div class="card">
                <h2 class="mb-2 text-lg font-bold text-ink-900">Need a License?</h2>
                <p class="mb-3 text-sm text-ink-500">Purchase a license to unlock all features, receive updates, and get support.</p>
                <a href="<?php echo e($license['purchase_url']); ?>" target="_blank" rel="noopener" class="btn-primary">
                    Purchase License
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            </div>
        <?php endif; ?>
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
<?php /**PATH C:\MY_ERP\resources\views/admin/security/license.blade.php ENDPATH**/ ?>