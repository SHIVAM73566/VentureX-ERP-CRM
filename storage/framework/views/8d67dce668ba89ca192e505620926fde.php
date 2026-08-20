<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Announcements','breadcrumbs' => [['label' => 'Administration', 'url' => route('admin.control-center.index')], ['label' => 'Control Center', 'url' => route('admin.control-center.index')], ['label' => 'Announcements']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Announcements'),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Administration', 'url' => route('admin.control-center.index')], ['label' => 'Control Center', 'url' => route('admin.control-center.index')], ['label' => 'Announcements']])]); ?>

    <?php
        $typeColor = fn ($type) => match($type) {
            'info' => 'badge-blue',
            'warning' => 'badge-yellow',
            'critical' => 'badge-red',
            'update' => 'badge-violet',
            default => 'badge-gray',
        };

        $statusColor = fn ($status) => match($status) {
            'published' => 'badge-green',
            'draft' => 'badge-gray',
            'expired' => 'badge-amber',
            default => 'badge-gray',
        };
    ?>

    <div class="mx-auto max-w-5xl space-y-6" x-data="{ showForm: false }">

        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-ink-500">Manage platform-wide announcements visible to all customers.</p>
            </div>
            <button @click="showForm = !showForm" class="btn-primary">
                <span x-show="!showForm">New Announcement</span>
                <span x-show="showForm">Cancel</span>
            </button>
        </div>

        <div x-show="showForm" x-cloak x-transition>
            <form method="POST" action="<?php echo e(route('admin.control-center.announcements.store')); ?>" class="card">
                <?php echo csrf_field(); ?>
                <h3 class="mb-4 text-sm font-bold uppercase tracking-wide text-ink-400">Create Announcement</h3>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="title" class="label">Title</label>
                        <input id="title" name="title" class="input" required maxlength="255" placeholder="Announcement title">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="message" class="label">Message</label>
                        <textarea id="message" name="message" class="input" rows="4" required placeholder="Announcement body..."></textarea>
                    </div>
                    <div>
                        <label for="type" class="label">Type</label>
                        <select id="type" name="type" class="input" required>
                            <option value="info">Info</option>
                            <option value="warning">Warning</option>
                            <option value="critical">Critical</option>
                            <option value="update">Update</option>
                        </select>
                    </div>
                    <div>
                        <label for="target_audience" class="label">Target Audience</label>
                        <select id="target_audience" name="target_audience" class="input" required>
                            <option value="all">All Customers</option>
                            <option value="enterprise">Enterprise Only</option>
                            <option value="professional">Professional & Up</option>
                            <option value="trial">Trial Users</option>
                        </select>
                    </div>
                    <div>
                        <label for="publish_at" class="label">Publish Date</label>
                        <input id="publish_at" name="publish_at" type="datetime-local" class="input" value="<?php echo e(now()->format('Y-m-d\TH:i')); ?>">
                    </div>
                    <div>
                        <label for="expires_at" class="label">Expires Date</label>
                        <input id="expires_at" name="expires_at" type="datetime-local" class="input">
                    </div>
                </div>

                <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-2 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                <?php $__errorArgs = ['message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-2 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                <div class="mt-4 flex justify-end">
                    <button type="submit" class="btn-accent">Publish Announcement</button>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="text-sm font-bold text-ink-800">Existing Announcements</h2>
                <span class="text-xs text-ink-400"><?php echo e($announcements->count()); ?> total</span>
            </div>

            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Target</th>
                            <th>Status</th>
                            <th>Published</th>
                            <th>Expires</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $announcements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $announcement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="font-medium text-ink-800"><?php echo e(Str::limit($announcement->title, 40)); ?></td>
                                <td><span class="<?php echo e($typeColor($announcement->type)); ?>"><?php echo e(ucfirst($announcement->type)); ?></span></td>
                                <td class="text-xs text-ink-600"><?php echo e(ucfirst($announcement->target_audience)); ?></td>
                                <td><span class="<?php echo e($statusColor($announcement->status)); ?>"><?php echo e(ucfirst($announcement->status)); ?></span></td>
                                <td class="text-xs text-ink-400"><?php echo e($announcement->publish_at?->format('d M Y H:i') ?? '--'); ?></td>
                                <td class="text-xs text-ink-400"><?php echo e($announcement->expires_at?->format('d M Y H:i') ?? 'Never'); ?></td>
                                <td class="text-right">
                                    <form method="POST" action="<?php echo e(route('admin.control-center.announcements.destroy', $announcement)); ?>" onsubmit="return confirm('Delete this announcement?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn-danger px-3 py-1.5 text-xs">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="py-8 text-center text-sm text-ink-400">No announcements yet.</td>
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
<?php /**PATH C:\MY_ERP\resources\views\admin\control-center\announcements.blade.php ENDPATH**/ ?>