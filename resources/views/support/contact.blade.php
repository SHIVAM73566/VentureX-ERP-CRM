<x-layouts.app
    :title="'Contact Support'"
    :breadcrumbs="[
        ['label' => 'Support Center', 'url' => route('support.index')],
        ['label' => 'Contact Support'],
    ]">

    <div class="mx-auto max-w-5xl space-y-6">

        {{-- Contact Form --}}
        <div class="card">
            <h2 class="mb-4 text-lg font-bold text-ink-900">Send Us a Message</h2>
            <p class="mb-6 text-sm text-ink-500">Fill out the form below and our support team will get back to you within 24 hours.</p>

            <form action="{{ route('support.contact.submit') }}" method="POST">
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="name" class="mb-1 block text-sm font-medium text-ink-700">Full Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name', auth()->user()->name ?? '') }}" required
                            class="w-full rounded-lg border border-ink-200 bg-white px-3 py-2.5 text-sm text-ink-700 placeholder-ink-400 focus:border-navy-500 focus:outline-none focus:ring-2 focus:ring-navy-500/20">
                    </div>
                    <div>
                        <label for="email" class="mb-1 block text-sm font-medium text-ink-700">Email Address</label>
                        <input type="email" id="email" name="email" value="{{ old('email', auth()->user()->email ?? '') }}" required
                            class="w-full rounded-lg border border-ink-200 bg-white px-3 py-2.5 text-sm text-ink-700 placeholder-ink-400 focus:border-navy-500 focus:outline-none focus:ring-2 focus:ring-navy-500/20">
                    </div>
                </div>
                <div class="mt-4">
                    <label for="subject" class="mb-1 block text-sm font-medium text-ink-700">Subject</label>
                    <input type="text" id="subject" name="subject" value="{{ old('subject') }}" required
                        class="w-full rounded-lg border border-ink-200 bg-white px-3 py-2.5 text-sm text-ink-700 placeholder-ink-400 focus:border-navy-500 focus:outline-none focus:ring-2 focus:ring-navy-500/20">
                </div>
                <div class="mt-4">
                    <label for="category" class="mb-1 block text-sm font-medium text-ink-700">Category</label>
                    <select id="category" name="category" required
                        class="w-full rounded-lg border border-ink-200 bg-white px-3 py-2.5 text-sm text-ink-700 focus:border-navy-500 focus:outline-none focus:ring-2 focus:ring-navy-500/20">
                        <option value="">Select a category...</option>
                        <option value="general" {{ old('category') === 'general' ? 'selected' : '' }}>General Inquiry</option>
                        <option value="billing" {{ old('category') === 'billing' ? 'selected' : '' }}>Billing</option>
                        <option value="technical" {{ old('category') === 'technical' ? 'selected' : '' }}>Technical Support</option>
                        <option value="other" {{ old('category') === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div class="mt-4">
                    <label for="message" class="mb-1 block text-sm font-medium text-ink-700">Message</label>
                    <textarea id="message" name="message" rows="5" required
                        class="w-full rounded-lg border border-ink-200 bg-white px-3 py-2.5 text-sm text-ink-700 placeholder-ink-400 focus:border-navy-500 focus:outline-none focus:ring-2 focus:ring-navy-500/20">{{ old('message') }}</textarea>
                </div>
                <div class="mt-4 flex justify-end">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-navy-600 px-5 py-2.5 text-sm font-semibold text-white shadow transition hover:bg-navy-700">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Send Message
                    </button>
                </div>
            </form>
        </div>

        {{-- Info Cards --}}
        <div class="grid gap-4 sm:grid-cols-2">

            {{-- Support Hours --}}
            <div class="card">
                <div class="flex items-start gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-navy-50 text-navy-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-ink-800">Support Hours</h3>
                        <p class="mt-1 text-sm text-ink-500">Monday â€“ Friday: 8:00 AM â€“ 8:00 PM (UTC)</p>
                        <p class="text-sm text-ink-500">Saturday: 9:00 AM â€“ 5:00 PM (UTC)</p>
                        <p class="text-sm text-ink-500">Sunday: Closed</p>
                        <p class="mt-2 text-xs text-ink-400">Urgent tickets are monitored 24/7.</p>
                    </div>
                </div>
            </div>

            {{-- Email Addresses --}}
            <div class="card">
                <div class="flex items-start gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-ink-800">Email Us</h3>
                        <p class="mt-1 text-sm text-ink-500">General support & questions:</p>
                        <p class="text-sm font-medium text-navy-600">support@venturexerp.com</p>
                        <p class="mt-2 text-sm text-ink-500">Sales & licensing inquiries:</p>
                        <p class="text-sm font-medium text-navy-600">sales@venturexerp.com</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-layouts.app>
