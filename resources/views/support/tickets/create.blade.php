<x-layouts.app
    :title="'Create Support Ticket'"
    :breadcrumbs="[
        ['label' => 'Support Center', 'url' => route('support.index')],
        ['label' => 'My Tickets', 'url' => route('support.tickets.index')],
        ['label' => 'Create Ticket'],
    ]">

    <div class="mx-auto max-w-3xl space-y-6">

        {{-- FAQ Reminder --}}
        <div class="flex items-start gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <p class="text-sm font-medium text-amber-800">Before submitting, check our FAQ</p>
                <p class="text-xs text-amber-700">Many common questions are already answered in our <a href="{{ route('support.faq') }}" class="font-semibold underline hover:text-amber-900">FAQ section</a> and <a href="{{ route('support.docs') }}" class="font-semibold underline hover:text-amber-900">Documentation</a>.</p>
            </div>
        </div>

        {{-- Create Ticket Form --}}
        <form action="{{ route('support.tickets.store') }}" method="POST">
            @csrf
            <div class="card space-y-5">
                <h2 class="text-lg font-bold text-ink-900">Ticket Details</h2>

                <div>
                    <label for="subject" class="mb-1 block text-sm font-medium text-ink-700">Subject *</label>
                    <input type="text" id="subject" name="subject" value="{{ old('subject') }}" required
                        placeholder="Brief summary of your issue"
                        class="w-full rounded-lg border border-ink-200 bg-white px-3 py-2.5 text-sm text-ink-700 placeholder-ink-400 focus:border-navy-500 focus:outline-none focus:ring-2 focus:ring-navy-500/20">
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="category" class="mb-1 block text-sm font-medium text-ink-700">Category *</label>
                        <select id="category" name="category" required
                            class="w-full rounded-lg border border-ink-200 bg-white px-3 py-2.5 text-sm text-ink-700 focus:border-navy-500 focus:outline-none focus:ring-2 focus:ring-navy-500/20">
                            <option value="">Select category...</option>
                            <option value="bug" {{ old('category') === 'bug' ? 'selected' : '' }}>Bug Report</option>
                            <option value="feature_request" {{ old('category') === 'feature_request' ? 'selected' : '' }}>Feature Request</option>
                            <option value="question" {{ old('category') === 'question' ? 'selected' : '' }}>Question</option>
                            <option value="installation" {{ old('category') === 'installation' ? 'selected' : '' }}>Installation Issue</option>
                            <option value="other" {{ old('category') === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div>
                        <label for="module" class="mb-1 block text-sm font-medium text-ink-700">Module</label>
                        <select id="module" name="module"
                            class="w-full rounded-lg border border-ink-200 bg-white px-3 py-2.5 text-sm text-ink-700 focus:border-navy-500 focus:outline-none focus:ring-2 focus:ring-navy-500/20">
                            <option value="">Select module...</option>
                            @foreach (['crm', 'sales', 'procurement', 'inventory', 'logistics', 'finance', 'ai', 'admin', 'auth', 'other'] as $mod)
                                <option value="{{ $mod }}" {{ old('module') === $mod ? 'selected' : '' }}>{{ ucfirst($mod) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label for="priority" class="mb-1 block text-sm font-medium text-ink-700">Priority *</label>
                    <select id="priority" name="priority" required
                        class="w-full rounded-lg border border-ink-200 bg-white px-3 py-2.5 text-sm text-ink-700 focus:border-navy-500 focus:outline-none focus:ring-2 focus:ring-navy-500/20">
                        <option value="">Select priority...</option>
                        <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                        <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                </div>

                <div>
                    <label for="description" class="mb-1 block text-sm font-medium text-ink-700">Description *</label>
                    <textarea id="description" name="description" rows="6" required
                        placeholder="Provide a detailed description of your issue or request..."
                        class="w-full rounded-lg border border-ink-200 bg-white px-3 py-2.5 text-sm text-ink-700 placeholder-ink-400 focus:border-navy-500 focus:outline-none focus:ring-2 focus:ring-navy-500/20">{{ old('description') }}</textarea>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <a href="{{ route('support.tickets.index') }}" class="text-sm text-ink-500 hover:text-ink-700">
                        ← Back to My Tickets
                    </a>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-navy-600 px-5 py-2.5 text-sm font-semibold text-white shadow transition hover:bg-navy-700">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Submit Ticket
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.app>
