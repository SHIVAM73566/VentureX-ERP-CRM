<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Import Details','breadcrumbs' => [['label' => 'Administration', 'url' => route('admin.imports.index')], ['label' => $import->file_name]]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Import Details'),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Administration', 'url' => route('admin.imports.index')], ['label' => $import->file_name]])]); ?>

    <div class="mx-auto max-w-6xl space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <a href="<?php echo e(route('admin.imports.index')); ?>" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to Imports</a>
                <h1 class="mt-2 text-2xl font-bold text-ink-900"><?php echo e($import->file_name); ?></h1>
                <p class="mt-1 text-sm text-ink-500"><?php echo e($import->getDestinationLabel()); ?> &middot; <?php echo e($import->created_at->diffForHumans()); ?></p>
            </div>
            <div class="flex gap-3">
                <?php if($import->failed_rows > 0 || $import->skipped_rows > 0): ?>
                <a href="<?php echo e(route('admin.imports.error-report', $import)); ?>" class="btn-secondary">Error Report</a>
                <?php endif; ?>
                <form action="<?php echo e(route('admin.imports.destroy', $import)); ?>" method="POST" onsubmit="return confirm('Delete this import?')">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                    <button class="rounded-lg border border-red-300 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-50">Delete</button>
                </form>
            </div>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-ink-200 bg-white p-4 text-center">
                <div class="text-2xl font-bold text-ink-900"><?php echo e(number_format($import->total_rows)); ?></div>
                <div class="text-xs text-ink-500">Total Rows</div>
            </div>
            <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-center">
                <div class="text-2xl font-bold text-green-700"><?php echo e(number_format($import->created_rows)); ?></div>
                <div class="text-xs text-green-600">Created</div>
            </div>
            <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-4 text-center">
                <div class="text-2xl font-bold text-yellow-700"><?php echo e(number_format($import->skipped_rows)); ?></div>
                <div class="text-xs text-yellow-600">Skipped</div>
            </div>
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-center">
                <div class="text-2xl font-bold text-red-700"><?php echo e(number_format($import->failed_rows)); ?></div>
                <div class="text-xs text-red-600">Failed</div>
            </div>
        </div>

        <div class="rounded-xl border border-ink-200 bg-white">
            <div class="border-b border-ink-200 px-6 py-4">
                <h2 class="text-lg font-semibold text-ink-900">Row Details</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-ink-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-ink-500">Row</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-ink-500">Status</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-ink-500">Record ID</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-ink-500">Errors</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-ink-500">Raw Data</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100">
                        <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-ink-50">
                            <td class="px-4 py-2 text-xs text-ink-500"><?php echo e($row->row_number); ?></td>
                            <td class="px-4 py-2">
                                <?php $colors = ['created' => 'green', 'updated' => 'blue', 'skipped' => 'yellow', 'failed' => 'red']; ?>
                                <span class="rounded-full bg-<?php echo e($colors[$row->status] ?? 'gray'); ?>-100 px-2 py-0.5 text-xs font-medium text-<?php echo e($colors[$row->status] ?? 'gray'); ?>-800"><?php echo e(ucfirst($row->status)); ?></span>
                            </td>
                            <td class="px-4 py-2 text-xs text-ink-500"><?php echo e($row->imported_record_id ?? '&mdash;'); ?></td>
                            <td class="px-4 py-2 text-xs text-red-600"><?php echo e($row->errors ?? '&mdash;'); ?></td>
                            <td class="px-4 py-2 text-xs text-ink-500 max-w-xs truncate"><?php echo e(is_array($row->raw_data) ? json_encode($row->raw_data) : $row->raw_data); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <div class="border-t border-ink-200 px-6 py-3">
                <?php echo e($rows->links()); ?>

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
<?php /**PATH C:\MY_ERP\resources\views\admin\imports\show.blade.php ENDPATH**/ ?>