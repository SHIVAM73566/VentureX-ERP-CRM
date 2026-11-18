<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use App\Services\Demo\DemoDataWipeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * @author Shivam Chaturvedi <chaturvedishivam179@gmail.com>
 */
class DemoDataController extends Controller
{
    public const CONFIRMATION_PHRASE = 'WIPE DEMO DATA';

    public function index(DemoDataWipeService $wipe): View
    {
        $companyId = $this->companyId();
        abort_if($companyId === 0, 403);

        return view('admin.imports.demo-data', [
            'counts' => $wipe->preview($companyId),
            'phrase' => self::CONFIRMATION_PHRASE,
        ]);
    }

    public function wipe(Request $request, DemoDataWipeService $wipe): RedirectResponse
    {
        $companyId = $this->companyId();
        abort_if($companyId === 0, 403);

        $data = $request->validate([
            'password' => ['required', 'string'],
            'confirmation' => ['required', 'string'],
        ]);

        $user = $request->user();
        if (! $user || ! Hash::check($data['password'], (string) $user->password)) {
            AuditLogger::log('demo_data_wipe_denied', 'admin');

            return back()->withErrors([
                'password' => 'Current password verification failed. No changes were made.',
            ]);
        }

        if (! hash_equals(self::CONFIRMATION_PHRASE, $data['confirmation'])) {
            return back()->withErrors([
                'confirmation' => 'Type the exact phrase '.self::CONFIRMATION_PHRASE.' to confirm.',
            ]);
        }

        $deleted = $wipe->wipe($companyId, (int) $user->id);
        $total = array_sum($deleted);

        return back()->with('success', $total === 0
            ? 'No demo data was found. Nothing was changed.'
            : "Demo data wiped: {$total} row(s) removed across ".count($deleted).' table(s).');
    }

    protected function companyId(): int
    {
        return (int) (CompanyContext::id() ?? session('company_id') ?? auth()->user()?->company_id ?? 0);
    }
}
