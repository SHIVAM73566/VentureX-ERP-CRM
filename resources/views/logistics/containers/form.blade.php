<x-layouts.app
    :title="$container->exists ? 'Edit Container' : 'Register Container'"
    :breadcrumbs="[['label' => 'Logistics', 'url' => route('logistics.containers.index')], ['label' => 'Containers', 'url' => route('logistics.containers.index')], ['label' => $container->exists ? 'Edit' : 'New']]">

    <div class="mx-auto max-w-2xl">
        <form method="POST" action="{{ $container->exists ? route('logistics.containers.update', $container) : route('logistics.containers.store') }}" class="space-y-6">
            @csrf
            @if ($container->exists)
                @method('PUT')
            @endif

            <div class="card space-y-4">
                <h2 class="text-lg font-bold text-ink-900">Container Details</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.input label="Container Number *" name="container_number" :value="$container->container_number" required placeholder="e.g. MSKU1234567" />
                    <x-form.input label="Size" name="size" :value="$container->size" placeholder="e.g. 20ft, 40ft HC" />
                    <x-form.input label="Seal Number" name="seal_number" :value="$container->seal_number" />
                    <x-form.select label="Status *" name="status" :options="\App\Models\Container::STATUSES" :value="$container->status ?? 'available'" required />
                </div>
                <x-form.textarea label="Notes" name="notes" :value="$container->notes" rows="2" />
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('logistics.containers.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-accent">{{ $container->exists ? 'Save Changes' : 'Register Container' }}</button>
            </div>
        </form>
    </div>
</x-layouts.app>
