<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => ''.e($supplier->name).'','breadcrumbs' => [['label' => 'Procurement', 'url' => route('suppliers.index')], ['label' => 'Suppliers', 'url' => route('suppliers.index')], ['label' => $supplier->name]]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => ''.e($supplier->name).'','breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Procurement', 'url' => route('suppliers.index')], ['label' => 'Suppliers', 'url' => route('suppliers.index')], ['label' => $supplier->name]])]); ?>

     <?php $__env->slot('actions', null, []); ?> 
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\AiRun::class)): ?>
            <?php if (isset($component)) { $__componentOriginalac3ae74eec2b8a59d49d7482de1288ef = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalac3ae74eec2b8a59d49d7482de1288ef = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ai.action','data' => ['label' => 'AI analysis','url' => route('ai.actions.supplier-analysis'),'payload' => ['supplier_id' => $supplier->id]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ai.action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'AI analysis','url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('ai.actions.supplier-analysis')),'payload' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['supplier_id' => $supplier->id])]); ?>
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
            <?php if (isset($component)) { $__componentOriginalac3ae74eec2b8a59d49d7482de1288ef = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalac3ae74eec2b8a59d49d7482de1288ef = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ai.action','data' => ['label' => 'Deep Analyze','url' => route('ai.deep.supplier'),'payload' => ['supplier_id' => $supplier->id],'intro' => 'A specialist analysis of this supplier: strengths, risks, pricing and delivery observations, negotiation opportunities and a recommended next action — all for your review.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ai.action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Deep Analyze','url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('ai.deep.supplier')),'payload' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['supplier_id' => $supplier->id]),'intro' => 'A specialist analysis of this supplier: strengths, risks, pricing and delivery observations, negotiation opportunities and a recommended next action — all for your review.']); ?>
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
            <?php if (isset($component)) { $__componentOriginalac3ae74eec2b8a59d49d7482de1288ef = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalac3ae74eec2b8a59d49d7482de1288ef = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ai.action','data' => ['label' => 'Negotiation Strategy','url' => route('ai.deep.negotiation'),'payload' => ['supplier_id' => $supplier->id],'intro' => 'A negotiation strategy built from this supplier\'s pricing history, terms and volumes — target price, points, concessions, questions and a draft email. Nothing is sent automatically.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ai.action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Negotiation Strategy','url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('ai.deep.negotiation')),'payload' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['supplier_id' => $supplier->id]),'intro' => 'A negotiation strategy built from this supplier\'s pricing history, terms and volumes — target price, points, concessions, questions and a draft email. Nothing is sent automatically.']); ?>
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
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $supplier)): ?>
            <a href="<?php echo e(route('suppliers.edit', $supplier)); ?>" class="btn-secondary">Edit</a>
        <?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\SupplierOffer::class)): ?>
            <a href="<?php echo e(route('supplier-offers.create')); ?>" class="btn-accent">New Offer</a>
        <?php endif; ?>
     <?php $__env->endSlot(); ?>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="card space-y-3">
            <h2 class="text-lg font-bold text-ink-900"><?php echo e($supplier->name); ?></h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-ink-400">Status</dt><dd><span class="badge-<?php echo e($supplier->status === 'approved' ? 'green' : ($supplier->status === 'blocked' || $supplier->status === 'rejected' ? 'red' : 'amber')); ?>"><?php echo e(\App\Models\Supplier::STATUSES[$supplier->status]); ?></span></dd></div>
                <?php if($supplier->supplier_code): ?><div class="flex justify-between"><dt class="text-ink-400">Code</dt><dd><?php echo e($supplier->supplier_code); ?></dd></div><?php endif; ?>
                <?php if($supplier->tax_id): ?><div class="flex justify-between"><dt class="text-ink-400">Tax ID</dt><dd><?php echo e($supplier->tax_id); ?></dd></div><?php endif; ?>
                <?php if($supplier->contact_person): ?><div class="flex justify-between"><dt class="text-ink-400">Contact</dt><dd><?php echo e($supplier->contact_person); ?></dd></div><?php endif; ?>
                <?php if($supplier->email): ?><div class="flex justify-between"><dt class="text-ink-400">Email</dt><dd><?php echo e($supplier->email); ?></dd></div><?php endif; ?>
                <?php if($supplier->phone): ?><div class="flex justify-between"><dt class="text-ink-400">Phone</dt><dd><?php echo e($supplier->phone); ?></dd></div><?php endif; ?>
                <?php if($supplier->country): ?><div class="flex justify-between"><dt class="text-ink-400">Country</dt><dd><?php echo e($supplier->country->name); ?></dd></div><?php endif; ?>
                <?php if($supplier->payment_terms): ?><div class="flex justify-between"><dt class="text-ink-400">Payment</dt><dd><?php echo e($supplier->payment_terms); ?></dd></div><?php endif; ?>
            </dl>
            <?php if($supplier->notes): ?>
                <div class="rounded-lg bg-ink-50 p-3 text-sm text-ink-600"><?php echo e($supplier->notes); ?></div>
            <?php endif; ?>
        </div>

        <div class="card lg:col-span-2">
            <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400">Offers (<?php echo e($supplier->offers_count); ?>)</h3>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr><th>Material</th><th>Qty (MT)</th><th>Price/MT</th><th>Status</th><th>Date</th><th></th></tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $offers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $offer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="font-medium text-ink-800"><?php echo e($offer->material_category); ?><?php if($offer->grade): ?> <span class="text-ink-400">(<?php echo e($offer->grade); ?>)</span><?php endif; ?></td>
                                <td><?php echo e(number_format((float) $offer->quantity_mt, 2)); ?></td>
                                <td><?php echo e(number_format((float) $offer->price_per_mt, 2)); ?></td>
                                <td><span class="badge-<?php echo e($offer->quality_status === 'GREEN' ? 'green' : ($offer->quality_status === 'RED' ? 'red' : 'amber')); ?>"><?php echo e($offer->quality_status); ?></span></td>
                                <td><?php echo e($offer->offer_date?->format('d M Y') ?? '—'); ?></td>
                                <td class="text-right"><a href="<?php echo e(route('supplier-offers.show', $offer)); ?>" class="text-sm font-medium text-navy-600">View</a></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="6" class="py-4 text-center text-ink-400">No offers yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-4"><?php echo e($offers->links()); ?></div>
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
<?php /**PATH C:\MY_ERP\resources\views\procurement\suppliers\show.blade.php ENDPATH**/ ?>