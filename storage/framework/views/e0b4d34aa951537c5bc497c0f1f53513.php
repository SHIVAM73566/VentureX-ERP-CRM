<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'AI Quotas','breadcrumbs' => [['label' => 'Administration'], ['label' => 'AI Quotas']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('AI Quotas'),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Administration'], ['label' => 'AI Quotas']])]); ?>
    <div class="mx-auto max-w-5xl space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">AI Quota Configuration</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Configure daily and weekly AI request limits per role. Changes take effect immediately.</p>
            </div>
            <div class="text-right text-sm">
                <p class="text-gray-500 dark:text-gray-400">Your quota today</p>
                <p class="font-semibold text-gray-900 dark:text-white"><?php echo e($stats['daily_used']); ?> / <?php echo e($stats['daily_limit']); ?> daily</p>
                <p class="font-semibold text-gray-900 dark:text-white"><?php echo e($stats['weekly_used']); ?> / <?php echo e($stats['weekly_limit']); ?> weekly</p>
            </div>
        </div>

        <?php if($stats['alerts']): ?>
            <div class="rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 p-4">
                <p class="text-sm font-medium text-amber-800 dark:text-amber-200">
                    ⚠️ You have used <?php echo e(max($stats['daily_used'] / max($stats['daily_limit'], 1), $stats['weekly_used'] / max($stats['weekly_limit'], 1)) * 100); ?>% of your AI quota.
                </p>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Requests (7d)</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e(number_format($usageStats['requests'])); ?></p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Cache Hit Rate</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e($usageStats['cache_hit_rate']); ?>%</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">API Calls (7d)</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e(number_format($usageStats['api_calls'])); ?></p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Estimated Cost</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">$<?php echo e(number_format($usageStats['estimated_cost'], 2)); ?></p>
            </div>
        </div>

        <form method="POST" action="<?php echo e(route('admin.ai-quotas.update')); ?>">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Role Quotas</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Daily Limit</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Weekly Limit</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <?php $__currentLoopData = $quotas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role => $quota): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                        <?php echo e(ucwords(str_replace('_', ' ', $role))); ?>

                                    </td>
                                    <td class="px-6 py-4">
                                        <input type="number" name="quotas[<?php echo e($role); ?>][daily]"
                                               value="<?php echo e($quota['daily']); ?>"
                                               min="0" max="9999"
                                               class="w-24 px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                    </td>
                                    <td class="px-6 py-4">
                                        <input type="number" name="quotas[<?php echo e($role); ?>][weekly]"
                                               value="<?php echo e($quota['weekly']); ?>"
                                               min="0" max="9999"
                                               class="w-24 px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <?php if($quota['overridden']): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                Custom
                                            </span>
                                        <?php else: ?>
                                            <span class="text-gray-400 dark:text-gray-500 text-xs">Default</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-4 flex justify-end">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">Save Quota Settings</button>
            </div>
        </form>
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
<?php /**PATH C:\MY_ERP\resources\views/admin/ai-quotas/index.blade.php ENDPATH**/ ?>