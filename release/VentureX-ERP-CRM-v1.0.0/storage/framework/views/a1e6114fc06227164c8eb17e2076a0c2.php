<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Import History','breadcrumbs' => [['label' => 'Administration', 'url' => route('admin.imports.index')], ['label' => 'Import History']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Import History'),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Administration', 'url' => route('admin.imports.index')], ['label' => 'Import History']])]); ?>

    <div class="mx-auto max-w-6xl space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-ink-900">Import History</h1>
            <p class="mt-1 text-sm text-ink-500">View all past imports and their status.</p>
        </div>

        <?php if($imports->count() === 0): ?>
            <div class="rounded-xl border border-ink-200 bg-white p-12 text-center">
                <p class="text-sm text-ink-500">No imports have been run yet.</p>
            </div>
        <?php else: ?>
            <div class="overflow-hidden rounded-xl border border-ink-200 bg-white">
                <table class="min-w-full divide-y divide-ink-200">
                    <thead class="bg-ink-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink-500">File</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink-500">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink-500">Created</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink-500">Updated</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink-500">Skipped</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink-500">Failed</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink-500">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink-500">Date</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-ink-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-200">
                        <?php $__currentLoopData = $imports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $import): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-ink-50">
                            <td class="px-6 py-4 text-sm font-medium text-ink-900"><?php echo e($import->file_name); ?></td>
                            <td class="px-6 py-4 text-sm text-ink-600"><?php echo e($import->getDestinationLabel()); ?></td>
                            <td class="px-6 py-4 text-sm text-ink-600"><?php echo e($import->created_rows); ?></td>
                            <td class="px-6 py-4 text-sm text-ink-600"><?php echo e($import->updated_rows); ?></td>
                            <td class="px-6 py-4 text-sm text-ink-600"><?php echo e($import->skipped_rows); ?></td>
                            <td class="px-6 py-4 text-sm text-ink-600"><?php echo e($import->failed_rows); ?></td>
                            <td class="px-6 py-4">
                                <?php $colors = ['completed' => 'green', 'completed_with_errors' => 'yellow', 'processing' => 'blue', 'pending' => 'gray', 'failed' => 'red', 'uploaded' => 'blue']; ?>
                                <span class="rounded-full bg-<?php echo e($colors[$import->status] ?? 'gray'); ?>-100 px-2.5 py-0.5 text-xs font-medium text-<?php echo e($colors[$import->status] ?? 'gray'); ?>-800"><?php echo e(ucfirst(str_replace('_', ' ', $import->status))); ?></span>
                            </td>
                            <td class="px-6 py-4 text-xs text-ink-500"><?php echo e($import->created_at->diffForHumans()); ?></td>
                            <td class="px-6 py-4 text-right">
                                <a href="<?php echo e(route('admin.imports.show', $import)); ?>" class="text-indigo-600 hover:text-indigo-900 text-sm">View</a>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <div><?php echo e($imports->links()); ?></div>
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
<?php /**PATH C:\MY_ERP\resources\views/admin/imports/history.blade.php ENDPATH**/ ?>