<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Sales Order — '.($order->order_number ?? 'New'),'breadcrumbs' => [['label' => 'Sales', 'url' => route('sales.orders.index')], ['label' => 'Orders', 'url' => route('sales.orders.index')], ['label' => $order->order_number]]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Sales Order — '.($order->order_number ?? 'New')),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Sales', 'url' => route('sales.orders.index')], ['label' => 'Orders', 'url' => route('sales.orders.index')], ['label' => $order->order_number]])]); ?>

     <?php $__env->slot('actions', null, []); ?> 
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Invoice::class)): ?>
            <a href="<?php echo e(route('sales.invoices.create')); ?>?sales_order_id=<?php echo e($order->id); ?>" class="btn-accent">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Invoice
            </a>
        <?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $order)): ?>
            <a href="<?php echo e(route('sales.orders.edit', $order)); ?>" class="btn-secondary">Edit</a>
        <?php endif; ?>
     <?php $__env->endSlot(); ?>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6">
            <div class="card">
                <h2 class="mb-3 text-lg font-bold text-ink-900">Order Summary</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-ink-400">Number</dt><dd><?php echo e($order->order_number); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Customer</dt><dd class="text-right"><?php echo e($order->customer?->name ?? '—'); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Order Date</dt><dd><?php echo e($order->order_date?->format('d M Y')); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Delivery</dt><dd><?php echo e($order->delivery_date?->format('d M Y') ?? '—'); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Status</dt>
                        <dd><?php $c = ['draft' => 'gray', 'confirmed' => 'blue', 'processing' => 'amber', 'shipped' => 'violet', 'completed' => 'green', 'cancelled' => 'red']; ?>
                            <span class="badge-<?php echo e($c[$order->status] ?? 'gray'); ?>"><?php echo e(\App\Models\SalesOrder::STATUSES[$order->status] ?? $order->status); ?></span>
                        </dd>
                    </div>
                    <div class="flex justify-between border-t border-ink-100 pt-2"><dt class="font-semibold text-ink-700">Total</dt><dd class="font-bold text-ink-900"><?php echo e(number_format((float) $order->total, 2)); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Paid</dt><dd class="font-semibold text-emerald-600"><?php echo e(number_format((float) $order->paid_amount, 2)); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Outstanding</dt><dd class="font-semibold <?php echo e((float) $order->outstandingBalance() > 0 ? 'text-red-600' : 'text-emerald-600'); ?>"><?php echo e(number_format((float) $order->outstandingBalance(), 2)); ?></dd></div>
                </dl>
            </div>
        </div>

        <div class="space-y-6 lg:col-span-2">
            <div class="card">
                <h2 class="mb-3 text-lg font-bold text-ink-900">Line Items</h2>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>Item</th><th>Qty</th><th>Unit Price</th><th>Tax</th><th class="text-right">Line Total</th></tr></thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><p class="font-medium text-ink-800"><?php echo e($item->description); ?></p><?php if($item->product): ?><p class="text-xs text-ink-400"><?php echo e($item->product->sku ?? ''); ?> <?php echo e($item->product->name); ?></p><?php endif; ?></td>
                                    <td><?php echo e(number_format((float) $item->quantity, 3)); ?></td>
                                    <td><?php echo e(number_format((float) $item->unit_price, 2)); ?></td>
                                    <td><?php echo e((float) $item->tax_rate); ?>%</td>
                                    <td class="text-right font-semibold text-ink-800"><?php echo e(number_format((float) $item->line_total, 2)); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="5" class="py-6 text-center text-ink-400">No items.</td></tr>
                            <?php endif; ?>
                            <tr class="border-t-2 border-ink-200">
                                <td colspan="4" class="text-right font-bold text-ink-900">Total</td>
                                <td class="text-right font-bold text-ink-900"><?php echo e(number_format((float) $order->total, 2)); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <h2 class="mb-3 text-lg font-bold text-ink-900">Invoices</h2>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>Invoice</th><th>Date</th><th>Total</th><th>Paid</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $order->invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="font-medium text-ink-800"><?php echo e($invoice->invoice_number); ?></td>
                                    <td><?php echo e($invoice->issue_date?->format('d M Y')); ?></td>
                                    <td><?php echo e(number_format((float) $invoice->total, 2)); ?></td>
                                    <td><?php echo e(number_format((float) $invoice->paid_amount, 2)); ?></td>
                                    <td><?php $c = ['draft' => 'gray', 'sent' => 'amber', 'partial' => 'violet', 'paid' => 'green', 'overdue' => 'red', 'cancelled' => 'gray']; ?>
                                        <span class="badge-<?php echo e($c[$invoice->status] ?? 'gray'); ?>"><?php echo e(\App\Models\Invoice::STATUSES[$invoice->status] ?? $invoice->status); ?></span>
                                    </td>
                                    <td class="text-right"><a href="<?php echo e(route('sales.invoices.show', $invoice)); ?>" class="text-sm font-medium text-navy-600 hover:text-navy-500">View</a></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="6" class="py-6 text-center text-ink-400">No invoices yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if($order->notes): ?>
                <div class="card"><h3 class="text-sm font-bold uppercase tracking-wide text-ink-400">Notes</h3><p class="mt-1 text-sm whitespace-pre-line text-ink-700"><?php echo e($order->notes); ?></p></div>
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
<?php /**PATH C:\MY_ERP\resources\views/sales/orders/show.blade.php ENDPATH**/ ?>