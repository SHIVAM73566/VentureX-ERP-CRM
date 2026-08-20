<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Container — '.($container->container_number ?? 'New'),'breadcrumbs' => [['label' => 'Logistics', 'url' => route('logistics.containers.index')], ['label' => 'Containers', 'url' => route('logistics.containers.index')], ['label' => $container->container_number]]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Container — '.($container->container_number ?? 'New')),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Logistics', 'url' => route('logistics.containers.index')], ['label' => 'Containers', 'url' => route('logistics.containers.index')], ['label' => $container->container_number]])]); ?>

     <?php $__env->slot('actions', null, []); ?> 
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $container)): ?>
            <a href="<?php echo e(route('logistics.containers.edit', $container)); ?>" class="btn-secondary">Edit</a>
        <?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $container)): ?>
            <form method="POST" action="<?php echo e(route('logistics.containers.destroy', $container)); ?>" onsubmit="return confirm('Delete this container?')">
                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                <button type="submit" class="btn-secondary text-red-600 hover:bg-red-50">Delete</button>
            </form>
        <?php endif; ?>
     <?php $__env->endSlot(); ?>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="card">
            <h2 class="mb-3 text-lg font-bold text-ink-900">Container Summary</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-ink-400">Number</dt><dd><?php echo e($container->container_number); ?></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Size</dt><dd><?php echo e($container->size ?? '—'); ?></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Seal Number</dt><dd><?php echo e($container->seal_number ?? '—'); ?></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Status</dt>
                    <dd><span class="badge-<?php echo e($container->status === 'available' ? 'green' : ($container->status === 'returned' ? 'gray' : 'amber')); ?>"><?php echo e(\App\Models\Container::STATUSES[$container->status] ?? $container->status); ?></span></dd>
                </div>
                <div class="flex justify-between border-t border-ink-100 pt-2"><dt class="font-semibold text-ink-700">Shipments</dt><dd class="font-bold text-ink-900"><?php echo e($container->shipments->count()); ?></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Landed Costs</dt><dd><?php echo e($container->landedCosts->count()); ?></dd></div>
            </dl>

            <?php if($container->notes): ?>
                <div class="mt-4 border-t border-ink-100 pt-3"><h3 class="text-sm font-bold uppercase tracking-wide text-ink-400">Notes</h3><p class="mt-1 text-sm whitespace-pre-line text-ink-700"><?php echo e($container->notes); ?></p></div>
            <?php endif; ?>
        </div>

        <div class="card lg:col-span-2">
            <h2 class="mb-3 text-lg font-bold text-ink-900">Shipments</h2>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead><tr><th>Shipment</th><th>Type</th><th>Route</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $container->shipments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shipment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><a href="<?php echo e(route('logistics.shipments.show', $shipment)); ?>" class="font-semibold text-navy-600 hover:text-navy-500"><?php echo e($shipment->shipment_number); ?></a></td>
                                <td><span class="badge-<?php echo e($shipment->type === 'outbound' ? 'blue' : 'violet'); ?>"><?php echo e(\App\Models\Shipment::TYPES[$shipment->type] ?? $shipment->type); ?></span></td>
                                <td><?php echo e($shipment->origin ?? '—'); ?> → <?php echo e($shipment->destination ?? '—'); ?></td>
                                <td><span class="badge-<?php echo e($shipment->status === 'delivered' ? 'green' : ($shipment->status === 'in_transit' ? 'amber' : 'gray')); ?>"><?php echo e(\App\Models\Shipment::STATUSES[$shipment->status] ?? $shipment->status); ?></span></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="4" class="py-6 text-center text-ink-400">No shipments for this container.</td></tr>
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
<?php /**PATH C:\MY_ERP\resources\views\logistics\containers\show.blade.php ENDPATH**/ ?>