<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Register â€” <?php echo e(config('app.name', 'VentureX ERP & CRM')); ?></title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="h-full bg-ink-100">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="w-full max-w-lg" x-data="registerApp()">

            <!-- Logo -->
            <div class="mb-8 text-center">
                <h1 class="text-3xl font-bold text-ink-900"><?php echo e(config('app.name')); ?></h1>
                <p class="mt-2 text-sm text-ink-500">Create Your Account</p>
            </div>

            <!-- Step Indicator -->
            <div class="mb-8 flex items-center justify-center space-x-4">
                <template x-for="s in [1,2,3]" :key="s">
                    <div class="flex items-center">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold"
                             :class="step > s ? 'bg-accent-600 text-white' : step === s ? 'bg-accent-600 text-white' : 'bg-ink-50 text-ink-500 border border-ink-200'">
                            <span x-show="step <= s" x-text="s"></span>
                            <span x-show="step > s">&#10003;</span>
                        </div>
                        <div x-show="s < 3" class="mx-2 h-0.5 w-12" :class="step > s ? 'bg-accent-600' : 'bg-ink-200'"></div>
                    </div>
                </template>
            </div>

            <!-- Step Labels -->
            <div class="mb-6 flex justify-between px-2 text-xs text-ink-500">
                <span :class="step >= 1 ? 'text-accent-600' : ''">Email</span>
                <span :class="step >= 2 ? 'text-accent-600' : ''">Phone</span>
                <span :class="step >= 3 ? 'text-accent-600' : ''">Identity</span>
            </div>

            <!-- STEP 1: Email + Password -->
            <div x-show="step === 1" class="rounded-2xl border border-ink-200 bg-white p-8 shadow-xl">
                <h2 class="mb-6 text-xl font-bold text-ink-900">Step 1 &mdash; Email Verification</h2>

                <div class="space-y-4">
                    <div>
                        <label class="label">Full Name</label>
                        <input type="text" x-model="form.name" class="input" placeholder="John Doe">
                    </div>
                    <div>
                        <label class="label">Email Address</label>
                        <input type="email" x-model="form.email" class="input" placeholder="john@company.com">
                    </div>
                    <div>
                        <label class="label">Password</label>
                        <input type="password" x-model="form.password" class="input" placeholder="Min 8 characters">
                    </div>
                    <div>
                        <label class="label">Confirm Password</label>
                        <input type="password" x-model="form.password_confirmation" class="input" placeholder="Repeat password">
                    </div>
                </div>

                <button @click="submitStep1()"
                        class="btn-primary mt-6 w-full justify-center py-2.5"
                        :disabled="loading">
                    <span x-show="!loading">Send Verification Code</span>
                    <span x-show="loading">Sending...</span>
                </button>
            </div>

            <!-- STEP 2: Email OTP + Phone -->
            <div x-show="step === 2" class="rounded-2xl border border-ink-200 bg-white p-8 shadow-xl">
                <h2 class="mb-2 text-xl font-bold text-ink-900">Step 2 &mdash; Phone Verification</h2>
                <p class="mb-6 text-sm text-ink-500" x-text="'Code sent to ' + form.email"></p>

                <div class="space-y-4">
                    <div>
                        <label class="label">Email OTP Code</label>
                        <input type="text" x-model="otp" maxlength="6" class="input text-center text-2xl tracking-widest" placeholder="000000">
                        <p class="mt-1 text-xs text-ink-400">Check your email for the verification code</p>
                    </div>
                    <div>
                        <label class="label">Phone Number</label>
                        <input type="tel" x-model="form.phone" class="input" placeholder="+1 234 567 8900">
                    </div>
                </div>

                <div class="mt-6 flex space-x-3">
                    <button @click="step = 1" class="flex-1 rounded-lg border border-ink-200 bg-white py-3 text-sm font-bold text-ink-700 transition hover:bg-ink-50">Back</button>
                    <button @click="submitStep2()" class="btn-primary flex-1 justify-center py-2.5" :disabled="loading">Verify & Continue</button>
                </div>
            </div>

            <!-- STEP 3: Phone OTP + Selfie -->
            <div x-show="step === 3" class="rounded-2xl border border-ink-200 bg-white p-8 shadow-xl">
                <h2 class="mb-2 text-xl font-bold text-ink-900">Step 3 &mdash; Identity Verification</h2>
                <p class="mb-6 text-sm text-ink-500">Upload a selfie to verify your identity</p>

                <div class="space-y-4">
                    <div>
                        <label class="label">Phone OTP Code</label>
                        <input type="text" x-model="phoneOtp" maxlength="6" class="input text-center text-2xl tracking-widest" placeholder="000000">
                    </div>

                    <!-- Selfie Upload -->
                    <div class="rounded-xl border-2 border-dashed border-ink-200 bg-ink-50 p-6 text-center">
                        <div x-show="!selfiePreview">
                            <svg class="mx-auto h-16 w-16 text-ink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                            </svg>
                            <p class="mt-2 text-sm text-ink-500">Click to take selfie</p>
                            <input type="file" accept="image/*" capture="user" @change="previewSelfie($event)" class="hidden" x-ref="selfieInput">
                            <button @click="$refs.selfieInput.click()" class="mt-3 rounded-lg border border-ink-200 bg-white px-4 py-2 text-sm font-medium text-ink-700 transition hover:bg-ink-50">Choose Photo</button>
                        </div>
                        <div x-show="selfiePreview">
                            <img :src="selfiePreview" class="mx-auto h-40 w-40 rounded-full border-4 border-accent-600 object-cover">
                            <button @click="selfiePreview = null; selfieFile = null" class="mt-2 text-sm text-red-600 hover:text-red-700">Retake</button>
                        </div>
                    </div>

                    <!-- Security Features -->
                    <div class="rounded-lg border border-ink-200 bg-ink-50 p-4">
                        <h3 class="mb-2 text-sm font-bold text-ink-700">Security Features</h3>
                        <ul class="space-y-1 text-xs text-ink-500">
                            <li>&#10003; Liveness detection &mdash; prevents photo spoofing</li>
                            <li>&#10003; Device fingerprinting &mdash; tracks trusted devices</li>
                            <li>&#10003; 3-step verification &mdash; email, phone, identity</li>
                            <li>&#10003; Encrypted storage &mdash; all data encrypted at rest</li>
                        </ul>
                    </div>
                </div>

                <div class="mt-6 flex space-x-3">
                    <button @click="step = 2" class="flex-1 rounded-lg border border-ink-200 bg-white py-3 text-sm font-bold text-ink-700 transition hover:bg-ink-50">Back</button>
                    <button @click="submitStep3()" class="flex-1 rounded-lg bg-accent-600 py-3 text-sm font-bold text-white transition hover:bg-accent-700" :disabled="loading">Complete Registration</button>
                </div>
            </div>

            <!-- Error Display -->
            <div x-show="error" class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" x-text="error"></div>
        </div>
    </div>

    <script>
    function registerApp() {
        return {
            step: 1,
            loading: false,
            error: null,
            otp: '',
            phoneOtp: '',
            selfiePreview: null,
            selfieFile: null,
            form: { name: '', email: '', password: '', password_confirmation: '', phone: '' },

            async submitStep1() {
                this.loading = true;
                this.error = null;
                try {
                    const res = await fetch('/auth/register/step1', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify(this.form)
                    });
                    const data = await res.json();
                    if (data.message) { this.step = 2; }
                    else { this.error = data.errors ? Object.values(data.errors).join(', ') : 'Failed'; }
                } catch(e) { this.error = 'Network error'; }
                this.loading = false;
            },

            async submitStep2() {
                this.loading = true;
                this.error = null;
                try {
                    const res = await fetch('/auth/register/step2', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify({ otp: this.otp, phone: this.form.phone })
                    });
                    const data = await res.json();
                    if (data.step === 2) { this.step = 3; }
                    else { this.error = data.errors ? Object.values(data.errors).join(', ') : 'Invalid code'; }
                } catch(e) { this.error = 'Network error'; }
                this.loading = false;
            },

            previewSelfie(event) {
                const file = event.target.files[0];
                if (file) { this.selfieFile = file; this.selfiePreview = URL.createObjectURL(file); }
            },

            async submitStep3() {
                this.loading = true;
                this.error = null;
                try {
                    const formData = new FormData();
                    formData.append('phone_otp', this.phoneOtp);
                    if (this.selfieFile) formData.append('selfie', this.selfieFile);

                    const res = await fetch('/auth/register/step3', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: formData
                    });
                    const data = await res.json();
                    if (data.redirect) { window.location.href = data.redirect; }
                    else { this.error = data.errors ? Object.values(data.errors).join(', ') : 'Failed'; }
                } catch(e) { this.error = 'Network error'; }
                this.loading = false;
            }
        }
    }
    </script>
</body>
</html>
<?php /**PATH C:\MY_ERP\resources\views\auth\register-steps.blade.php ENDPATH**/ ?>