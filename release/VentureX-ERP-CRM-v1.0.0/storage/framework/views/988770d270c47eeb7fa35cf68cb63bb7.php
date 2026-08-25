<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Search','breadcrumbs' => [['label' => 'Search']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Search','breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Search']])]); ?>

    <div class="mx-auto max-w-4xl">
        <form method="GET" action="<?php echo e(route('search')); ?>" class="mb-8 flex gap-2">
            <input type="search" name="q" value="<?php echo e($q); ?>" placeholder="Search customers, leads, suppliers, offers, documents…" class="input flex-1" autofocus />
            <button type="submit" class="btn-accent">Search</button>
        </form>

        <?php if(strlen($q) >= 2): ?>
            <?php if(collect($results)->flatten()->isEmpty()): ?>
                <p class="text-center text-ink-400">No results for “<?php echo e($q); ?>”.</p>
            <?php else: ?>
                <div class="space-y-6">
                    <?php $__currentLoopData = $results; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($items->isNotEmpty()): ?>
                            <div>
                                <h2 class="mb-2 text-xs font-bold uppercase tracking-wide text-ink-400"><?php echo e(ucfirst($group)); ?> (<?php echo e($items->count()); ?>)</h2>
                                <div class="card divide-y divide-ink-100">
                                    <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $route = match ($group) {
                                                'customers' => route('customers.show', $item),
                                                'leads' => route('leads.show', $item),
                                                'opportunities' => route('opportunities.show', $item),
                                                'suppliers' => route('suppliers.show', $item),
                                                'offers' => route('supplier-offers.show', $item),
                                                'documents' => null,
                                            };
                                        ?>
                                        <a href="<?php echo e($route ?? '#'); ?>" class="flex items-center justify-between px-4 py-3 hover:bg-ink-50">
                                            <div>
                                                <p class="font-medium text-ink-800">
                                                    <?php echo e($item->name ?? $item->contact_name ?? $item->material_description ?? $item->title ?? '—'); ?>

                                                </p>
                                                <p class="text-xs text-ink-400"><?php echo e($item->email ?? $item->company_name ?? $item->supplier?->name ?? '—'); ?></p>
                                            </div>
                                            <svg class="h-4 w-4 text-ink-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <p class="text-center text-ink-400">Type at least 2 characters to search.</p>
        <?php endif; ?>
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
<?php /**PATH C:\MY_ERP\resources\views/search/index.blade.php ENDPATH**/ ?>