<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Product — '.($product->name ?? 'New'),'breadcrumbs' => [['label' => 'Inventory', 'url' => route('inventory.products.index')], ['label' => 'Products', 'url' => route('inventory.products.index')], ['label' => $product->name]]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Product — '.($product->name ?? 'New')),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Inventory', 'url' => route('inventory.products.index')], ['label' => 'Products', 'url' => route('inventory.products.index')], ['label' => $product->name]])]); ?>

     <?php $__env->slot('actions', null, []); ?> 
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\AiRun::class)): ?>
            <?php if (isset($component)) { $__componentOriginalac3ae74eec2b8a59d49d7482de1288ef = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalac3ae74eec2b8a59d49d7482de1288ef = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ai.action','data' => ['label' => 'AI stock insight','url' => route('ai.actions.inventory'),'payload' => ['question' => 'Analyse the current stock position and reorder situation for this product.', 'product_id' => $product->id]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ai.action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'AI stock insight','url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('ai.actions.inventory')),'payload' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['question' => 'Analyse the current stock position and reorder situation for this product.', 'product_id' => $product->id])]); ?>
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
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\StockMovement::class)): ?>
            <a href="<?php echo e(route('inventory.stock.create')); ?>" class="btn-accent">+ Stock Movement</a>
        <?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $product)): ?>
            <a href="<?php echo e(route('inventory.products.edit', $product)); ?>" class="btn-secondary">Edit</a>
        <?php endif; ?>
     <?php $__env->endSlot(); ?>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6">
            <div class="card">
                <h2 class="mb-3 text-lg font-bold text-ink-900">Product Summary</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-ink-400">SKU</dt><dd><?php echo e($product->sku ?? '—'); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Category</dt><dd><?php echo e($product->category ?? '—'); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Unit</dt><dd><?php echo e($product->unit?->name ?? '—'); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Purchase Price</dt><dd><?php echo e(number_format((float) $product->purchase_price, 2)); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Selling Price</dt><dd><?php echo e(number_format((float) $product->selling_price, 2)); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Tax</dt><dd><?php echo e($product->taxRate?->name ?? '—'); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Supplier</dt><dd class="text-right"><?php echo e($product->supplier?->name ?? '—'); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Reorder Level</dt><dd><?php echo e($product->reorder_level !== null ? number_format((float) $product->reorder_level, 4) : '—'); ?></dd></div>
                    <div class="flex justify-between border-t border-ink-100 pt-2"><dt class="font-semibold text-ink-700">Available Stock</dt><dd class="font-bold text-ink-900"><?php echo e(number_format($product->availableStock(), 4)); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Status</dt>
                        <dd><?php if($product->isLowStock()): ?><span class="badge-red">Low Stock</span><?php else: ?><span class="badge-<?php echo e($product->status === 'active' ? 'green' : 'gray'); ?>"><?php echo e(\App\Models\Product::STATUSES[$product->status] ?? $product->status); ?></span><?php endif; ?></dd>
                    </div>
                </dl>
            </div>

            <?php if($product->description || $product->notes): ?>
                <div class="card space-y-3">
                    <?php if($product->description): ?><div><h3 class="text-sm font-bold uppercase tracking-wide text-ink-400">Description</h3><p class="mt-1 text-sm whitespace-pre-line text-ink-700"><?php echo e($product->description); ?></p></div><?php endif; ?>
                    <?php if($product->notes): ?><div><h3 class="text-sm font-bold uppercase tracking-wide text-ink-400">Notes</h3><p class="mt-1 text-sm whitespace-pre-line text-ink-700"><?php echo e($product->notes); ?></p></div><?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="space-y-6 lg:col-span-2">
            <div class="card">
                <h2 class="mb-3 text-lg font-bold text-ink-900">Stock Movements</h2>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>Type</th><th>Qty</th><th>Warehouse</th><th>Unit Cost</th><th>Note</th><th>Date</th></tr></thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $product->stockMovements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $movement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><span class="badge-<?php echo e($movement->type === 'in' ? 'green' : ($movement->type === 'out' ? 'red' : 'gray')); ?>"><?php echo e(\App\Models\StockMovement::TYPES[$movement->type] ?? $movement->type); ?></span></td>
                                    <td class="font-semibold"><?php echo e(number_format((float) $movement->quantity, 4)); ?></td>
                                    <td><?php echo e($movement->warehouse?->name ?? '—'); ?></td>
                                    <td><?php echo e($movement->unit_cost !== null ? number_format((float) $movement->unit_cost, 2) : '—'); ?></td>
                                    <td><?php echo e($movement->note ?? '—'); ?></td>
                                    <td><?php echo e($movement->created_at?->format('d M Y H:i')); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="6" class="py-6 text-center text-ink-400">No stock movements yet.</td></tr>
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
<?php /**PATH C:\MY_ERP\resources\views/inventory/products/show.blade.php ENDPATH**/ ?>