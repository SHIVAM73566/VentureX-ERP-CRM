<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['lines' => 3]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['lines' => 3]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<div class="card animate-pulse">
    <div class="h-5 bg-ink-200 rounded w-1/3 mb-4"></div>
    <div class="h-8 bg-ink-200 rounded w-1/2 mb-2"></div>
    <?php for($i = 0; $i < $lines; $i++): ?>
        <div class="h-3 bg-ink-100 rounded mb-1" style="width: <?php echo e(rand(50, 90)); ?>%"></div>
    <?php endfor; ?>
</div>
<?php /**PATH C:\MY_ERP\resources\views\components\ui\skeleton-card.blade.php ENDPATH**/ ?>