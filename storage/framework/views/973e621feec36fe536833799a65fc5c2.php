<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Approvals','breadcrumbs' => [['label' => 'Admin'], ['label' => 'Approvals']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Approvals'),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Admin'], ['label' => 'Approvals']])]); ?>
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h1 class="text-2xl font-bold text-ink-800">Approval Center</h1>
            <?php if($pendingCount > 0): ?>
                <span class="badge-amber"><?php echo e($pendingCount); ?> pending</span>
            <?php endif; ?>
        </div>

        <div class="flex space-x-1 border-b border-ink-200">
            <?php
                $tabs = [
                    'pending' => 'Pending Approval',
                    'my-requests' => 'My Requests',
                    'all' => 'All Requests',
                ];
            ?>
            <?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('admin.approvals.index', ['tab' => $key])); ?>"
                   class="px-4 py-2 text-sm font-medium border-b-2 transition-colors
                          <?php echo e($tab === $key
                              ? 'border-blue-500 text-blue-600'
                              : 'border-transparent text-ink-500 hover:text-ink-700'); ?>">
                    <?php echo e($label); ?>

                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <?php if($requests->isEmpty()): ?>
            <div class="text-center py-12 text-ink-400">
                <svg class="mx-auto h-12 w-12 text-ink-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="mt-2 text-sm">No <?php echo e($tab === 'pending' ? 'pending approvals' : 'requests'); ?> found.</p>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Request</th>
                                <th>Requested By</th>
                                <th>Risk</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $req): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="text-sm text-ink-800">
                                        <?php echo e(ucfirst(str_replace('_', ' ', $req->request_type))); ?>

                                    </td>
                                    <td class="text-sm text-ink-800 max-w-xs truncate">
                                        <?php echo e($req->title); ?>

                                    </td>
                                    <td class="text-sm text-ink-500">
                                        <?php echo e($req->requester?->name ?? 'Unknown'); ?>

                                    </td>
                                    <td class="text-sm">
                                        <?php
                                            $riskBadge = [
                                                'low' => 'badge-green',
                                                'medium' => 'badge-amber',
                                                'high' => 'badge-orange',
                                                'critical' => 'badge-red',
                                            ];
                                        ?>
                                        <span class="<?php echo e($riskBadge[$req->risk_level] ?? 'badge-green'); ?>">
                                            <?php echo e(ucfirst($req->risk_level)); ?>

                                        </span>
                                    </td>
                                    <td class="text-sm">
                                        <?php
                                            $statusBadge = [
                                                'pending' => 'badge-amber',
                                                'approved' => 'badge-green',
                                                'rejected' => 'badge-red',
                                                'expired' => 'badge-gray',
                                                'cancelled' => 'badge-gray',
                                            ];
                                        ?>
                                        <span class="<?php echo e($statusBadge[$req->status] ?? 'badge-gray'); ?>">
                                            <?php echo e(ucfirst($req->status)); ?>

                                        </span>
                                    </td>
                                    <td class="text-sm text-ink-500">
                                        <?php echo e($req->created_at->diffForHumans()); ?>

                                    </td>
                                    <td class="text-sm text-right space-x-2">
                                        <a href="<?php echo e(route('admin.approvals.show', $req)); ?>"
                                           class="inline-flex items-center px-3 py-2 text-sm rounded-lg text-navy-600 hover:text-navy-500">
                                            View
                                        </a>
                                        <?php if($req->status === 'pending' && (int) $req->requester_id !== (int) auth()->id()): ?>
                                            <form action="<?php echo e(route('admin.approvals.approve', $req)); ?>" method="POST" class="inline">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit"
                                                    class="inline-flex items-center px-3 py-2 text-sm rounded-lg text-green-600 hover:text-green-800 hover:bg-green-50">
                                                    Approve
                                                </button>
                                            </form>
                                            <form action="<?php echo e(route('admin.approvals.reject', $req)); ?>" method="POST" class="inline">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit"
                                                    class="inline-flex items-center px-3 py-2 text-sm rounded-lg text-red-600 hover:text-red-800 hover:bg-red-50"
                                                    onclick="return confirm('Reject this request?')">
                                                    Reject
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if($req->status === 'pending' && (int) $req->requester_id === (int) auth()->id()): ?>
                                            <form action="<?php echo e(route('admin.approvals.cancel', $req)); ?>" method="POST" class="inline">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit"
                                                    class="inline-flex items-center px-3 py-2 text-sm rounded-lg text-ink-600 hover:text-ink-800 hover:bg-ink-50"
                                                    onclick="return confirm('Cancel this request?')">
                                                    Cancel
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 border-t border-ink-200">
                    <?php echo e($requests->links()); ?>

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
<?php /**PATH C:\MY_ERP\resources\views/admin/approvals/index.blade.php ENDPATH**/ ?>