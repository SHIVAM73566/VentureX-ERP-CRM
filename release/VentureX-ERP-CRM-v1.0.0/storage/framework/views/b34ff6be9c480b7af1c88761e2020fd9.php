<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['name', 'label' => null, 'checked' => false, 'description' => null]));

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

foreach (array_filter((['name', 'label' => null, 'checked' => false, 'description' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<label class="flex items-start gap-3 rounded-lg border border-ink-200 bg-white p-3 hover:bg-ink-50">
    <input type="hidden" name="<?php echo e($name); ?>" value="0">
    <input type="checkbox" name="<?php echo e($name); ?>" value="1" <?php if(old($name, $checked)): echo 'checked'; endif; ?> class="mt-0.5 h-4 w-4 rounded border-ink-300 text-accent-600 focus:ring-accent-500">
    <span>
        <?php if($label): ?>
            <span class="block text-sm font-medium text-ink-800"><?php echo e($label); ?></span>
        <?php endif; ?>
        <?php if($description): ?>
            <span class="block text-xs text-ink-400"><?php echo e($description); ?></span>
        <?php endif; ?>
    </span>
</label>
<?php /**PATH C:\MY_ERP\resources\views/components/form/checkbox.blade.php ENDPATH**/ ?>