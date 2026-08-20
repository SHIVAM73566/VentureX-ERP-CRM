<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Approval: ' . $request->title]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Approval: ' . $request->title)]); ?>
    <div class="max-w-3xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e($request->title); ?></h1>
            <a href="<?php echo e(route('admin.approvals.index')); ?>" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">← Back to approvals</a>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="font-medium text-gray-500 dark:text-gray-400">Type:</span>
                    <span class="ml-2 text-gray-900 dark:text-white"><?php echo e(ucfirst(str_replace('_', ' ', $request->request_type))); ?></span>
                </div>
                <div>
                    <span class="font-medium text-gray-500 dark:text-gray-400">Risk Level:</span>
                    <?php
                        $riskColors = [
                            'low' => 'bg-green-100 text-green-800',
                            'medium' => 'bg-yellow-100 text-yellow-800',
                            'high' => 'bg-orange-100 text-orange-800',
                            'critical' => 'bg-red-100 text-red-800',
                        ];
                    ?>
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium <?php echo e($riskColors[$request->risk_level] ?? ''); ?>">
                        <?php echo e(ucfirst($request->risk_level)); ?>

                    </span>
                </div>
                <div>
                    <span class="font-medium text-gray-500 dark:text-gray-400">Requested By:</span>
                    <span class="ml-2 text-gray-900 dark:text-white"><?php echo e($request->requester?->name ?? 'Unknown'); ?></span>
                </div>
                <div>
                    <span class="font-medium text-gray-500 dark:text-gray-400">Date:</span>
                    <span class="ml-2 text-gray-900 dark:text-white"><?php echo e($request->created_at->format('M d, Y H:i')); ?></span>
                </div>
                <div>
                    <span class="font-medium text-gray-500 dark:text-gray-400">Status:</span>
                    <?php
                        $statusColors = [
                            'pending' => 'bg-amber-100 text-amber-800',
                            'approved' => 'bg-green-100 text-green-800',
                            'rejected' => 'bg-red-100 text-red-800',
                            'expired' => 'bg-gray-100 text-gray-800',
                            'cancelled' => 'bg-gray-100 text-gray-800',
                        ];
                    ?>
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium <?php echo e($statusColors[$request->status] ?? ''); ?>">
                        <?php echo e(ucfirst($request->status)); ?>

                    </span>
                </div>
                <div>
                    <span class="font-medium text-gray-500 dark:text-gray-400">Approvals:</span>
                    <span class="ml-2 text-gray-900 dark:text-white"><?php echo e($request->current_approvals); ?> / <?php echo e($request->required_approvals); ?></span>
                </div>
            </div>

            <?php if($request->description): ?>
                <div>
                    <span class="font-medium text-gray-500 dark:text-gray-400 text-sm">Description:</span>
                    <p class="mt-1 text-gray-900 dark:text-white text-sm"><?php echo e($request->description); ?></p>
                </div>
            <?php endif; ?>

            <?php if($request->metadata): ?>
                <div>
                    <span class="font-medium text-gray-500 dark:text-gray-400 text-sm">Details:</span>
                    <pre class="mt-1 p-3 bg-gray-50 dark:bg-gray-900 rounded text-xs text-gray-800 dark:text-gray-200 overflow-x-auto"><?php echo e(json_encode($request->metadata, JSON_PRETTY_PRINT)); ?></pre>
                </div>
            <?php endif; ?>

            <?php if($request->status === 'pending'): ?>
                <div class="flex space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <?php if((int) $request->requester_id !== (int) auth()->id()): ?>
                        <form action="<?php echo e(route('admin.approvals.approve', $request)); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">Approve</button>
                        </form>
                        <form action="<?php echo e(route('admin.approvals.reject', $request)); ?>" method="POST" class="flex space-x-2">
                            <?php echo csrf_field(); ?>
                            <input type="text" name="comment" placeholder="Rejection reason (optional)" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 dark:text-white">
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium" onclick="return confirm('Reject this request?')">Reject</button>
                        </form>
                    <?php endif; ?>
                    <?php if((int) $request->requester_id === (int) auth()->id()): ?>
                        <form action="<?php echo e(route('admin.approvals.cancel', $request)); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-sm font-medium" onclick="return confirm('Cancel this request?')">Cancel Request</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if($request->actions->isNotEmpty()): ?>
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Approval History</h2>
                <div class="space-y-3">
                    <?php $__currentLoopData = $request->actions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-start space-x-3 p-3 bg-gray-50 dark:bg-gray-900 rounded-lg">
                            <div class="flex-shrink-0">
                                <?php if($action->action === 'approve'): ?>
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-100 text-green-600">✓</span>
                                <?php else: ?>
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-100 text-red-600">✗</span>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm text-gray-900 dark:text-white">
                                    <span class="font-medium"><?php echo e($action->approver?->name ?? 'Unknown'); ?></span>
                                    <?php echo e($action->action === 'approve' ? 'approved' : 'rejected'); ?> this request
                                </p>
                                <?php if($action->comment): ?>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400"><?php echo e($action->comment); ?></p>
                                <?php endif; ?>
                                <p class="mt-1 text-xs text-gray-400"><?php echo e($action->created_at->diffForHumans()); ?></p>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
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
<?php /**PATH C:\MY_ERP\resources\views\admin\approvals\show.blade.php ENDPATH**/ ?>