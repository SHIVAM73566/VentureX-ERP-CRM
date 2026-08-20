<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Requisition — '.($requisition->pr_number ?? 'New'),'breadcrumbs' => [['label' => 'Procurement', 'url' => route('procurement.requisitions.index')], ['label' => 'Requisitions', 'url' => route('procurement.requisitions.index')], ['label' => $requisition->pr_number]]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Requisition — '.($requisition->pr_number ?? 'New')),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Procurement', 'url' => route('procurement.requisitions.index')], ['label' => 'Requisitions', 'url' => route('procurement.requisitions.index')], ['label' => $requisition->pr_number]])]); ?>

     <?php $__env->slot('actions', null, []); ?> 
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $requisition)): ?>
            <a href="<?php echo e(route('procurement.requisitions.edit', $requisition)); ?>" class="btn-secondary">Edit</a>
        <?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $requisition)): ?>
            <form method="POST" action="<?php echo e(route('procurement.requisitions.destroy', $requisition)); ?>" onsubmit="return confirm('Delete this requisition?')">
                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                <button type="submit" class="btn-secondary text-red-600 hover:bg-red-50">Delete</button>
            </form>
        <?php endif; ?>
     <?php $__env->endSlot(); ?>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6">
            <div class="card">
                <h2 class="mb-3 text-lg font-bold text-ink-900">Requisition Summary</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-ink-400">Number</dt><dd><?php echo e($requisition->pr_number); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Department</dt><dd><?php echo e($requisition->department?->name ?? '—'); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Requester</dt><dd><?php echo e($requisition->requester?->name ?? '—'); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Required</dt><dd><?php echo e($requisition->required_date?->format('d M Y') ?? '—'); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Priority</dt>
                        <dd><?php $pc = ['low' => 'gray', 'medium' => 'blue', 'high' => 'amber', 'critical' => 'red']; ?>
                            <span class="badge-<?php echo e($pc[$requisition->priority] ?? 'gray'); ?>"><?php echo e(\App\Models\PurchaseRequisition::PRIORITIES[$requisition->priority] ?? $requisition->priority); ?></span></dd>
                    </div>
                    <div class="flex justify-between"><dt class="text-ink-400">Status</dt>
                        <dd><?php $sc = ['draft' => 'gray', 'pending_approval' => 'amber', 'approved' => 'green', 'rejected' => 'red', 'ordered' => 'violet']; ?>
                            <span class="badge-<?php echo e($sc[$requisition->status] ?? 'gray'); ?>"><?php echo e(\App\Models\PurchaseRequisition::STATUSES[$requisition->status] ?? $requisition->status); ?></span></dd>
                    </div>
                    <div class="flex justify-between border-t border-ink-100 pt-2"><dt class="font-semibold text-ink-700">Items</dt><dd class="font-bold text-ink-900"><?php echo e($requisition->items->count()); ?></dd></div>
                </dl>
            </div>

            <?php if($requisition->notes): ?>
                <div class="card"><h3 class="text-sm font-bold uppercase tracking-wide text-ink-400">Notes</h3><p class="mt-1 text-sm whitespace-pre-line text-ink-700"><?php echo e($requisition->notes); ?></p></div>
            <?php endif; ?>
        </div>

        <div class="card lg:col-span-2">
            <h2 class="mb-3 text-lg font-bold text-ink-900">Items</h2>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead><tr><th>#</th><th>Product</th><th>Description</th><th class="text-right">Qty</th><th>Unit</th><th>Notes</th></tr></thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $requisition->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="text-ink-400"><?php echo e($loop->iteration); ?></td>
                                <td><?php echo e($item->product?->name ?? '—'); ?></td>
                                <td class="text-ink-700"><?php echo e($item->description); ?></td>
                                <td class="text-right font-semibold text-ink-800"><?php echo e(number_format((float) $item->quantity, 4)); ?></td>
                                <td><?php echo e($item->unit ?? '—'); ?></td>
                                <td class="text-ink-400"><?php echo e($item->notes ?? '—'); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="6" class="py-6 text-center text-ink-400">No items.</td></tr>
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
<?php /**PATH C:\MY_ERP\resources\views\procurement\requisitions\show.blade.php ENDPATH**/ ?>