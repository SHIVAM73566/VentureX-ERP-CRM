<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'AI Usage','breadcrumbs' => [['label' => 'AI Center'], ['label' => 'Usage']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'AI Usage','breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'AI Center'], ['label' => 'Usage']])]); ?>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="card">
            <p class="text-sm text-ink-400">Requests (30 days)</p>
            <p class="text-2xl font-bold text-ink-900"><?php echo e(number_format($stats['requests'])); ?></p>
            <p class="text-xs text-ink-400"><?php echo e(number_format($stats['api_calls'])); ?> real API calls · <?php echo e($stats['cache_hit_rate']); ?>% served from cache</p>
        </div>
        <div class="card">
            <p class="text-sm text-ink-400">Today</p>
            <p class="text-2xl font-bold text-ink-900"><?php echo e(number_format($today)); ?></p>
            <p class="text-xs text-ink-400">AI requests processed</p>
        </div>
        <div class="card">
            <p class="text-sm text-ink-400">Estimated cost (30 days)</p>
            <p class="text-2xl font-bold text-ink-900">$<?php echo e(number_format($stats['estimated_cost'], 4)); ?></p>
            <p class="text-xs text-emerald-600">$<?php echo e(number_format($stats['estimated_savings'], 4)); ?> saved via cache</p>
        </div>
        <div class="card">
            <p class="text-sm text-ink-400">Avg latency</p>
            <p class="text-2xl font-bold text-ink-900"><?php echo e($stats['avg_latency_ms']); ?>ms</p>
            <p class="text-xs text-ink-400"><?php echo e(number_format($stats['errors'])); ?> errors · <?php echo e(number_format($stats['fallbacks'])); ?> fallbacks (30d)</p>
        </div>
    </div>

    <div class="mb-6 card">
        <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400">Provider status & usage (30 days)</h3>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Provider</th>
                        <th>Status</th>
                        <th class="text-right">Requests</th>
                        <th class="text-right">Cache hits</th>
                        <th class="text-right">Actual API calls</th>
                        <th class="text-right">Cache hit rate</th>
                        <th class="text-right">Errors</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $stats['provider_details']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $provider => $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="font-medium text-ink-800 capitalize"><?php echo e($provider); ?></td>
                            <td>
                                <?php if(! ($health[$provider]['configured'] ?? false)): ?>
                                    <span class="badge-gray">Not configured</span>
                                <?php elseif(($health[$provider]['healthy'] ?? true) === false): ?>
                                    <span class="badge-red">Temporarily degraded</span>
                                <?php else: ?>
                                    <span class="badge-green">● Available</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-right"><?php echo e(number_format($detail['requests'])); ?></td>
                            <td class="text-right"><?php echo e(number_format($detail['cache_hits'])); ?></td>
                            <td class="text-right"><?php echo e(number_format($detail['api_calls'])); ?></td>
                            <td class="text-right"><?php echo e($detail['cache_hit_rate']); ?>%</td>
                            <td class="text-right <?php echo e($detail['errors'] > 0 ? 'text-red-600' : ''); ?>"><?php echo e(number_format($detail['errors'])); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="7" class="py-4 text-center text-ink-400">No provider activity recorded yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <p class="mt-2 text-xs text-ink-400">Requests = cache hits + actual API calls. A provider marked "Temporarily degraded" is skipped until it recovers. Users never see this page — providers are only visible to admins here.</p>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="card">
            <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400">By provider (30 days)</h3>
            <?php $__empty_1 = true; $__currentLoopData = $byProvider; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $provider => $total): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="mb-3 flex items-center justify-between text-sm">
                    <span class="capitalize text-ink-700"><?php echo e($provider); ?></span>
                    <span class="font-medium text-ink-900"><?php echo e(number_format($total)); ?></span>
                </div>
                <div class="mb-4 h-2 overflow-hidden rounded-full bg-ink-100">
                    <div class="h-full rounded-full bg-navy-600" style="width: <?php echo e($stats['requests'] > 0 ? round($total / $stats['requests'] * 100) : 0); ?>%"></div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-sm text-ink-400">No API calls recorded yet.</p>
            <?php endif; ?>

            <h3 class="mb-3 mt-6 text-sm font-bold uppercase tracking-wide text-ink-400">By task (30 days)</h3>
            <?php $__empty_1 = true; $__currentLoopData = $stats['tasks']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task => $total): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="mb-2 flex items-center justify-between text-sm">
                    <span class="text-ink-700"><?php echo e($task); ?></span>
                    <span class="font-medium text-ink-900"><?php echo e(number_format($total)); ?>

                        <span class="text-xs font-normal text-ink-400">(<?php echo e(number_format($stats['task_hits'][$task] ?? 0)); ?> cached)</span>
                    </span>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-sm text-ink-400">No task activity yet.</p>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400">By user (30 days)</h3>
            <?php $__empty_1 = true; $__currentLoopData = $byUser; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $total): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="mb-2 flex items-center justify-between text-sm">
                    <span class="text-ink-700"><?php echo e($name); ?></span>
                    <span class="font-medium text-ink-900"><?php echo e(number_format($total)); ?></span>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-sm text-ink-400">No usage yet.</p>
            <?php endif; ?>

            <h3 class="mb-3 mt-6 text-sm font-bold uppercase tracking-wide text-ink-400">Status (30 days)</h3>
            <div class="mb-2 flex items-center justify-between text-sm">
                <span class="text-ink-700">Failed runs</span>
                <span class="font-medium <?php echo e($failed30 > 0 ? 'text-red-600' : 'text-ink-900'); ?>"><?php echo e(number_format($failed30)); ?></span>
            </div>
            <div class="mb-2 flex items-center justify-between text-sm">
                <span class="text-ink-700">Fallbacks used</span>
                <span class="font-medium text-ink-900"><?php echo e(number_format($fallback30)); ?></span>
            </div>
        </div>

        <div class="card">
            <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400">Recent runs</h3>
            <div class="space-y-3">
                <?php $__empty_1 = true; $__currentLoopData = $recent; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $run): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="rounded-lg border border-ink-100 p-3 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-ink-800"><?php echo e($run->user?->name ?? 'System'); ?></span>
                            <span class="text-xs text-ink-400"><?php echo e($run->created_at->diffForHumans()); ?></span>
                        </div>
                        <p class="mt-1 text-xs text-ink-500">Task: <?php echo e($run->input_type); ?> · <?php echo e($run->provider ?? '—'); ?> · <?php echo e($run->model ?? '—'); ?></p>
                        <p class="mt-1 text-xs">
                            <span class="<?php echo e($run->status === 'completed' ? 'text-emerald-600' : ($run->status === 'failed' ? 'text-red-600' : 'text-amber-600')); ?> capitalize"><?php echo e($run->status); ?></span>
                            <span class="text-ink-400"> · $<?php echo e(number_format((float) $run->cost, 6)); ?></span>
                        </p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-sm text-ink-400">No AI runs yet.</p>
                <?php endif; ?>
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
<?php /**PATH C:\MY_ERP\resources\views\ai\usage.blade.php ENDPATH**/ ?>