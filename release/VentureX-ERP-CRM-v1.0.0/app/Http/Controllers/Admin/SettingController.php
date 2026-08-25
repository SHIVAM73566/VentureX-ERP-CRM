<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function __construct(protected SettingService $settings) {}

    public function index(Request $request): View
    {
        $this->authorize('configure');

        $tab = $request->query('tab', 'company');

        return view('admin.settings.index', [
            'tab' => $tab,
            'settings' => $this->settings->all(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorize('configure');

        $validated = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*' => ['nullable', 'string', 'max:2000'],
        ]);

        // Allowlist: only known application settings may be persisted.
        $allowed = [
            'ai.provider', 'ai.model', 'ai.temperature', 'ai.top_p', 'ai.max_tokens',
            'currency.default', 'format.date', 'fiscal.start', 'alerts.email',
        ];

        $updated = [];
        $oldValues = [];
        $newValues = [];

        foreach ($validated['settings'] as $key => $value) {
            if (! in_array($key, $allowed, true)) {
                continue;
            }

            $oldValue = $this->settings->get($key);
            $newValue = $value === '' ? null : $value;

            $this->settings->set($key, $newValue);
            $updated[] = $key;
            $oldValues[$key] = $oldValue;
            $newValues[$key] = $newValue;
        }

        if (! $updated) {
            return back()->with('error', 'No allowed settings were submitted.');
        }

        AuditLogger::log('update', 'settings', null, $oldValues, $newValues);

        return back()->with('success', 'Settings saved.');
    }
}
