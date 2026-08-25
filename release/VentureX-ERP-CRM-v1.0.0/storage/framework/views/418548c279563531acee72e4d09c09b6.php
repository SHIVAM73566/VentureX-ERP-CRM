<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Import Results','breadcrumbs' => [['label' => 'Administration', 'url' => route('admin.imports.index')], ['label' => 'Results']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Import Results'),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Administration', 'url' => route('admin.imports.index')], ['label' => 'Results']])]); ?>

    <div class="mx-auto max-w-4xl space-y-6">
        <div class="mb-8 text-center">
            <?php if($import->status === 'completed' || $import->status === 'completed_with_errors'): ?>
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
                    <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h1 class="text-2xl font-bold text-ink-900">Import Complete!</h1>
            <?php else: ?>
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-red-100">
                    <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <h1 class="text-2xl font-bold text-ink-900">Import Failed</h1>
            <?php endif; ?>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-center">
                <div class="text-3xl font-bold text-green-700"><?php echo e(number_format($results['created'])); ?></div>
                <div class="text-xs text-green-600">Created</div>
            </div>
            <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-center">
                <div class="text-3xl font-bold text-blue-700"><?php echo e(number_format($results['updated'])); ?></div>
                <div class="text-xs text-blue-600">Updated</div>
            </div>
            <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-4 text-center">
                <div class="text-3xl font-bold text-yellow-700"><?php echo e(number_format($results['skipped'])); ?></div>
                <div class="text-xs text-yellow-600">Skipped</div>
            </div>
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-center">
                <div class="text-3xl font-bold text-red-700"><?php echo e(number_format($results['failed'])); ?></div>
                <div class="text-xs text-red-600">Failed</div>
            </div>
        </div>

        <?php if(!empty($results['errors'])): ?>
        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
            <h3 class="text-sm font-semibold text-red-800">Errors (<?php echo e(count($results['errors'])); ?>)</h3>
            <div class="mt-2 max-h-40 overflow-y-auto text-xs text-red-700">
                <?php $__currentLoopData = array_slice($results['errors'], 0, 20); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $err): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="mb-1">Row <?php echo e($err['row']); ?>: <?php echo e($err['error']); ?></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="flex items-center justify-between">
            <a href="<?php echo e(route('admin.imports.show', $import)); ?>" class="btn-secondary">View Details</a>
            <div class="flex gap-3">
                <?php if($results['failed'] > 0 || $results['skipped'] > 0): ?>
                <a href="<?php echo e(route('admin.imports.error-report', $import)); ?>" class="btn-secondary">Download Error Report</a>
                <?php endif; ?>
                <a href="<?php echo e(route('admin.imports.create')); ?>" class="btn-primary">New Import</a>
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
<?php /**PATH C:\MY_ERP\resources\views/admin/imports/results.blade.php ENDPATH**/ ?>