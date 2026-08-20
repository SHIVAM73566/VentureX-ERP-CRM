<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Installation Guide','breadcrumbs' => [['label' => 'Support', 'url' => route('support.index')], ['label' => 'Installation Guide']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Installation Guide'),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Support', 'url' => route('support.index')], ['label' => 'Installation Guide']])]); ?>

    <div class="mx-auto max-w-3xl space-y-6">

        
        <div class="card space-y-4">
            <h2 class="text-lg font-bold text-ink-900">Video Demo & Installation Guide</h2>
            <p class="text-sm text-ink-600">Watch our complete video tutorial for a step-by-step walkthrough of installation and setup.</p>
            <a href="https://drive.google.com/drive/folders/16El5xzakJam5sLbKB-V6DWgZ0hQLRahh?usp=sharing" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-700">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Watch Demo & Installation Video
            </a>
        </div>

        
        <div class="card space-y-4">
            <h2 class="text-lg font-bold text-ink-900">System Requirements</h2>
            <p class="text-sm text-ink-600">Before installing VentureX ERP & CRM, ensure your server meets the following requirements.</p>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-ink-200 text-xs uppercase tracking-wide text-ink-400">
                            <th class="py-2 pr-4">Requirement</th>
                            <th class="py-2 pr-4">Minimum</th>
                            <th class="py-2">Recommended</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-ink-100">
                            <td class="py-2.5 pr-4 font-medium text-ink-800">PHP Version</td>
                            <td class="py-2.5 pr-4">8.2</td>
                            <td class="py-2.5">8.3+</td>
                        </tr>
                        <tr class="border-b border-ink-100">
                            <td class="py-2.5 pr-4 font-medium text-ink-800">PHP Extensions</td>
                            <td class="py-2.5 pr-4" colspan="2">curl, dom, fileinfo, hash, mbstring, openssl, pdo, pdo_mysql, tokenizer, xml, zip, bcmath, gd</td>
                        </tr>
                        <tr class="border-b border-ink-100">
                            <td class="py-2.5 pr-4 font-medium text-ink-800">Database</td>
                            <td class="py-2.5 pr-4">MySQL 8.0</td>
                            <td class="py-2.5">MySQL 8.0+ / MariaDB 10.6+</td>
                        </tr>
                        <tr class="border-b border-ink-100">
                            <td class="py-2.5 pr-4 font-medium text-ink-800">Web Server</td>
                            <td class="py-2.5 pr-4">Apache 2.4 / Nginx 1.18</td>
                            <td class="py-2.5">Nginx with PHP-FPM</td>
                        </tr>
                        <tr class="border-b border-ink-100">
                            <td class="py-2.5 pr-4 font-medium text-ink-800">Composer</td>
                            <td class="py-2.5 pr-4">2.0</td>
                            <td class="py-2.5">Latest 2.x</td>
                        </tr>
                        <tr class="border-b border-ink-100">
                            <td class="py-2.5 pr-4 font-medium text-ink-800">Node.js</td>
                            <td class="py-2.5 pr-4">18.x</td>
                            <td class="py-2.5">20.x LTS</td>
                        </tr>
                        <tr class="border-b border-ink-100">
                            <td class="py-2.5 pr-4 font-medium text-ink-800">Memory</td>
                            <td class="py-2.5 pr-4">512 MB</td>
                            <td class="py-2.5">2 GB+</td>
                        </tr>
                        <tr>
                            <td class="py-2.5 pr-4 font-medium text-ink-800">Disk Space</td>
                            <td class="py-2.5 pr-4">500 MB</td>
                            <td class="py-2.5">2 GB+ (including uploads)</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        
        <div class="card space-y-4">
            <h2 class="text-lg font-bold text-ink-900">Download & Install</h2>
            <p class="text-sm text-ink-600">Follow these steps to download and set up VentureX ERP & CRM on your server.</p>

            <div class="space-y-5">
                <div class="flex gap-4">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-navy-600 text-xs font-bold text-white">1</span>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-ink-800">Clone the Repository</p>
                        <p class="mt-1 text-sm text-ink-600">Download the latest release from our Git repository.</p>
                        <code class="mt-2 block rounded-lg bg-ink-900 px-4 py-2.5 text-xs text-emerald-400">git clone https://github.com/VentureX-ERP/VentureX-ERP.git<br>cd VentureX-ERP</code>
                    </div>
                </div>

                <div class="flex gap-4">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-navy-600 text-xs font-bold text-white">2</span>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-ink-800">Install PHP Dependencies</p>
                        <p class="mt-1 text-sm text-ink-600">Use Composer to install all required PHP packages.</p>
                        <code class="mt-2 block rounded-lg bg-ink-900 px-4 py-2.5 text-xs text-emerald-400">composer install --optimize-autoloader --no-dev</code>
                    </div>
                </div>

                <div class="flex gap-4">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-navy-600 text-xs font-bold text-white">3</span>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-ink-800">Install Front-End Dependencies</p>
                        <p class="mt-1 text-sm text-ink-600">Install Node packages and build production assets.</p>
                        <code class="mt-2 block rounded-lg bg-ink-900 px-4 py-2.5 text-xs text-emerald-400">npm install && npm run build</code>
                    </div>
                </div>

                <div class="flex gap-4">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-navy-600 text-xs font-bold text-white">4</span>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-ink-800">Configure Environment</p>
                        <p class="mt-1 text-sm text-ink-600">Copy the example environment file and generate an application key.</p>
                        <code class="mt-2 block rounded-lg bg-ink-900 px-4 py-2.5 text-xs text-emerald-400">cp .env.example .env<br>php artisan key:generate</code>
                    </div>
                </div>

                <div class="flex gap-4">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-navy-600 text-xs font-bold text-white">5</span>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-ink-800">Configure Database</p>
                        <p class="mt-1 text-sm text-ink-600">Edit your <code class="rounded bg-ink-100 px-1.5 py-0.5 text-xs">.env</code> file with your database credentials, then run migrations.</p>
                        <code class="mt-2 block rounded-lg bg-ink-900 px-4 py-2.5 text-xs text-emerald-400">php artisan migrate --seed</code>
                        <div class="mt-2 rounded-lg bg-amber-50 p-3 text-xs text-amber-700">
                            <strong>Note:</strong> The seed command creates demo users. Check the database seeder for current credentials. Always change passwords before production use.
                        </div>
                    </div>
                </div>

                <div class="flex gap-4">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-navy-600 text-xs font-bold text-white">6</span>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-ink-800">Set Permissions</p>
                        <p class="mt-1 text-sm text-ink-600">Ensure the web server can write to storage and cache directories.</p>
                        <code class="mt-2 block rounded-lg bg-ink-900 px-4 py-2.5 text-xs text-emerald-400">chmod -R 775 storage bootstrap/cache<br>chown -R www-data:www-data storage bootstrap/cache</code>
                    </div>
                </div>

                <div class="flex gap-4">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-navy-600 text-xs font-bold text-white">7</span>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-ink-800">Create Scheduler Cron Job</p>
                        <p class="mt-1 text-sm text-ink-600">Add the following cron entry to your server to enable scheduled tasks.</p>
                        <code class="mt-2 block rounded-lg bg-ink-900 px-4 py-2.5 text-xs text-emerald-400">* * * * * cd /path/to/VentureX-ERP && php artisan schedule:run >> /dev/null 2>&1</code>
                    </div>
                </div>

                <div class="flex gap-4">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-navy-600 text-xs font-bold text-white">8</span>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-ink-800">Verify Installation</p>
                        <p class="mt-1 text-sm text-ink-600">Start the server and verify everything is working.</p>
                        <code class="mt-2 block rounded-lg bg-ink-900 px-4 py-2.5 text-xs text-emerald-400">php artisan serve</code>
                        <p class="mt-1 text-sm text-ink-600">Visit <code class="rounded bg-ink-100 px-1.5 py-0.5 text-xs">http://localhost:8000</code> in your browser.</p>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="card space-y-4">
            <h2 class="text-lg font-bold text-ink-900">Production Deployment Checklist</h2>
            <div class="space-y-2">
                <label class="flex items-start gap-3 text-sm text-ink-700">
                    <input type="checkbox" class="mt-0.5 rounded border-ink-300 text-navy-600 focus:ring-navy-500">
                    Set <code class="rounded bg-ink-100 px-1 text-xs">APP_ENV=production</code> in .env
                </label>
                <label class="flex items-start gap-3 text-sm text-ink-700">
                    <input type="checkbox" class="mt-0.5 rounded border-ink-300 text-navy-600 focus:ring-navy-500">
                    Set <code class="rounded bg-ink-100 px-1 text-xs">APP_DEBUG=false</code> in .env
                </label>
                <label class="flex items-start gap-3 text-sm text-ink-700">
                    <input type="checkbox" class="mt-0.5 rounded border-ink-300 text-navy-600 focus:ring-navy-500">
                    Configure a proper mail server for notifications
                </label>
                <label class="flex items-start gap-3 text-sm text-ink-700">
                    <input type="checkbox" class="mt-0.5 rounded border-ink-300 text-navy-600 focus:ring-navy-500">
                    Set up SSL/TLS certificate (HTTPS required in production)
                </label>
                <label class="flex items-start gap-3 text-sm text-ink-700">
                    <input type="checkbox" class="mt-0.5 rounded border-ink-300 text-navy-600 focus:ring-navy-500">
                    Run <code class="rounded bg-ink-100 px-1 text-xs">php artisan config:cache && php artisan route:cache && php artisan view:cache</code>
                </label>
                <label class="flex items-start gap-3 text-sm text-ink-700">
                    <input type="checkbox" class="mt-0.5 rounded border-ink-300 text-navy-600 focus:ring-navy-500">
                    Set up queue workers for background jobs
                </label>
                <label class="flex items-start gap-3 text-sm text-ink-700">
                    <input type="checkbox" class="mt-0.5 rounded border-ink-300 text-navy-600 focus:ring-navy-500">
                    Configure backup strategy for database and files
                </label>
                <label class="flex items-start gap-3 text-sm text-ink-700">
                    <input type="checkbox" class="mt-0.5 rounded border-ink-300 text-navy-600 focus:ring-navy-500">
                    Change default admin password
                </label>
            </div>
        </div>

        
        <div class="card space-y-4">
            <h2 class="text-lg font-bold text-ink-900">Installation Troubleshooting</h2>
            <div class="space-y-3">
                <div class="rounded-lg border border-ink-200 p-4">
                    <p class="text-sm font-semibold text-ink-800">"Class not found" errors</p>
                    <p class="mt-1 text-sm text-ink-600">Run <code class="rounded bg-ink-100 px-1.5 py-0.5 text-xs">composer dump-autoload</code> to regenerate the autoloader.</p>
                </div>
                <div class="rounded-lg border border-ink-200 p-4">
                    <p class="text-sm font-semibold text-ink-800">"Permission denied" errors</p>
                    <p class="mt-1 text-sm text-ink-600">Ensure your web server user (www-data, nginx, apache) owns the <code class="rounded bg-ink-100 px-1.5 py-0.5 text-xs">storage</code> and <code class="rounded bg-ink-100 px-1.5 py-0.5 text-xs">bootstrap/cache</code> directories.</p>
                </div>
                <div class="rounded-lg border border-ink-200 p-4">
                    <p class="text-sm font-semibold text-ink-800">"SQLSTATE connection refused"</p>
                    <p class="mt-1 text-sm text-ink-600">Verify your database is running and the credentials in <code class="rounded bg-ink-100 px-1.5 py-0.5 text-xs">.env</code> are correct. Run <code class="rounded bg-ink-100 px-1.5 py-0.5 text-xs">php artisan db:show</code> to test the connection.</p>
                </div>
                <div class="rounded-lg border border-ink-200 p-4">
                    <p class="text-sm font-semibold text-ink-800">Vite assets not loading in production</p>
                    <p class="mt-1 text-sm text-ink-600">Run <code class="rounded bg-ink-100 px-1.5 py-0.5 text-xs">npm run build</code> and ensure <code class="rounded bg-ink-100 px-1.5 py-0.5 text-xs">public/build</code> directory exists and is populated.</p>
                </div>
            </div>
        </div>

        <div class="text-center">
            <p class="text-sm text-ink-500">Need help with installation?</p>
            <a href="<?php echo e(route('support.contact')); ?>" class="mt-1 inline-flex items-center gap-1 text-sm font-medium text-navy-600 hover:text-navy-500">
                Contact our support team
                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
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
<?php /**PATH C:\MY_ERP\resources\views\support\installation.blade.php ENDPATH**/ ?>