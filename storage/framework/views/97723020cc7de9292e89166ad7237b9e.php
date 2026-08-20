<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Import Data','breadcrumbs' => [['label' => 'Administration'], ['label' => 'Import Data']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Import Data'),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Administration'], ['label' => 'Import Data']])]); ?>

    <div class="mx-auto max-w-7xl space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-ink-900">Import Data</h1>
                <p class="mt-1 text-sm text-ink-500">Import customers, suppliers, products, and more from CSV or JSON files.</p>
            </div>
            <a href="<?php echo e(route('admin.imports.create')); ?>" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Import
            </a>
        </div>

        <?php if($imports->count() === 0): ?>
            <div class="rounded-xl border border-ink-200 bg-white p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-ink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                <h3 class="mt-4 text-lg font-medium text-ink-900">No imports yet</h3>
                <p class="mt-2 text-sm text-ink-500">Upload a CSV or JSON file to import your business data.</p>
                <a href="<?php echo e(route('admin.imports.create')); ?>" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Start Import</a>
            </div>
        <?php else: ?>
            <div class="overflow-hidden rounded-xl border border-ink-200 bg-white">
                <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-ink-200">
                    <thead class="bg-ink-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink-500">Import</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink-500">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink-500">Records</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink-500">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink-500">Date</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-ink-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-200">
                        <?php $__currentLoopData = $imports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $import): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-ink-50">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-ink-900"><?php echo e($import->file_name); ?></div>
                                <div class="text-xs text-ink-500">by <?php echo e($import->user->name ?? 'Unknown'); ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-800"><?php echo e($import->getDestinationLabel()); ?></span>
                            </td>
                            <td class="px-6 py-4 text-sm text-ink-600">
                                <?php echo e($import->created_rows); ?> created
                                <?php if($import->updated_rows > 0): ?> &middot; <?php echo e($import->updated_rows); ?> updated <?php endif; ?>
                                <?php if($import->failed_rows > 0): ?> &middot; <span class="text-red-600"><?php echo e($import->failed_rows); ?> failed</span> <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php $statusColors = ['completed' => 'green', 'completed_with_errors' => 'yellow', 'processing' => 'blue', 'pending' => 'gray', 'failed' => 'red', 'uploaded' => 'blue']; ?>
                                <?php $color = $statusColors[$import->status] ?? 'gray'; ?>
                                <span class="inline-flex items-center rounded-full bg-<?php echo e($color); ?>-100 px-2.5 py-0.5 text-xs font-medium text-<?php echo e($color); ?>-800"><?php echo e(ucfirst(str_replace('_', ' ', $import->status))); ?></span>
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
<?php /**PATH C:\MY_ERP\resources\views\admin\imports\index.blade.php ENDPATH**/ ?>