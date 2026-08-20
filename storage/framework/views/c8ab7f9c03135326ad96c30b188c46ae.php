<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['label' => null, 'name', 'type' => 'text', 'value' => null, 'help' => null, 'placeholder' => null, 'required' => false, 'disabled' => false]));

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

foreach (array_filter((['label' => null, 'name', 'type' => 'text', 'value' => null, 'help' => null, 'placeholder' => null, 'required' => false, 'disabled' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div>
    <?php if($label): ?>
        <label for="<?php echo e($name); ?>" class="label"><?php echo e($label); ?><?php if($required): ?> <span class="text-red-500">*</span><?php endif; ?></label>
    <?php endif; ?>
    <input id="<?php echo e($name); ?>" type="<?php echo e($type); ?>" name="<?php echo e($name); ?>" value="<?php echo e(old($name, $value)); ?>" placeholder="<?php echo e($placeholder); ?>" <?php echo e($required ? 'required' : ''); ?> <?php echo e($disabled ? 'disabled' : ''); ?> class="input" <?php $__errorArgs = [$name];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> aria-invalid="true" <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>>
    <?php $__errorArgs = [$name];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p>
    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    <?php if($help): ?>
        <p class="mt-1 text-xs text-ink-400"><?php echo e($help); ?></p>
    <?php endif; ?>
</div>
<?php /**PATH C:\MY_ERP\resources\views\components\form\input.blade.php ENDPATH**/ ?>