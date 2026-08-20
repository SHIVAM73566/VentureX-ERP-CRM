<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Purchase Order — '.($order->po_number ?? 'New'),'breadcrumbs' => [['label' => 'Procurement', 'url' => route('procurement.orders.index')], ['label' => 'Orders', 'url' => route('procurement.orders.index')], ['label' => $order->po_number]]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Purchase Order — '.($order->po_number ?? 'New')),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Procurement', 'url' => route('procurement.orders.index')], ['label' => 'Orders', 'url' => route('procurement.orders.index')], ['label' => $order->po_number]])]); ?>

     <?php $__env->slot('actions', null, []); ?> 
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $order)): ?>
            <a href="<?php echo e(route('procurement.orders.edit', $order)); ?>" class="btn-secondary">Edit</a>
        <?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $order)): ?>
            <form method="POST" action="<?php echo e(route('procurement.orders.destroy', $order)); ?>" onsubmit="return confirm('Delete this purchase order?')">
                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                <button type="submit" class="btn-secondary text-red-600 hover:bg-red-50">Delete</button>
            </form>
        <?php endif; ?>
     <?php $__env->endSlot(); ?>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6">
            <div class="card">
                <h2 class="mb-3 text-lg font-bold text-ink-900">Order Summary</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-ink-400">Number</dt><dd><?php echo e($order->po_number); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Supplier</dt><dd class="text-right"><?php echo e($order->supplier?->name ?? '—'); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Source RFQ</dt>
                        <dd><?php if($order->rfq): ?><a href="<?php echo e(route('procurement.rfqs.show', $order->rfq)); ?>" class="text-navy-600 hover:text-navy-500"><?php echo e($order->rfq->rfq_number); ?></a><?php else: ?> — <?php endif; ?></dd>
                    </div>
                    <div class="flex justify-between"><dt class="text-ink-400">Order Date</dt><dd><?php echo e($order->order_date?->format('d M Y')); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Expected</dt><dd><?php echo e($order->expected_date?->format('d M Y') ?? '—'); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Status</dt>
                        <dd><?php $sc = ['draft' => 'gray', 'pending' => 'amber', 'approved' => 'blue', 'ordered' => 'violet', 'partially_received' => 'amber', 'received' => 'green', 'cancelled' => 'red']; ?>
                            <span class="badge-<?php echo e($sc[$order->status] ?? 'gray'); ?>"><?php echo e(\App\Models\PurchaseOrder::STATUSES[$order->status] ?? $order->status); ?></span></dd>
                    </div>
                    <div class="flex justify-between"><dt class="text-ink-400">Payment</dt><dd><span class="badge-<?php echo e($order->payment_status === 'paid' ? 'green' : ($order->payment_status === 'partial' ? 'amber' : 'gray')); ?>"><?php echo e(\App\Models\PurchaseOrder::PAYMENT_STATUSES[$order->payment_status] ?? $order->payment_status); ?></span></dd></div>
                    <div class="flex justify-between border-t border-ink-100 pt-2"><dt class="font-semibold text-ink-700">Subtotal</dt><dd><?php echo e(number_format((float) $order->subtotal, 2)); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Discount</dt><dd><?php echo e(number_format((float) $order->discount, 2)); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Tax</dt><dd><?php echo e(number_format((float) $order->tax, 2)); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Shipping</dt><dd><?php echo e(number_format((float) $order->shipping, 2)); ?></dd></div>
                    <div class="flex justify-between border-t border-ink-100 pt-2"><dt class="font-bold text-ink-900">Total</dt><dd class="font-bold text-ink-900"><?php echo e(number_format((float) $order->total, 2)); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Paid</dt><dd class="font-semibold text-emerald-600"><?php echo e(number_format((float) $order->paid_amount, 2)); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Outstanding</dt><dd class="font-semibold <?php echo e((float) $order->outstandingBalance() > 0 ? 'text-red-600' : 'text-emerald-600'); ?>"><?php echo e(number_format((float) $order->outstandingBalance(), 2)); ?></dd></div>
                </dl>
            </div>

            <?php if($order->payment_terms || $order->notes): ?>
                <div class="card space-y-3">
                    <?php if($order->payment_terms): ?><div><h3 class="text-sm font-bold uppercase tracking-wide text-ink-400">Payment Terms</h3><p class="mt-1 text-sm text-ink-700"><?php echo e($order->payment_terms); ?></p></div><?php endif; ?>
                    <?php if($order->notes): ?><div><h3 class="text-sm font-bold uppercase tracking-wide text-ink-400">Notes</h3><p class="mt-1 text-sm whitespace-pre-line text-ink-700"><?php echo e($order->notes); ?></p></div><?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="card lg:col-span-2">
            <h2 class="mb-3 text-lg font-bold text-ink-900">Line Items</h2>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead><tr><th>Item</th><th class="text-right">Qty</th><th>Unit</th><th class="text-right">Unit Price</th><th class="text-right">Tax</th><th class="text-right">Line Total</th></tr></thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><p class="font-medium text-ink-800"><?php echo e($item->description); ?></p><?php if($item->product): ?><p class="text-xs text-ink-400"><?php echo e($item->product->sku ?? ''); ?> <?php echo e($item->product->name); ?></p><?php endif; ?></td>
                                <td class="text-right"><?php echo e(number_format((float) $item->quantity, 4)); ?></td>
                                <td><?php echo e($item->unit ?? '—'); ?></td>
                                <td class="text-right"><?php echo e(number_format((float) $item->unit_price, 2)); ?></td>
                                <td class="text-right"><?php echo e((float) $item->tax_rate); ?>%</td>
                                <td class="text-right font-semibold text-ink-800"><?php echo e(number_format((float) $item->line_total, 2)); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="6" class="py-6 text-center text-ink-400">No items.</td></tr>
                        <?php endif; ?>
                        <tr class="border-t-2 border-ink-200">
                            <td colspan="5" class="text-right font-bold text-ink-900">Total</td>
                            <td class="text-right font-bold text-ink-900"><?php echo e(number_format((float) $order->total, 2)); ?></td>
                        </tr>
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
<?php /**PATH C:\MY_ERP\resources\views\procurement\orders\show.blade.php ENDPATH**/ ?>