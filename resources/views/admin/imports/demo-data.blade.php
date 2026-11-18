<x-layouts.app :title="'Demo Data'" :breadcrumbs="[['label' => 'Administration', 'url' => route('admin.imports.index')], ['label' => 'Demo Data']]">

    <div class="mx-auto max-w-4xl space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-ink-900">Demo Data</h1>
            <p class="mt-1 text-sm text-ink-500">Preview and remove seeded demo records and publicly known demo accounts. Settings, roles, permissions, the chart of accounts and your buyer admin account are preserved.</p>
        </div>

        <div class="rounded-xl border border-ink-200 bg-white p-6">
            <h2 class="text-sm font-medium text-ink-700">Current demo rows</h2>
            @if (empty($counts))
                <p class="mt-3 text-sm text-green-700">No demo data was found for this company.</p>
            @else
                <div class="mt-3 overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="border-b border-ink-200 text-ink-500">
                            <tr><th class="py-2 pr-4">Table</th><th class="py-2">Rows</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($counts as $table => $count)
                                <tr class="border-b border-ink-100">
                                    <td class="py-2 pr-4 font-mono text-xs">{{ $table }}</td>
                                    <td class="py-2">{{ $count }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="rounded-xl border border-red-200 bg-white p-6">
            <h2 class="text-sm font-medium text-red-700">Wipe demo data</h2>
            <p class="mt-1 text-sm text-ink-500">This action is audited and cannot be undone. Re-enter your current password and type <code class="font-mono font-semibold">{{ $phrase }}</code> exactly to continue.</p>

            <form method="POST" action="{{ route('admin.demo-data.wipe') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label for="password" class="mb-1 block text-sm font-medium text-ink-700">Current password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" class="input w-full" required>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="confirmation" class="mb-1 block text-sm font-medium text-ink-700">Confirmation phrase</label>
                    <input id="confirmation" name="confirmation" type="text" autocomplete="off" class="input w-full" placeholder="{{ $phrase }}" required>
                    @error('confirmation')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="btn-primary">Wipe demo data</button>
            </form>
        </div>

        <div class="rounded-xl border border-indigo-200 bg-white p-6">
            <h2 class="text-sm font-medium text-indigo-700">AERO X action: wipe_demo_data</h2>
            <p class="mt-1 text-sm text-ink-500">AERO X requires a current-password check, the exact typed phrase, and an explicit confirmation payload before anything is deleted.</p>

            <div class="mt-4 space-y-4">
                <div>
                    <label for="aero-password" class="mb-1 block text-sm font-medium text-ink-700">Current password</label>
                    <input id="aero-password" type="password" autocomplete="current-password" class="input w-full" required>
                </div>
                <div>
                    <label for="aero-confirmation" class="mb-1 block text-sm font-medium text-ink-700">Confirmation phrase</label>
                    <input id="aero-confirmation" type="text" autocomplete="off" class="input w-full" placeholder="{{ $phrase }}" required>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button id="aero-preview" type="button" class="btn-primary">Preview with AERO X</button>
                    <button id="aero-execute" type="button" class="btn-primary" disabled>Wipe via AERO X</button>
                </div>
                <div id="aero-status" class="hidden rounded-lg bg-ink-50 p-3 text-sm text-ink-700"></div>
            </div>
        </div>
    </div>

    <script>
    (() => {
        const status = document.getElementById('aero-status');
        const execute = document.getElementById('aero-execute');
        const phrase = @json($phrase);
        const endpoint = @json(route('admin.demo-data.aero-x.wipe'));
        const token = @json(csrf_token());
        let previewReady = false;

        const show = (message, isError = false) => {
            status.textContent = message;
            status.classList.remove('hidden');
            status.classList.toggle('bg-red-50', isError);
            status.classList.toggle('text-red-700', isError);
            status.classList.toggle('bg-ink-50', !isError);
            status.classList.toggle('text-ink-700', !isError);
        };

        const payload = (confirm) => ({
            action_key: 'wipe_demo_data',
            confirm: confirm,
            password: document.getElementById('aero-password').value,
            confirmation: document.getElementById('aero-confirmation').value,
        });

        const call = async (confirm) => {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(payload(confirm)),
            });

            const data = await response.json().catch(() => ({}));
            if (!response.ok || data.success === false) {
                execute.disabled = true;
                previewReady = false;
                show(data.error || 'AERO X could not complete the request. No changes were made.', true);
                return;
            }

            if (!confirm) {
                previewReady = true;
                execute.disabled = false;
                const rows = Object.entries(data.preview || {}).map(([table, count]) => `${table}: ${count}`).join(', ');
                show(`Confirmation required. ${data.message}${rows ? ' Preview: ' + rows : ''}`);
                return;
            }

            execute.disabled = true;
            previewReady = false;
            show(`${data.message} ${data.total_rows ?? 0} row(s) removed.`);
        };

        document.getElementById('aero-preview').addEventListener('click', () => {
            if (document.getElementById('aero-confirmation').value !== phrase) {
                show(`Type the exact phrase ${phrase} to continue.`, true);
                return;
            }
            call(false).catch(() => show('AERO X could not complete the request. No changes were made.', true));
        });

        execute.addEventListener('click', () => {
            if (!previewReady) {
                show('Preview the wipe with AERO X before executing it.', true);
                return;
            }
            call(true).catch(() => show('AERO X could not complete the request. No changes were made.', true));
        });
    })();
    </script>

</x-layouts.app>
