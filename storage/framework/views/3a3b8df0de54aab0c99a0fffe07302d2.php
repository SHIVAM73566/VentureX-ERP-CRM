<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Shipment — '.($shipment->shipment_number ?? 'New'),'breadcrumbs' => [['label' => 'Logistics', 'url' => route('logistics.shipments.index')], ['label' => 'Shipments', 'url' => route('logistics.shipments.index')], ['label' => $shipment->shipment_number]]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Shipment — '.($shipment->shipment_number ?? 'New')),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Logistics', 'url' => route('logistics.shipments.index')], ['label' => 'Shipments', 'url' => route('logistics.shipments.index')], ['label' => $shipment->shipment_number]])]); ?>

     <?php $__env->slot('actions', null, []); ?> 
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $shipment)): ?>
            <a href="<?php echo e(route('logistics.shipments.edit', $shipment)); ?>" class="btn-secondary">Edit</a>
        <?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $shipment)): ?>
            <form method="POST" action="<?php echo e(route('logistics.shipments.destroy', $shipment)); ?>" onsubmit="return confirm('Delete this shipment?')">
                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                <button type="submit" class="btn-secondary text-red-600 hover:bg-red-50">Delete</button>
            </form>
        <?php endif; ?>
     <?php $__env->endSlot(); ?>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="card">
            <h2 class="mb-3 text-lg font-bold text-ink-900">Shipment Summary</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-ink-400">Number</dt><dd><?php echo e($shipment->shipment_number); ?></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Type</dt><dd><span class="badge-<?php echo e($shipment->type === 'outbound' ? 'blue' : 'violet'); ?>"><?php echo e(\App\Models\Shipment::TYPES[$shipment->type] ?? $shipment->type); ?></span></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Mode</dt><dd><?php echo e(\App\Models\Shipment::MODES[$shipment->mode] ?? $shipment->mode); ?></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Status</dt>
                    <dd><?php $sc = ['draft' => 'gray', 'scheduled' => 'blue', 'in_transit' => 'amber', 'customs' => 'violet', 'delivered' => 'green', 'delayed' => 'red', 'cancelled' => 'gray']; ?>
                        <span class="badge-<?php echo e($sc[$shipment->status] ?? 'gray'); ?>"><?php echo e(\App\Models\Shipment::STATUSES[$shipment->status] ?? $shipment->status); ?></span></dd>
                </div>
                <div class="flex justify-between"><dt class="text-ink-400">Carrier</dt><dd><?php echo e($shipment->carrier ?? '—'); ?></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Tracking</dt><dd><?php echo e($shipment->tracking_number ?? '—'); ?></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Container</dt><dd><?php echo e($shipment->container?->container_number ?? '—'); ?></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Customer</dt><dd><?php echo e($shipment->customer?->name ?? '—'); ?></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Supplier</dt><dd><?php echo e($shipment->supplier?->name ?? '—'); ?></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Sales Order</dt>
                    <dd><?php if($shipment->salesOrder): ?><a href="<?php echo e(route('sales.orders.show', $shipment->salesOrder)); ?>" class="text-navy-600 hover:text-navy-500"><?php echo e($shipment->salesOrder->order_number); ?></a><?php else: ?> — <?php endif; ?></dd>
                </div>
                <div class="flex justify-between"><dt class="text-ink-400">Purchase Order</dt>
                    <dd><?php if($shipment->purchaseOrder): ?><a href="<?php echo e(route('procurement.orders.show', $shipment->purchaseOrder)); ?>" class="text-navy-600 hover:text-navy-500"><?php echo e($shipment->purchaseOrder->po_number); ?></a><?php else: ?> — <?php endif; ?></dd>
                </div>
                <div class="flex justify-between"><dt class="text-ink-400">Route</dt><dd class="text-right"><?php echo e($shipment->origin ?? '—'); ?> → <?php echo e($shipment->destination ?? '—'); ?></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Departure</dt><dd><?php echo e($shipment->departure_date?->format('d M Y') ?? '—'); ?></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Arrival</dt><dd><?php echo e($shipment->arrival_date?->format('d M Y') ?? '—'); ?></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Weight / Volume</dt><dd><?php echo e($shipment->weight !== null ? number_format((float) $shipment->weight, 2).' kg' : '—'); ?> / <?php echo e($shipment->volume !== null ? number_format((float) $shipment->volume, 2).' m³' : '—'); ?></dd></div>
            </dl>
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
<?php /**PATH C:\MY_ERP\resources\views\logistics\shipments\show.blade.php ENDPATH**/ ?>