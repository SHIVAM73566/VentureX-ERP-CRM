<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => ''.e($customer->name).'','breadcrumbs' => [['label' => 'CRM', 'url' => route('customers.index')], ['label' => 'Customers', 'url' => route('customers.index')], ['label' => $customer->name]]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => ''.e($customer->name).'','breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'CRM', 'url' => route('customers.index')], ['label' => 'Customers', 'url' => route('customers.index')], ['label' => $customer->name]])]); ?>

     <?php $__env->slot('actions', null, []); ?> 
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\AiRun::class)): ?>
            <?php if (isset($component)) { $__componentOriginalac3ae74eec2b8a59d49d7482de1288ef = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalac3ae74eec2b8a59d49d7482de1288ef = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ai.action','data' => ['label' => 'AI summary','url' => route('ai.actions.customer-summary'),'payload' => ['customer_id' => $customer->id]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ai.action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'AI summary','url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('ai.actions.customer-summary')),'payload' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['customer_id' => $customer->id])]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ai.action','data' => ['label' => 'Deep Customer Analysis','url' => route('ai.deep.customer'),'payload' => ['customer_id' => $customer->id],'intro' => 'A specialist analysis of this customer: health, revenue opportunity, retention risk, follow-up priority, upsell/cross-sell opportunities and a recommended next action — all for your review.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ai.action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Deep Customer Analysis','url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('ai.deep.customer')),'payload' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['customer_id' => $customer->id]),'intro' => 'A specialist analysis of this customer: health, revenue opportunity, retention risk, follow-up priority, upsell/cross-sell opportunities and a recommended next action — all for your review.']); ?>
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
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $customer)): ?>
            <a href="<?php echo e(route('customers.edit', $customer)); ?>" class="btn-secondary">Edit</a>
        <?php endif; ?>
     <?php $__env->endSlot(); ?>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6">
            <div class="card">
                <h2 class="mb-3 text-lg font-bold text-ink-900"><?php echo e($customer->name); ?></h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-ink-400">Status</dt><dd><span class="<?php echo e($customer->status === 'active' ? 'badge-green' : 'badge-gray'); ?>"><?php echo e(ucfirst($customer->status)); ?></span></dd></div>
                    <?php if($customer->email): ?><div class="flex justify-between"><dt class="text-ink-400">Email</dt><dd><?php echo e($customer->email); ?></dd></div><?php endif; ?>
                    <?php if($customer->phone): ?><div class="flex justify-between"><dt class="text-ink-400">Phone</dt><dd><?php echo e($customer->phone); ?></dd></div><?php endif; ?>
                    <?php if($customer->website): ?><div class="flex justify-between"><dt class="text-ink-400">Website</dt><dd><?php echo e($customer->website); ?></dd></div><?php endif; ?>
                    <?php if($customer->tax_id): ?><div class="flex justify-between"><dt class="text-ink-400">VAT</dt><dd><?php echo e($customer->tax_id); ?></dd></div><?php endif; ?>
                    <?php if($customer->country): ?><div class="flex justify-between"><dt class="text-ink-400">Country</dt><dd><?php echo e($customer->country->name); ?></dd></div><?php endif; ?>
                </dl>
            </div>

            <div class="card">
                <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400">Contacts (<?php echo e($customer->contacts_count); ?>)</h3>
                <?php $__empty_1 = true; $__currentLoopData = $customer->contacts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $contact): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="py-2">
                        <p class="text-sm font-semibold text-ink-800"><?php echo e($contact->fullName()); ?></p>
                        <p class="text-xs text-ink-400"><?php echo e($contact->email); ?><?php if($contact->phone): ?> • <?php echo e($contact->phone); ?><?php endif; ?></p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-sm text-ink-400">No contacts.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="space-y-6 lg:col-span-2">
            <div class="card">
                <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400">Opportunities (<?php echo e($customer->opportunities_count); ?>)</h3>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr><th>Opportunity</th><th>Stage</th><th>Value</th><th></th></tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $customer->opportunities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opportunity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="font-medium text-ink-800"><?php echo e($opportunity->name); ?></td>
                                    <td><?php echo e($opportunity->stage); ?></td>
                                    <td><?php echo e($opportunity->expected_value); ?></td>
                                    <td class="text-right"><a href="<?php echo e(route('opportunities.show', $opportunity)); ?>" class="text-sm font-medium text-navy-600">View</a></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="4" class="py-4 text-center text-ink-400">No opportunities.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400">Recent Activities</h3>
                <?php $__empty_1 = true; $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="border-l-2 border-navy-100 py-1 pl-4">
                        <p class="text-sm text-ink-800"><span class="font-semibold"><?php echo e(ucfirst($activity->type)); ?></span> — <?php echo e($activity->title); ?></p>
                        <p class="text-xs text-ink-400"><?php echo e($activity->due_at?->format('d M Y, H:i') ?? '—'); ?> <?php if($activity->assignedTo): ?> by <?php echo e($activity->assignedTo->name); ?><?php endif; ?></p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-sm text-ink-400">No activities logged.</p>
                <?php endif; ?>
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
<?php /**PATH C:\MY_ERP\resources\views\crm\customers\show.blade.php ENDPATH**/ ?>