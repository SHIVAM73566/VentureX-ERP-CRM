<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Report an Error','breadcrumbs' => [
        ['label' => 'Support Center', 'url' => route('support.index')],
        ['label' => 'Report an Error'],
    ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Report an Error'),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
        ['label' => 'Support Center', 'url' => route('support.index')],
        ['label' => 'Report an Error'],
    ])]); ?>

    <div class="mx-auto max-w-3xl space-y-6">

        
        <div class="card">
            <h2 class="mb-1 text-lg font-bold text-ink-900">Auto-Diagnostics</h2>
            <p class="mb-4 text-sm text-ink-500">The following system information is collected automatically to help us resolve your issue faster.</p>

            <div x-data="{ browser: 'Detecting...' }" x-init="browser = navigator.userAgent">
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-xs font-medium uppercase tracking-wider text-ink-400">App Version</label>
                        <div class="rounded-lg border border-ink-100 bg-ink-50 px-3 py-2 text-sm text-ink-700"><?php echo e(config('app.version', '1.0.0')); ?></div>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium uppercase tracking-wider text-ink-400">PHP Version</label>
                        <div class="rounded-lg border border-ink-100 bg-ink-50 px-3 py-2 text-sm text-ink-700"><?php echo e(phpversion()); ?></div>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium uppercase tracking-wider text-ink-400">Browser</label>
                        <div class="rounded-lg border border-ink-100 bg-ink-50 px-3 py-2 text-sm text-ink-700" x-text="browser"></div>
                    </div>
                </div>
                <div class="mt-4">
                    <label class="mb-1 block text-xs font-medium uppercase tracking-wider text-ink-400">Timestamp</label>
                    <div class="rounded-lg border border-ink-100 bg-ink-50 px-3 py-2 text-sm text-ink-700"><?php echo e(now()->format('Y-m-d H:i:s')); ?> UTC</div>
                </div>
            </div>
        </div>

        
        <form action="<?php echo e(route('support.report-error.store')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <div class="card space-y-4">
                <h2 class="text-lg font-bold text-ink-900">Error Details</h2>

                <div>
                    <label for="description" class="mb-1 block text-sm font-medium text-ink-700">What happened? *</label>
                    <textarea id="description" name="description" rows="4" required
                        placeholder="Describe the error you encountered..."
                        class="w-full rounded-lg border border-ink-200 bg-white px-3 py-2.5 text-sm text-ink-700 placeholder-ink-400 focus:border-navy-500 focus:outline-none focus:ring-2 focus:ring-navy-500/20"><?php echo e(old('description')); ?></textarea>
                </div>

                <div>
                    <label for="steps" class="mb-1 block text-sm font-medium text-ink-700">Steps to Reproduce</label>
                    <textarea id="steps" name="steps_to_reproduce" rows="4"
                        placeholder="1. Go to...&#10;2. Click on...&#10;3. See error..."
                        class="w-full rounded-lg border border-ink-200 bg-white px-3 py-2.5 text-sm text-ink-700 placeholder-ink-400 focus:border-navy-500 focus:outline-none focus:ring-2 focus:ring-navy-500/20"><?php echo e(old('steps_to_reproduce')); ?></textarea>
                </div>

                <div class="flex items-start gap-2 rounded-lg bg-amber-50 px-4 py-3">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                    <p class="text-xs text-amber-700">We never collect passwords, API keys, or sensitive data. Only non-sensitive diagnostic information is attached.</p>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-navy-600 px-5 py-2.5 text-sm font-semibold text-white shadow transition hover:bg-navy-700">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                        Submit Error Report
                    </button>
                </div>
            </div>
        </form>
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
<?php /**PATH C:\MY_ERP\resources\views\support\report-error.blade.php ENDPATH**/ ?>