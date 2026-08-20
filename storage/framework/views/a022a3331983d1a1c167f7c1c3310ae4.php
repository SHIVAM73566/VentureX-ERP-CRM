<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Offer — '.($offer->material_category ?? 'Unknown').' ('.$offer->quality_status.')','breadcrumbs' => [['label' => 'Procurement', 'url' => route('supplier-offers.index')], ['label' => 'Supplier Offers', 'url' => route('supplier-offers.index')], ['label' => '#'.$offer->id]]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Offer — '.($offer->material_category ?? 'Unknown').' ('.$offer->quality_status.')'),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Procurement', 'url' => route('supplier-offers.index')], ['label' => 'Supplier Offers', 'url' => route('supplier-offers.index')], ['label' => '#'.$offer->id]])]); ?>

     <?php $__env->slot('actions', null, []); ?> 
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $offer)): ?>
            <a href="<?php echo e(route('supplier-offers.edit', $offer)); ?>" class="btn-secondary">Edit</a>
        <?php endif; ?>
     <?php $__env->endSlot(); ?>

    <?php
        $analysis = $offer->ai_analysis ?? [];
        $elements = \App\Services\Procurement\ScrapOfferProcessingService::GRADE_CHEMISTRY[$analysis['grade_key'] ?? ''] ?? [];
    ?>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6">
            <div class="card">
                <h2 class="mb-3 text-lg font-bold text-ink-900">Offer Summary</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-ink-400">Material</dt><dd class="text-right"><?php echo e($offer->material_category); ?></dd></div>
                    <?php if($offer->material_description): ?><div class="flex justify-between"><dt class="text-ink-400">Description</dt><dd class="text-right"><?php echo e($offer->material_description); ?></dd></div><?php endif; ?>
                    <?php if($offer->grade): ?><div class="flex justify-between"><dt class="text-ink-400">Grade</dt><dd class="text-right"><?php echo e($offer->grade); ?></dd></div><?php endif; ?>
                    <?php if($offer->isri_grade): ?><div class="flex justify-between"><dt class="text-ink-400">ISRI</dt><dd class="text-right"><?php echo e($offer->isri_grade); ?></dd></div><?php endif; ?>
                    <div class="flex justify-between"><dt class="text-ink-400">Quantity</dt><dd><?php echo e(number_format((float) $offer->quantity_mt, 3)); ?> MT</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Price/MT</dt><dd><?php echo e(number_format((float) $offer->price_per_mt, 2)); ?> <?php echo e($offer->currency_code ?? 'USD'); ?></dd></div>
                    <div class="flex justify-between border-t border-ink-100 pt-2"><dt class="font-semibold text-ink-700">Est. Metal Value</dt><dd class="font-bold text-ink-900"><?php echo e($offer->estimated_metal_value ? number_format((float) $offer->estimated_metal_value, 2) : '—'); ?></dd></div>
                </dl>
            </div>

            <div class="card space-y-3">
                <h2 class="text-lg font-bold text-ink-900">Supplier</h2>
                <?php if($offer->supplier): ?>
                    <a href="<?php echo e(route('suppliers.show', $offer->supplier)); ?>" class="block font-medium text-navy-600 hover:text-navy-500"><?php echo e($offer->supplier->name); ?></a>
                    <p class="text-sm text-ink-400"><?php echo e($offer->supplier->contact_person ?? ''); ?> <?php echo e($offer->supplier->email ? '• '.$offer->supplier->email : ''); ?></p>
                <?php else: ?>
                    <p class="text-sm text-ink-600"><?php echo e($offer->source_email ?? 'No supplier linked'); ?></p>
                <?php endif; ?>
                <?php if($offer->contact_person): ?><p class="text-sm text-ink-600">Contact: <?php echo e($offer->contact_person); ?></p><?php endif; ?>
                <?php if($offer->offer_date): ?><p class="text-sm text-ink-600">Offered: <?php echo e($offer->offer_date->format('d M Y')); ?></p><?php endif; ?>
                <?php if($offer->validity_date): ?><p class="text-sm text-ink-600">Valid until: <?php echo e($offer->validity_date->format('d M Y')); ?></p><?php endif; ?>
            </div>
        </div>

        <div class="space-y-6 lg:col-span-2">
            <div class="card border-<?php echo e($offer->quality_status === 'GREEN' ? 'emerald' : ($offer->quality_status === 'RED' ? 'red' : 'amber')); ?>-200">
                <h2 class="mb-4 flex items-center gap-2 text-lg font-bold text-ink-900">
                    <span class="badge-<?php echo e($offer->quality_status === 'GREEN' ? 'green' : ($offer->quality_status === 'RED' ? 'red' : 'amber')); ?>"><?php echo e($offer->quality_status); ?></span>
                    AI Chemistry & Grade Analysis
                </h2>

                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="rounded-lg bg-ink-50 p-3">
                        <p class="text-xs font-semibold uppercase text-ink-400">Grade Match</p>
                        <p class="text-sm font-bold text-ink-800"><?php echo e($analysis['grade_match'] ?? 'unknown'); ?></p>
                    </div>
                    <div class="rounded-lg bg-ink-50 p-3">
                        <p class="text-xs font-semibold uppercase text-ink-400">Risk Level</p>
                        <p class="text-sm font-bold text-ink-800"><?php echo e($analysis['risk_level'] ?? '—'); ?></p>
                    </div>
                    <div class="rounded-lg bg-ink-50 p-3">
                        <p class="text-xs font-semibold uppercase text-ink-400">Matched Grade</p>
                        <p class="text-sm font-bold text-ink-800"><?php echo e($analysis['grade_key'] ?? 'unknown'); ?></p>
                    </div>
                </div>

                <?php if(! empty($analysis['issues'])): ?>
                    <div class="mt-4 space-y-1">
                        <?php $__currentLoopData = $analysis['issues']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $issue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <p class="text-sm text-red-600">• <?php echo e($issue); ?></p>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <p class="mt-4 text-sm text-emerald-600">No issues flagged. Requires COA verification before final decision.</p>
                <?php endif; ?>

                <div class="mt-5">
                    <p class="mb-2 text-xs font-bold uppercase tracking-wide text-ink-400">Reported Chemistry vs Expected Range</p>
                    <div class="space-y-2">
                        <?php $__empty_1 = true; $__currentLoopData = ($analysis['reported_elements'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $range = $elements[strtoupper($element)] ?? null;
                                $inRange = $range && $value >= $range[0] && $value <= $range[1];
                            ?>
                            <div class="flex items-center gap-3">
                                <span class="w-8 text-sm font-semibold uppercase text-ink-600"><?php echo e($element); ?></span>
                                <div class="h-2 flex-1 rounded-full bg-ink-100">
                                    <div class="h-2 rounded-full <?php echo e($inRange ? 'bg-emerald-500' : 'bg-red-500'); ?>" style="width: <?php echo e(max(4, min(100, $value))); ?>%"></div>
                                </div>
                                <span class="w-16 text-right text-sm text-ink-600"><?php echo e($value); ?>%</span>
                                <?php if($range): ?>
                                    <span class="w-20 text-xs text-ink-400"><?php echo e($range[0]); ?>–<?php echo e($range[1]); ?>%</span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <p class="text-sm text-ink-400">No chemistry reported for this offer.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <p class="mt-5 text-xs text-ink-400">
                    <strong>Governance:</strong> this analysis is advisory. The AI does not approve or reject suppliers.
                    A human buyer must make the final decision.
                </p>
            </div>

            <div class="card">
                <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400">Offer Documents & Terms</h3>
                <dl class="grid gap-2 text-sm sm:grid-cols-2">
                    <?php if($offer->coa_number): ?><div class="flex justify-between"><dt class="text-ink-400">COA Number</dt><dd><?php echo e($offer->coa_number); ?></dd></div><?php endif; ?>
                    <?php if($offer->spectro_report_number): ?><div class="flex justify-between"><dt class="text-ink-400">Spectro Report</dt><dd><?php echo e($offer->spectro_report_number); ?></dd></div><?php endif; ?>
                    <div class="flex justify-between"><dt class="text-ink-400">COA Available</dt><dd><?php echo e($offer->coa_available ? 'Yes' : 'No'); ?></dd></div>
                    <?php if($offer->delivery_location): ?><div class="flex justify-between"><dt class="text-ink-400">Delivery</dt><dd><?php echo e($offer->delivery_location); ?></dd></div><?php endif; ?>
                    <?php if($offer->payment_terms): ?><div class="flex justify-between"><dt class="text-ink-400">Payment</dt><dd><?php echo e($offer->payment_terms); ?></dd></div><?php endif; ?>
                    <?php if($offer->loading_terms): ?><div class="flex justify-between"><dt class="text-ink-400">Loading</dt><dd><?php echo e($offer->loading_terms); ?></dd></div><?php endif; ?>
                </dl>
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
<?php /**PATH C:\MY_ERP\resources\views\procurement\offers\show.blade.php ENDPATH**/ ?>