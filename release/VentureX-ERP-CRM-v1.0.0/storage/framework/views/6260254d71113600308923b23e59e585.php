<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Invoice — '.($invoice->invoice_number ?? 'New'),'breadcrumbs' => [['label' => 'Sales', 'url' => route('sales.invoices.index')], ['label' => 'Invoices', 'url' => route('sales.invoices.index')], ['label' => $invoice->invoice_number]]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Invoice — '.($invoice->invoice_number ?? 'New')),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Sales', 'url' => route('sales.invoices.index')], ['label' => 'Invoices', 'url' => route('sales.invoices.index')], ['label' => $invoice->invoice_number]])]); ?>

     <?php $__env->slot('actions', null, []); ?> 
        <a href="<?php echo e(route('sales.invoices.pdf', $invoice)); ?>" class="btn-secondary" target="_blank">Download PDF</a>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\AiRun::class)): ?>
            <?php if (isset($component)) { $__componentOriginalac3ae74eec2b8a59d49d7482de1288ef = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalac3ae74eec2b8a59d49d7482de1288ef = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ai.action','data' => ['label' => 'AI summary','url' => route('ai.actions.invoice-summary'),'payload' => ['invoice_id' => $invoice->id]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ai.action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'AI summary','url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('ai.actions.invoice-summary')),'payload' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['invoice_id' => $invoice->id])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalac3ae74eec2b8a59d49d7482de1288ef)): ?>
<?php $attributes = $__attributesOriginalac3ae74eec2b8a59d49d7482de1288ef; ?>
<?php unset($__attributesOriginalac3ae74eec2b8a59d49d7482de1288ef); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalac3ae74eec2b8a59d49d7482de1288ef)): ?>
<?php $component = $__componentOriginalac3ae74eec2b8a59d49d7482de1288ef; ?>
<?php unset($__componentOriginalac3ae74eec2b8a59d49d7482de1288ef); ?>
<?php endif; ?>
        <?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Payment::class)): ?>
            <a href="<?php echo e(route('sales.payments.create')); ?>?invoice_id=<?php echo e($invoice->id); ?>" class="btn-accent">Record Payment</a>
        <?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $invoice)): ?>
            <a href="<?php echo e(route('sales.invoices.edit', $invoice)); ?>" class="btn-secondary">Edit</a>
        <?php endif; ?>
     <?php $__env->endSlot(); ?>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6">
            <div class="card">
                <h2 class="mb-3 text-lg font-bold text-ink-900">Invoice Summary</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-ink-400">Number</dt><dd><?php echo e($invoice->invoice_number); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Customer</dt><dd class="text-right"><?php echo e($invoice->customer?->name ?? '—'); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Issue Date</dt><dd><?php echo e($invoice->issue_date?->format('d M Y')); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Due Date</dt><dd><?php echo e($invoice->due_date?->format('d M Y') ?? '—'); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Status</dt>
                        <dd><?php $c = ['draft' => 'gray', 'sent' => 'amber', 'partial' => 'violet', 'paid' => 'green', 'overdue' => 'red', 'cancelled' => 'gray']; ?>
                            <span class="badge-<?php echo e($c[$invoice->status] ?? 'gray'); ?>"><?php echo e(\App\Models\Invoice::STATUSES[$invoice->status] ?? $invoice->status); ?></span>
                        </dd>
                    </div>
                    <div class="flex justify-between border-t border-ink-100 pt-2"><dt class="font-semibold text-ink-700">Total</dt><dd class="font-bold text-ink-900"><?php echo e(number_format((float) $invoice->total, 2)); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Paid</dt><dd class="font-semibold text-emerald-600"><?php echo e(number_format((float) $invoice->paid_amount, 2)); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Outstanding</dt><dd class="font-semibold <?php echo e((float) $invoice->outstandingBalance() > 0 ? 'text-red-600' : 'text-emerald-600'); ?>"><?php echo e(number_format((float) $invoice->outstandingBalance(), 2)); ?></dd></div>
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
                            <?php $__empty_1 = true; $__currentLoopData = $invoice->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
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
                                <td class="text-right font-bold text-ink-900"><?php echo e(number_format((float) $invoice->total, 2)); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <h2 class="mb-3 text-lg font-bold text-ink-900">Payments</h2>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>Payment</th><th>Date</th><th>Method</th><th>Amount</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $invoice->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="font-medium text-ink-800"><?php echo e($payment->payment_number); ?></td>
                                    <td><?php echo e($payment->payment_date?->format('d M Y')); ?></td>
                                    <td><?php echo e(\App\Models\Payment::METHODS[$payment->method] ?? $payment->method); ?></td>
                                    <td><?php echo e(number_format((float) $payment->amount, 2)); ?></td>
                                    <td><?php $c = ['pending' => 'amber', 'completed' => 'green', 'failed' => 'red', 'refunded' => 'gray']; ?>
                                        <span class="badge-<?php echo e($c[$payment->status] ?? 'gray'); ?>"><?php echo e(\App\Models\Payment::STATUSES[$payment->status] ?? $payment->status); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="5" class="py-6 text-center text-ink-400">No payments recorded.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if($invoice->notes): ?>
                <div class="card"><h3 class="text-sm font-bold uppercase tracking-wide text-ink-400">Notes</h3><p class="mt-1 text-sm whitespace-pre-line text-ink-700"><?php echo e($invoice->notes); ?></p></div>
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
<?php /**PATH C:\MY_ERP\resources\views/sales/invoices/show.blade.php ENDPATH**/ ?>