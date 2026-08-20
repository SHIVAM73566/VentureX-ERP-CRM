<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Quotation — '.($quotation->quotation_number ?? 'New'),'breadcrumbs' => [['label' => 'Sales', 'url' => route('sales.quotations.index')], ['label' => 'Quotations', 'url' => route('sales.quotations.index')], ['label' => $quotation->quotation_number]]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Quotation — '.($quotation->quotation_number ?? 'New')),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Sales', 'url' => route('sales.quotations.index')], ['label' => 'Quotations', 'url' => route('sales.quotations.index')], ['label' => $quotation->quotation_number]])]); ?>

     <?php $__env->slot('actions', null, []); ?> 
        <a href="<?php echo e(route('sales.quotations.pdf', $quotation)); ?>" class="btn-secondary" target="_blank">Download PDF</a>
        <?php if($quotation->status === 'accepted'): ?>
            <form method="POST" action="<?php echo e(route('sales.quotations.convert', $quotation)); ?>" onsubmit="return confirm('Convert this quotation to a sales order?')">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn-primary">Convert to Sales Order</button>
            </form>
        <?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $quotation)): ?>
            <a href="<?php echo e(route('sales.quotations.edit', $quotation)); ?>" class="btn-secondary">Edit</a>
        <?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $quotation)): ?>
            <form method="POST" action="<?php echo e(route('sales.quotations.destroy', $quotation)); ?>" onsubmit="return confirm('Delete this quotation?')">
                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                <button type="submit" class="btn-secondary text-red-600 hover:bg-red-50">Delete</button>
            </form>
        <?php endif; ?>
     <?php $__env->endSlot(); ?>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6">
            <div class="card">
                <h2 class="mb-3 text-lg font-bold text-ink-900">Quotation Summary</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-ink-400">Number</dt><dd><?php echo e($quotation->quotation_number); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Customer</dt><dd class="text-right"><?php echo e($quotation->customer?->name ?? '—'); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Created</dt><dd><?php echo e($quotation->created_at?->format('d M Y H:i')); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Valid Until</dt><dd><?php echo e($quotation->valid_until?->format('d M Y') ?? '—'); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Status</dt>
                        <dd><?php $c = ['draft' => 'gray', 'sent' => 'amber', 'accepted' => 'green', 'rejected' => 'red', 'expired' => 'gray', 'converted' => 'blue']; ?>
                            <span class="badge-<?php echo e($c[$quotation->status] ?? 'gray'); ?>"><?php echo e(\App\Models\Quotation::STATUSES[$quotation->status] ?? $quotation->status); ?></span>
                        </dd>
                    </div>
                    <div class="flex justify-between border-t border-ink-100 pt-2"><dt class="font-semibold text-ink-700">Total</dt><dd class="font-bold text-ink-900"><?php echo e(number_format((float) $quotation->total, 2)); ?> <?php echo e($quotation->currency_code ?? 'USD'); ?></dd></div>
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
                            <?php $__empty_1 = true; $__currentLoopData = $quotation->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
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
                                <td colspan="4" class="text-right font-semibold text-ink-700">Subtotal / Discount / Tax</td>
                                <td class="text-right font-semibold"><?php echo e(number_format((float) $quotation->subtotal, 2)); ?> / <?php echo e(number_format((float) $quotation->discount, 2)); ?> / <?php echo e(number_format((float) $quotation->tax, 2)); ?></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-right font-bold text-ink-900">Total</td>
                                <td class="text-right font-bold text-ink-900"><?php echo e(number_format((float) $quotation->total, 2)); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if($quotation->notes || $quotation->terms): ?>
                <div class="card space-y-3">
                    <?php if($quotation->notes): ?><div><h3 class="text-sm font-bold uppercase tracking-wide text-ink-400">Notes</h3><p class="mt-1 text-sm whitespace-pre-line text-ink-700"><?php echo e($quotation->notes); ?></p></div><?php endif; ?>
                    <?php if($quotation->terms): ?><div><h3 class="text-sm font-bold uppercase tracking-wide text-ink-400">Terms</h3><p class="mt-1 text-sm whitespace-pre-line text-ink-700"><?php echo e($quotation->terms); ?></p></div><?php endif; ?>
                </div>
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
<?php /**PATH C:\MY_ERP\resources\views\sales\quotations\show.blade.php ENDPATH**/ ?>