<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'lines' => 1,
    'class' => '',
    'circle' => false,
]));

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

foreach (array_filter(([
    'lines' => 1,
    'class' => '',
    'circle' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<div <?php echo e($attributes->merge(['class' => 'animate-pulse'])); ?>>
    <?php if($circle): ?>
        <div class="rounded-full bg-ink-200 h-10 w-10 <?php echo e($class); ?>"></div>
    <?php else: ?>
        <?php for($i = 0; $i < $lines; $i++): ?>
            <div class="h-4 bg-ink-200 rounded <?php echo e($class); ?> <?php echo e($i < $lines - 1 ? 'mb-2' : ''); ?>" style="width: <?php echo e(rand(60, 100)); ?>%"></div>
        <?php endfor; ?>
    <?php endif; ?>
</div>
<?php /**PATH C:\MY_ERP\resources\views\components\ui\skeleton.blade.php ENDPATH**/ ?>