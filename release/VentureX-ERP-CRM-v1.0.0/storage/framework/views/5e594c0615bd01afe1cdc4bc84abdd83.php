<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Map Columns','breadcrumbs' => [['label' => 'Administration', 'url' => route('admin.imports.index')], ['label' => 'Map Columns']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Map Columns'),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Administration', 'url' => route('admin.imports.index')], ['label' => 'Map Columns']])]); ?>

    <div class="mx-auto max-w-6xl space-y-6">
        <div>
            <div class="flex items-center gap-2 text-sm text-ink-500">
                <span>Upload</span>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="font-medium text-indigo-600">Map Columns</span>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span>Preview</span>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span>Import</span>
            </div>
            <h1 class="mt-2 text-2xl font-bold text-ink-900">Map Your Columns</h1>
            <p class="mt-1 text-sm text-ink-500">Match your file columns to the correct ERP fields.</p>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6">
                <form action="<?php echo e(route('admin.imports.mapping.save')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="import_id" value="<?php echo e($import->id); ?>">

                    <div class="rounded-xl border border-ink-200 bg-white p-6">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-ink-900">Column Mapping</h2>
                            <span class="text-xs text-ink-500"><?php echo e(count($columns)); ?> columns detected</span>
                        </div>

                        <div class="space-y-3">
                            <?php $__currentLoopData = $mappings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $mapping): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex items-center gap-3 rounded-lg border border-ink-200 p-3">
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-ink-900"><?php echo e($mapping['column']); ?></div>
                                    <div class="text-xs text-ink-500">Sample: <?php echo e($preview[0][$mapping['column']] ?? '&mdash;'); ?></div>
                                </div>
                                <svg class="h-5 w-5 flex-shrink-0 text-ink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                <select name="mappings[<?php echo e($i); ?>][field]" class="w-48 rounded-lg border border-ink-300 bg-white px-3 py-2 text-sm">
                                    <option value="">&mdash; Ignore &mdash;</option>
                                    <?php $__currentLoopData = $fieldLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($field); ?>" <?php echo e(($mapping['field'] ?? '') === $field ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <input type="hidden" name="mappings[<?php echo e($i); ?>][column]" value="<?php echo e($mapping['column']); ?>">
                                <?php if(($mapping['confidence'] ?? 0) >= 70): ?>
                                    <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800"><?php echo e($mapping['confidence']); ?>%</span>
                                <?php else: ?>
                                    <span class="rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-800">Review</span>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    <div class="rounded-xl border border-ink-200 bg-white p-6">
                        <h3 class="mb-3 text-sm font-medium text-ink-700">Import Settings</h3>
                        <label class="mb-1 block text-xs text-ink-500">Destination Module</label>
                        <select name="destination" class="input">
                            <?php $__currentLoopData = $allDestinations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($key); ?>" <?php echo e($key === $import->destination ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="btn-primary">Continue to Preview</button>
                    </div>
                </form>
            </div>

            <div class="space-y-6">
                <div class="rounded-xl border border-ink-200 bg-white p-6">
                    <h3 class="mb-3 text-sm font-semibold text-ink-900">Auto-Detection</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-ink-500">Suggested module:</span>
                            <span class="font-medium text-ink-900"><?php echo e(ucfirst($detection['destination'])); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-ink-500">Confidence:</span>
                            <span class="font-medium text-green-600"><?php echo e($detection['confidence']); ?>%</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-ink-200 bg-white p-6">
                    <h3 class="mb-3 text-sm font-semibold text-ink-900">File Preview</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-xs">
                            <thead>
                                <tr>
                                    <?php $__currentLoopData = $columns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <th class="whitespace-nowrap px-2 py-1 text-left font-medium text-ink-500"><?php echo e($col); ?></th>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $preview; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="border-t border-ink-100">
                                    <?php $__currentLoopData = $columns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <td class="whitespace-nowrap px-2 py-1 text-ink-600"><?php echo e($row[$col] ?? ''); ?></td>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    <p class="mt-2 text-xs text-ink-400">Showing first <?php echo e(count($preview)); ?> of <?php echo e($import->total_rows); ?> rows</p>
                </div>
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
<?php /**PATH C:\MY_ERP\resources\views/admin/imports/map.blade.php ENDPATH**/ ?>