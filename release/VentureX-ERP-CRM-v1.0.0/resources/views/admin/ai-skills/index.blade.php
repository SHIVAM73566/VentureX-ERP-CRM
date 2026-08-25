<x-layouts.app title="AI Skills" :breadcrumbs="[['label' => 'Admin'], ['label' => 'AI Skills']]">

    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <form method="GET" action="{{ route('admin.ai-skills.index') }}" class="flex flex-1">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search name or slug..." class="input max-w-md" />
            <button type="submit" class="btn-secondary">Filter</button>
        </form>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($skills as $skill)
            <div class="card flex flex-col">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="font-bold text-ink-900">{{ $skill->name }}</h3>
                        <p class="text-xs font-mono text-ink-400">{{ $skill->slug }}</p>
                    </div>
                    <span class="badge-{{ $skill->is_active ? 'green' : 'gray' }}">{{ $skill->is_active ? 'Active' : 'Inactive' }}</span>
                </div>
                <p class="mt-2 line-clamp-2 flex-1 text-sm text-ink-500">{{ $skill->description ?? 'No description.' }}</p>
                <dl class="mt-3 grid grid-cols-3 gap-2 border-t border-ink-100 pt-3 text-center text-xs">
                    <div><dt class="text-ink-400">Provider</dt><dd class="font-semibold text-ink-700">{{ $skill->provider ?? '—' }}</dd></div>
                    <div><dt class="text-ink-400">Temp</dt><dd class="font-semibold text-ink-700">{{ $skill->temperature ?? '—' }}</dd></div>
                    <div><dt class="text-ink-400">Max Tokens</dt><dd class="font-semibold text-ink-700">{{ $skill->max_tokens ?? '—' }}</dd></div>
                </dl>
                @can('update', $skill)
                    <div class="mt-3 text-right">
                        <a href="{{ route('admin.ai-skills.edit', $skill) }}" class="text-sm font-medium text-navy-600 hover:text-navy-500">Configure</a>
                    </div>
                @endcan
            </div>
        @empty
            <div class="card sm:col-span-2 lg:col-span-3"><p class="py-8 text-center text-ink-400">No AI skills configured.</p></div>
        @endforelse
    </div>

    <div class="mt-4">{{ $skills->links() }}</div>
</x-layouts.app>
