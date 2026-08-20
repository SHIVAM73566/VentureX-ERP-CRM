<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'New Import','breadcrumbs' => [['label' => 'Administration', 'url' => route('admin.imports.index')], ['label' => 'New Import']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('New Import'),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Administration', 'url' => route('admin.imports.index')], ['label' => 'New Import']])]); ?>

    <div class="mx-auto max-w-4xl space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-ink-900">New Data Import</h1>
            <p class="mt-1 text-sm text-ink-500">Upload a CSV or JSON file to import business data into VentureX ERP & CRM.</p>
        </div>

        <div class="rounded-xl border border-ink-200 bg-white p-6">
            <form action="<?php echo e(route('admin.imports.upload')); ?>" method="POST" enctype="multipart/form-data" id="upload-form">
                <?php echo csrf_field(); ?>

                <div class="mb-6">
                    <label class="mb-2 block text-sm font-medium text-ink-700">Select File</label>
                    <div id="drop-zone" class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-ink-300 bg-ink-50 p-10 text-center transition hover:border-indigo-400 hover:bg-indigo-50/50">
                        <svg class="mb-3 h-10 w-10 text-ink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        <p class="text-sm font-medium text-ink-700">Drag & drop your file here</p>
                        <p class="mt-1 text-xs text-ink-500">or click to browse</p>
                        <p class="mt-2 text-xs text-ink-400">Supports CSV, JSON, TXT (max 50MB)</p>
                        <input type="file" name="file" id="file-input" class="hidden" accept=".csv,.json,.txt">
                    </div>
                    <div id="file-info" class="mt-3 hidden">
                        <div class="flex items-center gap-3 rounded-lg bg-green-50 p-3">
                            <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span id="file-name" class="text-sm font-medium text-green-800"></span>
                            <span id="file-size" class="text-xs text-green-600"></span>
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="mb-2 block text-sm font-medium text-ink-700">Or select a saved template</label>
                    <select name="template_id" class="input">
                        <option value="">Upload a new file (auto-detect)</option>
                        <?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $template): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($template->id); ?>"><?php echo e($template->name); ?> &mdash; <?php echo e(ucfirst($template->destination)); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <?php $__errorArgs = ['file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-700"><?php echo e($message); ?></div>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                <button type="submit" id="submit-btn" class="btn-primary w-full" disabled>
                    Upload &amp; Continue
                </button>
            </form>
        </div>

        <div class="rounded-xl border border-ink-200 bg-white p-6">
            <h3 class="mb-3 text-sm font-medium text-ink-700">Supported Formats</h3>
            <div class="grid grid-cols-3 gap-4 text-center">
                <div class="rounded-lg border border-ink-200 p-3">
                    <div class="text-lg font-bold text-ink-900">CSV</div>
                    <div class="text-xs text-ink-500">Comma-separated values</div>
                </div>
                <div class="rounded-lg border border-ink-200 p-3">
                    <div class="text-lg font-bold text-ink-900">JSON</div>
                    <div class="text-xs text-ink-500">Structured data format</div>
                </div>
                <div class="rounded-lg border border-ink-200 p-3">
                    <div class="text-lg font-bold text-ink-900">TXT</div>
                    <div class="text-xs text-ink-500">Tab/comma delimited</div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('drop-zone').addEventListener('click', () => document.getElementById('file-input').click());
    document.getElementById('drop-zone').addEventListener('dragover', (e) => { e.preventDefault(); e.currentTarget.classList.add('border-indigo-400', 'bg-indigo-50'); });
    document.getElementById('drop-zone').addEventListener('dragleave', (e) => { e.currentTarget.classList.remove('border-indigo-400', 'bg-indigo-50'); });
    document.getElementById('drop-zone').addEventListener('drop', (e) => { e.preventDefault(); e.currentTarget.classList.remove('border-indigo-400', 'bg-indigo-50'); document.getElementById('file-input').files = e.dataTransfer.files; showFile(); });
    document.getElementById('file-input').addEventListener('change', showFile);
    function showFile() {
        const file = document.getElementById('file-input').files[0];
        if (file) {
            document.getElementById('file-name').textContent = file.name;
            document.getElementById('file-size').textContent = (file.size / 1024).toFixed(1) + ' KB';
            document.getElementById('file-info').classList.remove('hidden');
            document.getElementById('submit-btn').disabled = false;
        }
    }
    </script>

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
<?php /**PATH C:\MY_ERP\resources\views/admin/imports/create.blade.php ENDPATH**/ ?>