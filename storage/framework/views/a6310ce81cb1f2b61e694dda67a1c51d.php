<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Security Events','breadcrumbs' => [['label' => 'Administration', 'url' => route('security.dashboard')], ['label' => 'Security Events']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Security Events'),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Administration', 'url' => route('security.dashboard')], ['label' => 'Security Events']])]); ?>

    <div class="mx-auto max-w-5xl space-y-6">
        <div class="card">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-ink-900">Security Events</h2>
                    <p class="text-sm text-ink-500">Filter and review security-related events across your organization.</p>
                </div>
                <a href="<?php echo e(route('security.dashboard')); ?>" class="btn btn-secondary btn-sm">Back to Dashboard</a>
            </div>

            <form method="GET" class="mb-4 flex flex-wrap gap-3">
                <select name="severity" class="input input-sm w-40">
                    <option value="">All severities</option>
                    <option value="info" <?php echo e(request('severity') === 'info' ? 'selected' : ''); ?>>Info</option>
                    <option value="warning" <?php echo e(request('severity') === 'warning' ? 'selected' : ''); ?>>Warning</option>
                    <option value="high" <?php echo e(request('severity') === 'high' ? 'selected' : ''); ?>>High</option>
                    <option value="critical" <?php echo e(request('severity') === 'critical' ? 'selected' : ''); ?>>Critical</option>
                </select>
                <input type="text" name="event" value="<?php echo e(request('event')); ?>" placeholder="Event type..." class="input input-sm w-48">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            </form>

            <?php if($events->isEmpty()): ?>
                <p class="py-8 text-center text-sm text-ink-400">No security events found.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Event</th>
                                <th>Severity</th>
                                <th>User</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="text-xs text-ink-500"><?php echo e($event->created_at->diffForHumans()); ?></td>
                                    <td class="text-sm font-medium text-ink-900"><?php echo e($event->event); ?></td>
                                    <td>
                                        <?php
                                            $colors = ['info' => 'bg-blue-100 text-blue-700', 'warning' => 'bg-yellow-100 text-yellow-700', 'high' => 'bg-orange-100 text-orange-700', 'critical' => 'bg-red-100 text-red-700'];
                                        ?>
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium <?php echo e($colors[$event->severity] ?? 'bg-ink-100 text-ink-600'); ?>">
                                            <?php echo e($event->severity); ?>

                                        </span>
                                    </td>
                                    <td class="text-sm text-ink-600"><?php echo e($event->user?->name ?? 'System'); ?></td>
                                    <td class="max-w-xs truncate text-xs text-ink-500"><?php echo e($event->details); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                <?php echo e($events->links()); ?>

            <?php endif; ?>
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
<?php /**PATH C:\MY_ERP\resources\views\admin\security\events.blade.php ENDPATH**/ ?>