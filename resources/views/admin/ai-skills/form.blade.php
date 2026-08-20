<x-layouts.app
    :title="'Configure — '.($skill->name ?? 'AI Skill')"
    :breadcrumbs="[['label' => 'Admin', 'url' => route('admin.ai-skills.index')], ['label' => 'AI Skills', 'url' => route('admin.ai-skills.index')], ['label' => $skill->name]]">

    <div class="mx-auto max-w-3xl">
        <form method="POST" action="{{ route('admin.ai-skills.update', $skill) }}" class="space-y-6">
            @csrf @method('PUT')

            <div class="card space-y-4">
                <h2 class="text-lg font-bold text-ink-900">Skill Configuration</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.input label="Name *" name="name" :value="$skill->name" required />
                    <x-form.input label="Slug" name="slug" :value="$skill->slug" disabled />
                    <x-form.input label="Provider" name="provider" :value="$skill->provider" disabled />
                    <x-form.input label="Model" name="model" :value="$skill->model" disabled />
                    <x-form.input label="Temperature" name="temperature" type="number" step="0.05" min="0" max="2" :value="$skill->temperature" help="0 = deterministic, 2 = creative" />
                    <x-form.input label="Top P" name="top_p" type="number" step="0.05" min="0" max="1" :value="$skill->top_p" />
                    <x-form.input label="Max Tokens" name="max_tokens" type="number" min="16" :value="$skill->max_tokens" />
                </div>
                <x-form.checkbox name="is_active" label="Active" description="Enable this skill for use in AI features." :checked="$skill->is_active" />
                <x-form.textarea label="Description" name="description" :value="$skill->description" rows="2" />
                <x-form.textarea label="Instructions (system prompt)" name="instructions" :value="$skill->instructions" rows="6" class="font-mono" />
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('admin.ai-skills.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-accent">Save Changes</button>
            </div>
        </form>
    </div>
</x-layouts.app>
