<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Delete My Account','breadcrumbs' => [['label' => 'Settings', 'url' => route('dashboard')], ['label' => 'Delete Account']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Delete My Account','breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Settings', 'url' => route('dashboard')], ['label' => 'Delete Account']])]); ?>

    <div class="mx-auto max-w-2xl space-y-6">
        
        <div class="rounded-lg border border-red-200 bg-red-50 p-6 dark:border-red-800 dark:bg-red-950">
            <div class="flex items-start gap-3">
                <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
                <div>
                    <h3 class="text-lg font-bold text-red-800 dark:text-red-200">Warning: This action is irreversible</h3>
                    <p class="mt-1 text-sm text-red-700 dark:text-red-300">
                        Deleting your account will permanently remove all your personal data including:
                    </p>
                    <ul class="mt-2 list-inside list-disc text-sm text-red-700 dark:text-red-300">
                        <li>Your name, email, phone, and job title</li>
                        <li>Your login credentials and MFA settings</li>
                        <li>All audit logs associated with your account</li>
                        <li>Your login history and device fingerprints</li>
                    </ul>
                </div>
            </div>
        </div>

        
        <div class="card">
            <h2 class="mb-4 text-lg font-bold text-ink-900">Your Data Summary</h2>
            <div class="grid grid-cols-3 gap-4 text-center">
                <div class="rounded-lg bg-ink-50 p-4">
                    <div class="text-2xl font-bold text-ink-900"><?php echo e(number_format($dataSummary['audit_logs'])); ?></div>
                    <div class="text-xs text-ink-500">Audit Log Entries</div>
                </div>
                <div class="rounded-lg bg-ink-50 p-4">
                    <div class="text-2xl font-bold text-ink-900"><?php echo e(number_format($dataSummary['login_attempts'])); ?></div>
                    <div class="text-xs text-ink-500">Login Attempts</div>
                </div>
                <div class="rounded-lg bg-ink-50 p-4">
                    <div class="text-2xl font-bold text-ink-900"><?php echo e(number_format($dataSummary['security_events'])); ?></div>
                    <div class="text-xs text-ink-500">Security Events</div>
                </div>
            </div>
        </div>

        
        <div class="card">
            <h2 class="mb-2 text-lg font-bold text-ink-900">Step 1: Export Your Data</h2>
            <p class="mb-4 text-sm text-ink-500">
                Before deleting your account, you can download a copy of all your personal data.
            </p>
            <form method="POST" action="<?php echo e(route('privacy.export')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn-secondary">
                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    Download My Data (JSON)
                </button>
            </form>
        </div>

        
        <div class="card border-red-200 dark:border-red-800">
            <h2 class="mb-2 text-lg font-bold text-red-800 dark:text-red-200">Step 2: Delete Your Account</h2>
            <p class="mb-4 text-sm text-ink-500">
                Enter your password and type <code class="rounded bg-ink-100 px-1 py-0.5 text-xs">DELETE_MY_ACCOUNT</code> to confirm.
            </p>
            
            <form method="POST" action="<?php echo e(route('privacy.delete')); ?>" onsubmit="return confirm('Are you absolutely sure? This cannot be undone.')">
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
                
                <div class="space-y-4">
                    <div>
                        <label for="password" class="form-label">Your Password</label>
                        <input type="password" name="password" id="password" required class="input" autocomplete="current-password">
                        <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    
                    <div>
                        <label for="confirmation" class="form-label">Type <code class="text-xs">DELETE_MY_ACCOUNT</code> to confirm</label>
                        <input type="text" name="confirmation" id="confirmation" required class="input" placeholder="DELETE_MY_ACCOUNT">
                    </div>
                </div>
                
                <div class="mt-6">
                    <button type="submit" class="btn-danger">
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                        Delete My Account Permanently
                    </button>
                </div>
            </form>
        </div>

        
        <div class="text-center">
            <a href="<?php echo e(route('dashboard')); ?>" class="text-sm text-ink-500 hover:text-ink-700">
                ← Back to Dashboard
            </a>
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
<?php /**PATH C:\MY_ERP\resources\views/auth/data-deletion.blade.php ENDPATH**/ ?>