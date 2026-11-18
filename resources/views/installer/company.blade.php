@extends('installer.layout')

@section('title', 'Company & Super Admin')

@section('content')
    <div class="card overflow-hidden">
        <div class="px-6 py-5 border-b border-ink-100 dark:border-ink-800">
            <h2 class="text-lg font-bold text-ink-900 dark:text-ink-50">Company &amp; Super Admin</h2>
            <p class="mt-1 text-sm text-ink-500 dark:text-ink-400">These details are used exactly as entered. The company name is never derived from the admin name.</p>
        </div>

        <form method="POST" action="{{ route('installer.company.store') }}" enctype="multipart/form-data" class="p-6 space-y-5" id="company-form">
            @csrf

            @if (($mode ?? 'production') === 'demo')
                <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-800 dark:border-amber-800 dark:bg-amber-500/10 dark:text-amber-300">
                    Demo mode is selected. Sample records and publicly known demo accounts will be created. Do not use demo mode for live business data.
                </div>
            @endif

            @error('company_name')
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-500/10 dark:text-red-400">{{ $message }}</div>
            @enderror

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="company_name" class="label">Company Name</label>
                    <input id="company_name" name="company_name" type="text" value="{{ old('company_name') }}" class="input" placeholder="Your Legal Business Name" required>
                </div>
                <div>
                    <label for="currency_code" class="label">Default Currency</label>
                    <input id="currency_code" name="currency_code" type="text" maxlength="3" value="{{ old('currency_code', 'USD') }}" class="input" placeholder="USD" required>
                    <p class="mt-1 text-xs text-ink-400 dark:text-ink-500">Three-letter ISO code, for example USD, EUR or INR.</p>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="tax_id" class="label">Tax ID <span class="font-normal text-ink-400">(optional)</span></label>
                    <input id="tax_id" name="tax_id" type="text" value="{{ old('tax_id') }}" class="input" autocomplete="off">
                </div>
                <div>
                    <label for="logo" class="label">Company Logo <span class="font-normal text-ink-400">(optional)</span></label>
                    <input id="logo" name="logo" type="file" accept="image/png,image/jpeg,image/webp" class="input">
                    <p class="mt-1 text-xs text-ink-400 dark:text-ink-500">PNG, JPG or WEBP up to 5 MB. Stored in private application storage.</p>
                </div>
            </div>

            <div class="border-t border-ink-100 pt-5 dark:border-ink-800">
                <h3 class="text-sm font-bold text-ink-800 dark:text-ink-100">Super Administrator</h3>
                <p class="mt-1 text-xs text-ink-500 dark:text-ink-400">This account receives the super_admin role.</p>

                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="admin_name" class="label">Full Name</label>
                        <input id="admin_name" name="admin_name" type="text" value="{{ old('admin_name') }}" class="input" required autofocus>
                    </div>
                    <div>
                        <label for="admin_email" class="label">Email Address</label>
                        <input id="admin_email" name="admin_email" type="email" value="{{ old('admin_email') }}" class="input" autocomplete="username" required>
                    </div>
                    <div>
                        <label for="admin_password" class="label">Password</label>
                        <input id="admin_password" name="admin_password" type="password" class="input" autocomplete="new-password" minlength="10" required>
                        <div class="mt-2 space-y-1" id="password-strength">
                            <div class="flex items-center gap-2 text-xs" data-check="length"><span class="h-1.5 w-1.5 rounded-full bg-ink-300"></span><span class="text-ink-400">At least 10 characters</span></div>
                            <div class="flex items-center gap-2 text-xs" data-check="upper"><span class="h-1.5 w-1.5 rounded-full bg-ink-300"></span><span class="text-ink-400">One uppercase letter</span></div>
                            <div class="flex items-center gap-2 text-xs" data-check="lower"><span class="h-1.5 w-1.5 rounded-full bg-ink-300"></span><span class="text-ink-400">One lowercase letter</span></div>
                            <div class="flex items-center gap-2 text-xs" data-check="digit"><span class="h-1.5 w-1.5 rounded-full bg-ink-300"></span><span class="text-ink-400">One number</span></div>
                            <div class="flex items-center gap-2 text-xs" data-check="special"><span class="h-1.5 w-1.5 rounded-full bg-ink-300"></span><span class="text-ink-400">One special character</span></div>
                        </div>
                    </div>
                    <div>
                        <label for="admin_password_confirmation" class="label">Confirm Password</label>
                        <input id="admin_password_confirmation" name="admin_password_confirmation" type="password" class="input" autocomplete="new-password" required>
                    </div>
                </div>

                @error('admin_password')
                    <p class="mt-2 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
                @error('admin_email')
                    <p class="mt-2 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('installer.environment') }}" class="btn-secondary">&larr; Back</a>
                <button type="submit" class="btn-accent">Review Install &rarr;</button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const password = document.getElementById('admin_password');
            if (!password) return;

            const checks = {
                length: (value) => value.length >= 10,
                upper: (value) => /[A-Z]/.test(value),
                lower: (value) => /[a-z]/.test(value),
                digit: (value) => /\d/.test(value),
                special: (value) => /[^A-Za-z0-9]/.test(value),
            };

            password.addEventListener('input', function () {
                Object.entries(checks).forEach(([name, test]) => {
                    const row = document.querySelector('[data-check="' + name + '"]');
                    if (!row) return;
                    const passed = test(password.value);
                    row.querySelector('span:first-child').className = passed
                        ? 'h-1.5 w-1.5 rounded-full bg-emerald-500'
                        : 'h-1.5 w-1.5 rounded-full bg-ink-300';
                    row.querySelector('span:last-child').className = passed
                        ? 'text-emerald-600 dark:text-emerald-400'
                        : 'text-ink-400 dark:text-ink-500';
                });
            });
        });
    </script>
@endsection
