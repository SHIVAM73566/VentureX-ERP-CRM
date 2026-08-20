<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Document Manager','breadcrumbs' => [['label' => 'Administration'], ['label' => 'Document Manager']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Document Manager'),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Administration'], ['label' => 'Document Manager']])]); ?>

    <div class="mx-auto max-w-5xl space-y-6">
        <form method="POST" action="<?php echo e(route('admin.documents.store')); ?>" enctype="multipart/form-data" class="card">
            <?php echo csrf_field(); ?>
            <h2 class="mb-1 text-lg font-bold text-ink-900">Upload a document</h2>
            <p class="mb-4 text-sm text-ink-500">Files are size-limited, type-checked by content signature, and scanned. Suspicious files are quarantined.</p>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="title" class="label">Title</label>
                    <input id="title" name="title" class="input" required>
                </div>
                <div>
                    <label for="file" class="label">File (PDF, Office, CSV, TXT, images — max <?php echo e(round(config('security.upload.max_size_kb') / 1024, 1)); ?> MB)</label>
                    <input id="file" name="file" type="file" class="input" required>
                </div>
            </div>

            <?php $__errorArgs = ['file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-2 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-2 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

            <div class="mt-4 flex justify-end">
                <button type="submit" class="btn-accent">Upload & scan</button>
            </div>
        </form>

        <div class="card">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h2 class="text-lg font-bold text-ink-900">Documents</h2>
                <form method="GET" action="<?php echo e(route('admin.documents.index')); ?>" class="flex items-center gap-2">
                    <input name="q" value="<?php echo e(request('q')); ?>" placeholder="Search…" class="input">
                    <button type="submit" class="btn-secondary">Search</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-ink-200 text-xs uppercase tracking-wide text-ink-400">
                            <th class="py-2 pr-4">Title</th>
                            <th class="py-2 pr-4">File</th>
                            <th class="py-2 pr-4">Scan</th>
                            <th class="py-2 pr-4">Status</th>
                            <th class="py-2 pr-4">Uploaded</th>
                            <th class="py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $documents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="border-b border-ink-100">
                                <td class="py-2.5 pr-4 font-medium text-ink-800"><?php echo e($document->title); ?></td>
                                <td class="py-2.5 pr-4 text-xs text-ink-500">
                                    <?php echo e($document->original_name); ?><br>
                                    <?php echo e(round($document->size / 1024, 1)); ?> KB · <?php echo e(strtoupper(pathinfo($document->original_name, PATHINFO_EXTENSION))); ?>

                                </td>
                                <td class="py-2.5 pr-4">
                                    <?php if($document->is_quarantined): ?>
                                        <span class="badge badge-red">Quarantined</span>
                                    <?php elseif($document->scan_status === 'clean'): ?>
                                        <span class="badge badge-green">Clean</span>
                                    <?php elseif($document->scan_status === 'error'): ?>
                                        <span class="badge badge-gray">Unscanned</span>
                                    <?php else: ?>
                                        <span class="badge badge-gray"><?php echo e($document->scan_status); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-2.5 pr-4">
                                    <span class="badge <?php echo e($document->status === 'rejected' ? 'badge-red' : 'badge-gray'); ?>"><?php echo e(\App\Models\Document::STATUSES[$document->status] ?? $document->status); ?></span>
                                </td>
                                <td class="py-2.5 pr-4 text-xs text-ink-500"><?php echo e($document->created_at->format('d M Y H:i')); ?></td>
                                <td class="py-2.5">
                                    <div class="flex items-center justify-end gap-2">
                                        <?php if (! ($document->is_quarantined)): ?>
                                            <a href="<?php echo e(route('admin.documents.download', $document)); ?>" class="btn-secondary px-3 py-1.5 text-xs">Download</a>
                                        <?php endif; ?>
                                        <form method="POST" action="<?php echo e(route('admin.documents.destroy', $document)); ?>" onsubmit="return confirm('Delete this document permanently?')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn-danger px-3 py-1.5 text-xs">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="py-8 text-center text-sm text-ink-400">No documents uploaded yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-4"><?php echo e($documents->links()); ?></div>
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
<?php /**PATH C:\MY_ERP\resources\views\admin\documents\index.blade.php ENDPATH**/ ?>