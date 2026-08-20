<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['rows' => 5, 'cols' => 4]));

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

foreach (array_filter((['rows' => 5, 'cols' => 4]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<div class="animate-pulse">
    <div class="bg-ink-200 h-10 rounded-t-lg w-full"></div>
    <?php for($i = 0; $i < $rows; $i++): ?>
        <div class="flex gap-4 py-3 <?php echo e($i < $rows - 1 ? 'border-b border-ink-100' : ''); ?>">
            <?php for($j = 0; $j < $cols; $j++): ?>
                <div class="h-4 bg-ink-100 rounded flex-1"></div>
            <?php endfor; ?>
        </div>
    <?php endfor; ?>
</div>
<?php /**PATH C:\MY_ERP\resources\views\components\ui\skeleton-table.blade.php ENDPATH**/ ?>