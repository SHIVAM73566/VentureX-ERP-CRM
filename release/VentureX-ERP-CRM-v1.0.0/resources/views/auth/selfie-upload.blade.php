<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Upload Selfie — {{ config('app.name', 'VentureX ERP & CRM') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-ink-100" x-data="selfieApp()">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="w-full max-w-md rounded-2xl border border-ink-200 bg-white p-8 shadow-xl text-center">

            <h1 class="text-2xl font-bold text-ink-900">Identity Verification</h1>
            <p class="mt-2 text-sm text-ink-500">Take a clear selfie for liveness detection</p>

            <!-- Selfie Capture -->
            <div class="relative mt-8 mb-6">
                <div x-show="!photoSrc" class="mx-auto flex h-64 w-64 items-center justify-center rounded-full border-4 border-dashed border-ink-200 bg-ink-50">
                    <svg class="h-20 w-20 text-ink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                    </svg>
                </div>
                <div x-show="photoSrc" class="mx-auto h-64 w-64 overflow-hidden rounded-full border-4 border-accent-600">
                    <img :src="photoSrc" class="h-full w-full object-cover">
                </div>
                <!-- Hidden canvas for camera -->
                <video x-ref="video" class="hidden" autoplay playsinline></video>
                <canvas x-ref="canvas" class="hidden"></canvas>
            </div>

            <!-- Buttons -->
            <div class="space-y-3">
                <div x-show="!photoSrc && !cameraActive">
                    <button @click="startCamera()" class="btn-primary w-full justify-center py-2.5">
                        Open Camera
                    </button>
                    <p class="mt-2 text-xs text-ink-400">Or choose from gallery</p>
                    <input type="file" accept="image/*" @change="handleFile($event)" class="hidden" x-ref="fileInput">
                    <button @click="$refs.fileInput.click()" class="mt-2 text-sm text-accent-600 hover:text-accent-700">Choose Photo</button>
                </div>

                <div x-show="cameraActive && !photoSrc">
                    <button @click="takePhoto()" class="w-full rounded-lg bg-red-600 py-3 text-sm font-bold text-white transition hover:bg-red-700">
                        Take Photo
                    </button>
                    <button @click="stopCamera()" class="mt-2 text-sm text-ink-500 hover:text-ink-700">Cancel</button>
                </div>

                <div x-show="photoSrc">
                    <button @click="submit()" class="btn-primary w-full justify-center py-2.5" :disabled="loading">
                        <span x-show="!loading">Submit for Verification</span>
                        <span x-show="loading">Processing...</span>
                    </button>
                    <button @click="retake()" class="mt-2 text-sm text-ink-500 hover:text-ink-700">Retake</button>
                </div>
            </div>

            <!-- Liveness Tips -->
            <div class="mt-8 rounded-xl border border-ink-200 bg-ink-50 p-4 text-left">
                <h3 class="mb-2 text-sm font-bold text-ink-700">Photo Tips</h3>
                <ul class="space-y-1 text-xs text-ink-500">
                    <li>&#10003; Face the camera directly</li>
                    <li>&#10003; Ensure good lighting</li>
                    <li>&#10003; Remove sunglasses or hats</li>
                    <li>&#10003; Keep a neutral expression</li>
                    <li>&#10003; Minimum 200x200 pixels</li>
                </ul>
            </div>

            <!-- Security Badge -->
            <div class="mt-4 flex items-center justify-center space-x-2 text-xs text-ink-400">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/></svg>
                <span>Encrypted & stored securely</span>
            </div>
        </div>
    </div>

    <script>
    function selfieApp() {
        return {
            photoSrc: null,
            photoFile: null,
            cameraActive: false,
            loading: false,

            async startCamera() {
                try {
                    const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: 640, height: 480 } });
                    this.$refs.video.srcObject = stream;
                    this.cameraActive = true;
                } catch(e) { alert('Camera access denied. Please use file upload.'); }
            },

            stopCamera() {
                const stream = this.$refs.video.srcObject;
                if (stream) stream.getTracks().forEach(t => t.stop());
                this.cameraActive = false;
            },

            takePhoto() {
                const canvas = this.$refs.canvas;
                const video = this.$refs.video;
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0);
                this.photoSrc = canvas.toDataURL('image/jpeg');
                this.stopCamera();

                canvas.toBlob(blob => { this.photoFile = new File([blob], 'selfie.jpg', { type: 'image/jpeg' }); }, 'image/jpeg');
            },

            handleFile(event) {
                const file = event.target.files[0];
                if (file) { this.photoFile = file; this.photoSrc = URL.createObjectURL(file); }
            },

            retake() { this.photoSrc = null; this.photoFile = null; },

            async submit() {
                this.loading = true;
                const formData = new FormData();
                formData.append('selfie', this.photoFile);
                try {
                    const res = await fetch('/auth/selfie/upload', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: formData
                    });
                    const data = await res.json();
                    if (data.redirect) window.location.href = data.redirect;
                    else if (data.success) window.location.href = '/dashboard';
                } catch(e) { alert('Upload failed'); }
                this.loading = false;
            }
        }
    }
    </script>
</body>
</html>
