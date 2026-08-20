<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Product Updates','breadcrumbs' => [['label' => 'Administration', 'url' => route('admin.control-center.index')], ['label' => 'Control Center', 'url' => route('admin.control-center.index')], ['label' => 'Product Updates']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Product Updates'),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Administration', 'url' => route('admin.control-center.index')], ['label' => 'Control Center', 'url' => route('admin.control-center.index')], ['label' => 'Product Updates']])]); ?>

    <?php
        $typeColor = fn ($type) => match($type) {
            'patch' => 'badge-gray',
            'minor' => 'badge-blue',
            'major' => 'badge-violet',
            default => 'badge-gray',
        };
    ?>

    <div class="mx-auto max-w-5xl space-y-6" x-data="{ showForm: false }">

        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-ink-500">Manage product versions and update notifications sent to customer installations.</p>
            </div>
            <button @click="showForm = !showForm" class="btn-primary">
                <span x-show="!showForm">New Update</span>
                <span x-show="showForm">Cancel</span>
            </button>
        </div>

        <div x-show="showForm" x-cloak x-transition>
            <form method="POST" action="<?php echo e(route('admin.control-center.updates.store')); ?>" class="card">
                <?php echo csrf_field(); ?>
                <h3 class="mb-4 text-sm font-bold uppercase tracking-wide text-ink-400">Create Product Update</h3>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="version" class="label">Version Number</label>
                        <input id="version" name="version" class="input font-mono" required placeholder="e.g. 2.4.0" maxlength="32">
                    </div>
                    <div>
                        <label for="type" class="label">Update Type</label>
                        <select id="type" name="type" class="input" required>
                            <option value="patch">Patch</option>
                            <option value="minor">Minor</option>
                            <option value="major">Major</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="title" class="label">Title</label>
                        <input id="title" name="title" class="input" required maxlength="255" placeholder="Release title">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="description" class="label">Description</label>
                        <textarea id="description" name="description" class="input" rows="3" required placeholder="High-level summary of this update..."></textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="changelog" class="label">Changelog</label>
                        <textarea id="changelog" name="changelog" class="input font-mono text-xs" rows="6" placeholder="- Added feature X&#10;- Fixed bug Y&#10;- Improved performance of Z"></textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="mandatory" value="1" class="rounded border-ink-300 text-navy-600 focus:ring-navy-500">
                            <span class="text-sm text-ink-700">Mandatory update (customers must update)</span>
                        </label>
                    </div>
                </div>

                <?php $__errorArgs = ['version'];
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
                    <button type="submit" class="btn-accent">Publish Update</button>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="text-sm font-bold text-ink-800">Existing Updates</h2>
                <span class="text-xs text-ink-400"><?php echo e($updates->count()); ?> total</span>
            </div>

            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Version</th>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Mandatory</th>
                            <th>Published</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $updates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $update): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="font-mono text-xs font-medium text-ink-800"><?php echo e($update->version); ?></td>
                                <td class="font-medium text-ink-800"><?php echo e(Str::limit($update->title, 40)); ?></td>
                                <td><span class="<?php echo e($typeColor($update->type)); ?>"><?php echo e(ucfirst($update->type)); ?></span></td>
                                <td>
                                    <?php if($update->mandatory): ?>
                                        <span class="badge badge-red">Required</span>
                                    <?php else: ?>
                                        <span class="badge badge-gray">Optional</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-xs text-ink-400"><?php echo e($update->created_at->format('d M Y')); ?></td>
                                <td class="text-right">
                                    <form method="POST" action="<?php echo e(route('admin.control-center.updates.destroy', $update)); ?>" onsubmit="return confirm('Delete this update record?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn-danger px-3 py-1.5 text-xs">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="py-8 text-center text-sm text-ink-400">No product updates yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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
<?php /**PATH C:\MY_ERP\resources\views/admin/control-center/updates.blade.php ENDPATH**/ ?>