<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Import Preview','breadcrumbs' => [['label' => 'Administration', 'url' => route('admin.imports.index')], ['label' => 'Preview']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Import Preview'),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Administration', 'url' => route('admin.imports.index')], ['label' => 'Preview']])]); ?>

    <div class="mx-auto max-w-6xl space-y-6">
        <div>
            <div class="flex items-center gap-2 text-sm text-ink-500">
                <span>Upload</span><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span>Map Columns</span><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="font-medium text-indigo-600">Preview</span><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span>Import</span>
            </div>
            <h1 class="mt-2 text-2xl font-bold text-ink-900">Import Preview</h1>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-ink-200 bg-white p-4 text-center">
                <div class="text-2xl font-bold text-ink-900"><?php echo e(number_format($stats['total'])); ?></div>
                <div class="text-xs text-ink-500">Total Rows</div>
            </div>
            <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-center">
                <div class="text-2xl font-bold text-green-700"><?php echo e(number_format($stats['total'] - $stats['errors'] - $stats['duplicates'])); ?></div>
                <div class="text-xs text-green-600">Valid</div>
            </div>
            <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-4 text-center">
                <div class="text-2xl font-bold text-yellow-700"><?php echo e(number_format($stats['duplicates'])); ?></div>
                <div class="text-xs text-yellow-600">Duplicates</div>
            </div>
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-center">
                <div class="text-2xl font-bold text-red-700"><?php echo e(number_format($stats['errors'])); ?></div>
                <div class="text-xs text-red-600">Errors</div>
            </div>
        </div>

        <?php if($stats['errors'] > 0): ?>
        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
            <h3 class="text-sm font-semibold text-red-800">Validation Errors Found</h3>
            <div class="mt-2 max-h-40 overflow-y-auto text-xs text-red-700">
                <?php $__currentLoopData = $validation['errors']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rowIndex => $errs): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(!empty($errs)): ?>
                        <div class="mb-1"><strong>Row <?php echo e($rowIndex + 1); ?>:</strong> <?php echo e(implode(', ', $errs)); ?></div>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="rounded-xl border border-ink-200 bg-white p-6">
            <h2 class="mb-4 text-lg font-semibold text-ink-900">Data Preview (first 50 rows)</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-ink-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-ink-500">#</th>
                            <?php $__currentLoopData = $mappings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if(!empty($m['field'])): ?>
                                <th class="px-3 py-2 text-left text-xs font-medium text-ink-500"><?php echo e(ucfirst(str_replace('_', ' ', $m['field']))); ?></th>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <th class="px-3 py-2 text-left text-xs font-medium text-ink-500">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100">
                        <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-ink-50">
                            <td class="px-3 py-2 text-xs text-ink-500"><?php echo e($i + 1); ?></td>
                            <?php $__currentLoopData = $mappings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if(!empty($m['field'])): ?>
                                <td class="px-3 py-2 text-xs text-ink-700"><?php echo e($row[$m['column']] ?? ''); ?></td>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <td class="px-3 py-2">
                                <?php $rowErrors = $validation['errors'][$i] ?? []; ?>
                                <?php if(!empty($rowErrors)): ?>
                                    <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800">Error</span>
                                <?php elseif(!empty($duplicates[$i])): ?>
                                    <span class="rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-800">Duplicate</span>
                                <?php else: ?>
                                    <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">Valid</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-xl border border-ink-200 bg-white p-6">
            <h3 class="mb-3 text-sm font-semibold text-ink-900">Duplicate Strategy</h3>
            <p class="mb-3 text-xs text-ink-500">Choose how to handle <?php echo e($stats['duplicates']); ?> duplicate records:</p>
            <div class="flex gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="duplicate_strategy" value="skip" checked class="text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm text-ink-700">Skip duplicates</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="duplicate_strategy" value="update" class="text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm text-ink-700">Update existing records</span>
                </label>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <a href="<?php echo e(route('admin.imports.show', $import)); ?>" class="btn-secondary">Back to Mapping</a>
            <form action="<?php echo e(route('admin.imports.execute')); ?>" method="POST" onsubmit="return confirm('Import <?php echo e(number_format($stats['total'])); ?> records into <?php echo e($import->getDestinationLabel()); ?>?')">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="import_id" value="<?php echo e($import->id); ?>">
                <input type="hidden" name="duplicate_strategy" id="dup-strategy" value="skip">
                <button type="submit" class="rounded-lg bg-green-600 px-6 py-3 text-sm font-medium text-white hover:bg-green-700">
                    Confirm &amp; Import <?php echo e(number_format($stats['total'])); ?> Records
                </button>
            </form>
        </div>
    </div>

    <script>
    document.querySelectorAll('input[name="duplicate_strategy"]').forEach(r => {
        r.addEventListener('change', () => document.getElementById('dup-strategy').value = r.value);
    });
    </script>

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
<?php /**PATH C:\MY_ERP\resources\views\admin\imports\preview.blade.php ENDPATH**/ ?>