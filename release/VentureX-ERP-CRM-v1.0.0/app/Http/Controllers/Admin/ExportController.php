<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExportRequest;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use App\Services\ExportService;
use App\Services\SecurityEventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExportController extends Controller
{
    public function __construct(protected ExportService $exports) {}

    public function index(): View
    {
        $this->authorize('viewAny', ExportRequest::class);

        $user = auth()->user();
        $canApprove = $user->hasPermissionTo('exports.approve') || $user->hasPermissionTo('exports.export');

        $requests = ExportRequest::query()
            ->where('company_id', CompanyContext::id())
            ->with('user:id,name,email', 'approver:id,name,email')
            ->when(! $canApprove, fn ($q) => $q->where('user_id', $user->id))
            ->latest()
            ->paginate(15);

        return view('admin.exports.index', [
            'requests' => $requests,
            'sources' => $this->exports->sources(),
            'canApprove' => $canApprove,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', ExportRequest::class);

        $data = $request->validate([
            'data_type' => ['required', Rule::in($this->exports->sources())],
            'reason' => ['required', 'string', 'max:500'],
            'format' => ['required', Rule::in(['csv', 'xlsx'])],
        ]);

        $export = $this->exports->createRequest(auth()->user(), $data['data_type'], $data['reason'], $data['format']);

        if ($export->status === 'pending') {
            SecurityEventService::record('export', 'export_requested', "Export request for {$export->data_type} awaits approval", 'info', null, null, ['export_id' => $export->export_id]);

            return back()->with('success', 'Export requested. A privileged user must approve it before it is generated.');
        }

        $this->exports->generate($export);
        AuditLogger::log('export_requested', 'exports', null, null, ['export_id' => $export->export_id, 'data_type' => $export->data_type]);

        return back()->with('success', 'Export generated. Download it from the table below.');
    }

    public function approve(Request $request, ExportRequest $export): RedirectResponse
    {
        $this->authorize('approve', ExportRequest::class);

        // IDOR protection
        if ((int) $export->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        if ($export->status !== 'pending') {
            return back()->with('error', 'This export is not awaiting approval.');
        }

        $this->exports->approve($export, auth()->user());
        $this->exports->generate($export);

        AuditLogger::log('export_approved', 'exports', null, null, ['export_id' => $export->export_id]);
        SecurityEventService::record('export', 'export_approved', "Export {$export->export_id} approved", 'info');

        return back()->with('success', 'Export approved and generated.');
    }

    public function reject(Request $request, ExportRequest $export): RedirectResponse
    {
        $this->authorize('approve', ExportRequest::class);

        // IDOR protection
        if ((int) $export->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        if ($export->status !== 'pending') {
            return back()->with('error', 'This export is not awaiting approval.');
        }

        $this->exports->reject($export, auth()->user());
        AuditLogger::log('export_rejected', 'exports', null, null, ['export_id' => $export->export_id]);
        SecurityEventService::record('export', 'export_rejected', "Export {$export->export_id} rejected by user ".auth()->id(), 'info');

        return back()->with('success', 'Export request rejected.');
    }

    public function download(ExportRequest $export): RedirectResponse
    {
        $user = auth()->user();

        // IDOR protection
        if ((int) $export->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        if ((int) $export->user_id !== (int) $user->id && ! $user->hasPermissionTo('exports.export')) {
            abort(403, 'You may only download your own exports.');
        }

        $url = $this->exports->signedDownloadUrl($export);

        if (! $url) {
            return back()->with('error', 'This export is not ready, has expired, or its download limit was reached.');
        }

        $export->increment('download_count');
        AuditLogger::log('export_downloaded', 'exports', null, null, ['export_id' => $export->export_id]);
        SecurityEventService::record('export', 'export_downloaded', "Export {$export->export_id} downloaded", 'info');

        return redirect()->away($url);
    }

    public function destroy(ExportRequest $export): RedirectResponse
    {
        $this->authorize('delete', $export);

        // IDOR protection
        if ((int) $export->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        if ((int) $export->user_id !== (int) auth()->id() && ! auth()->user()->hasRole('super_admin')) {
            abort(403);
        }

        $export->delete();
        AuditLogger::deleted($export);

        return back()->with('success', 'Export request deleted.');
    }
}
