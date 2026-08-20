<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'RFQ — '.($rfq->rfq_number ?? 'New'),'breadcrumbs' => [['label' => 'Procurement', 'url' => route('procurement.rfqs.index')], ['label' => 'RFQs', 'url' => route('procurement.rfqs.index')], ['label' => $rfq->rfq_number]]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('RFQ — '.($rfq->rfq_number ?? 'New')),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Procurement', 'url' => route('procurement.rfqs.index')], ['label' => 'RFQs', 'url' => route('procurement.rfqs.index')], ['label' => $rfq->rfq_number]])]); ?>

     <?php $__env->slot('actions', null, []); ?> 
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $rfq)): ?>
            <a href="<?php echo e(route('procurement.rfqs.edit', $rfq)); ?>" class="btn-secondary">Edit</a>
        <?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $rfq)): ?>
            <form method="POST" action="<?php echo e(route('procurement.rfqs.destroy', $rfq)); ?>" onsubmit="return confirm('Delete this RFQ?')">
                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                <button type="submit" class="btn-secondary text-red-600 hover:bg-red-50">Delete</button>
            </form>
        <?php endif; ?>
     <?php $__env->endSlot(); ?>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6">
            <div class="card">
                <h2 class="mb-3 text-lg font-bold text-ink-900">RFQ Summary</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-ink-400">Number</dt><dd><?php echo e($rfq->rfq_number); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Title</dt><dd class="text-right"><?php echo e($rfq->title); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Issued</dt><dd><?php echo e($rfq->issued_at?->format('d M Y') ?? '—'); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Closes</dt><dd><?php echo e($rfq->closes_at?->format('d M Y') ?? '—'); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Status</dt>
                        <dd><?php $sc = ['draft' => 'gray', 'sent' => 'blue', 'open' => 'amber', 'awarded' => 'green', 'cancelled' => 'red']; ?>
                            <span class="badge-<?php echo e($sc[$rfq->status] ?? 'gray'); ?>"><?php echo e(\App\Models\Rfq::STATUSES[$rfq->status] ?? $rfq->status); ?></span></dd>
                    </div>
                    <div class="flex justify-between border-t border-ink-100 pt-2"><dt class="font-semibold text-ink-700">Items</dt><dd class="font-bold text-ink-900"><?php echo e($rfq->items->count()); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Responses</dt><dd><?php echo e($rfq->responses->count()); ?></dd></div>
                </dl>
            </div>

            <?php if($rfq->description): ?>
                <div class="card"><h3 class="text-sm font-bold uppercase tracking-wide text-ink-400">Description</h3><p class="mt-1 text-sm whitespace-pre-line text-ink-700"><?php echo e($rfq->description); ?></p></div>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\PurchaseOrder::class)): ?>
                <?php if($rfq->status !== 'cancelled' && $rfq->responses->count()): ?>
                    <div class="card">
                        <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400">Create Purchase Order</h3>
                        <form method="POST" action="<?php echo e(route('procurement.rfqs.create-order', $rfq)); ?>" class="space-y-3">
                            <?php echo csrf_field(); ?>
                            <?php if (isset($component)) { $__componentOriginal8cee41e4af1fe2df52d1d5acd06eed36 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8cee41e4af1fe2df52d1d5acd06eed36 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.select','data' => ['label' => 'Award Supplier','name' => 'supplier_id','options' => $rfq->responses->mapWithKeys(fn ($r) => [$r->supplier_id => $r->supplier->name]),'required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Award Supplier','name' => 'supplier_id','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($rfq->responses->mapWithKeys(fn ($r) => [$r->supplier_id => $r->supplier->name])),'required' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8cee41e4af1fe2df52d1d5acd06eed36)): ?>
<?php $attributes = $__attributesOriginal8cee41e4af1fe2df52d1d5acd06eed36; ?>
<?php unset($__attributesOriginal8cee41e4af1fe2df52d1d5acd06eed36); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8cee41e4af1fe2df52d1d5acd06eed36)): ?>
<?php $component = $__componentOriginal8cee41e4af1fe2df52d1d5acd06eed36; ?>
<?php unset($__componentOriginal8cee41e4af1fe2df52d1d5acd06eed36); ?>
<?php endif; ?>
                            <?php if (isset($component)) { $__componentOriginal5c2a97ab476b69c1189ee85d1a95204b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c2a97ab476b69c1189ee85d1a95204b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.input','data' => ['label' => 'PO Number (optional)','name' => 'po_number','placeholder' => 'Leave blank to auto-generate']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'PO Number (optional)','name' => 'po_number','placeholder' => 'Leave blank to auto-generate']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c2a97ab476b69c1189ee85d1a95204b)): ?>
<?php $attributes = $__attributesOriginal5c2a97ab476b69c1189ee85d1a95204b; ?>
<?php unset($__attributesOriginal5c2a97ab476b69c1189ee85d1a95204b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c2a97ab476b69c1189ee85d1a95204b)): ?>
<?php $component = $__componentOriginal5c2a97ab476b69c1189ee85d1a95204b; ?>
<?php unset($__componentOriginal5c2a97ab476b69c1189ee85d1a95204b); ?>
<?php endif; ?>
                            <button type="submit" class="btn-accent w-full">Award &amp; Create Order</button>
                        </form>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="space-y-6 lg:col-span-2">
            <div class="card">
                <h2 class="mb-3 text-lg font-bold text-ink-900">Line Items</h2>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>#</th><th>Product</th><th>Description</th><th class="text-right">Qty</th><th>Unit</th></tr></thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $rfq->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="text-ink-400"><?php echo e($loop->iteration); ?></td>
                                    <td><?php echo e($item->product?->name ?? '—'); ?></td>
                                    <td class="text-ink-700"><?php echo e($item->description); ?></td>
                                    <td class="text-right font-semibold text-ink-800"><?php echo e(number_format((float) $item->quantity, 4)); ?></td>
                                    <td><?php echo e($item->unit ?? '—'); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="5" class="py-6 text-center text-ink-400">No items.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <h2 class="mb-3 text-lg font-bold text-ink-900">Supplier Responses</h2>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>Supplier</th><th class="text-right">Amount</th><th>Delivery</th><th>Valid Until</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $rfq->responses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $response): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="font-semibold text-ink-800"><?php echo e($response->supplier?->name ?? '—'); ?></td>
                                    <td class="text-right"><?php echo e($response->amount !== null ? number_format((float) $response->amount, 2) : '—'); ?></td>
                                    <td><?php echo e($response->delivery_time_days !== null ? $response->delivery_time_days.' days' : '—'); ?></td>
                                    <td><?php echo e($response->valid_until?->format('d M Y') ?? '—'); ?></td>
                                    <td><?php $rc = ['submitted' => 'blue', 'awarded' => 'green', 'rejected' => 'red']; ?>
                                        <span class="badge-<?php echo e($rc[$response->status] ?? 'gray'); ?>"><?php echo e(\App\Models\RfqResponse::STATUSES[$response->status] ?? $response->status); ?></span></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="5" class="py-6 text-center text-ink-400">No responses yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
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
<?php /**PATH C:\MY_ERP\resources\views\procurement\rfqs\show.blade.php ENDPATH**/ ?>