<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\ErrorReport;
use App\Models\ProductUpdate;
use App\Models\SupportNotification;
use App\Models\SupportTicket;
use App\Models\SystemAnnouncement;
use App\Services\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HelpCenterController extends Controller
{
    public function index(): View
    {
        $activeAnnouncements = SystemAnnouncement::query()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->latest('published_at')
            ->limit(5)
            ->get();

        $latestUpdates = ProductUpdate::latest('released_at')
            ->limit(3)
            ->get();

        return view('support.index', [
            'announcements' => $activeAnnouncements,
            'updates' => $latestUpdates,
        ]);
    }

    public function docs(): View
    {
        return view('support.docs');
    }

    public function installation(): View
    {
        $latestUpdate = ProductUpdate::latest('released_at')->first();

        return view('support.installation', [
            'latestUpdate' => $latestUpdate,
        ]);
    }

    public function faq(): View
    {
        return view('support.faq');
    }

    public function contact(): View
    {
        return view('support.contact');
    }

    public function submitError(): View
    {
        return view('support.report-error', [
            'errorTypes' => ErrorReport::ERROR_TYPES,
        ]);
    }

    public function storeError(Request $request): RedirectResponse
    {
        $this->authorize('create', ErrorReport::class);

        $validated = $request->validate([
            'error_type' => ['required', 'string', 'in:'.implode(',', array_keys(ErrorReport::ERROR_TYPES))],
            'error_message' => ['required', 'string', 'max:5000'],
            'module' => ['nullable', 'string', 'max:128'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        $validated['company_id'] = CompanyContext::id();
        $validated['user_id'] = auth()->id();
        $validated['ip_address'] = $request->ip();
        $validated['user_agent'] = mb_substr($request->userAgent() ?? '', 0, 500);
        $validated['error_hash'] = ErrorReport::generateErrorHash(
            $validated['error_message'],
            $validated['module'] ?? '',
            0
        );
        $validated['status'] = 'new';
        $validated['occurrence_count'] = 1;
        $validated['first_seen_at'] = now();
        $validated['last_seen_at'] = now();

        $existing = ErrorReport::where('error_hash', $validated['error_hash'])->first();

        if ($existing) {
            $existing->update([
                'occurrence_count' => $existing->occurrence_count + 1,
                'last_seen_at' => now(),
            ]);

            return back()->with('success', 'This error has been recorded. Thank you for your report.');
        }

        ErrorReport::create($validated);

        return back()->with('success', 'Error report submitted. Our team will investigate.');
    }

    public function notifications(): View
    {
        $notifications = SupportNotification::where('user_id', auth()->id())
            ->latest()
            ->paginate(20);

        if (request()->filled('type')) {
            $notifications->appends(['type' => request('type')]);
        }

        return view('support.notifications', compact('notifications'));
    }

    public function markNotificationRead(SupportNotification $notification): RedirectResponse
    {
        if ((int) $notification->user_id !== (int) auth()->id()) {
            abort(403);
        }

        $notification->update(['read_at' => now()]);

        return back()->with('success', 'Notification marked as read.');
    }

    public function markNotificationUnread(SupportNotification $notification): RedirectResponse
    {
        if ((int) $notification->user_id !== (int) auth()->id()) {
            abort(403);
        }

        $notification->update(['read_at' => null]);

        return back()->with('success', 'Notification marked as unread.');
    }

    public function submitContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:general,billing,technical,other'],
            'message' => ['required', 'string', 'max:10000'],
        ]);

        $validated['company_id'] = CompanyContext::id();
        $validated['user_id'] = auth()->id();
        $validated['status'] = 'open';
        $validated['ticket_number'] = SupportTicket::generateTicketNumber();
        $validated['priority'] = 'medium';
        $validated['source'] = 'contact_form';

        SupportTicket::create($validated);

        return back()->with('success', 'Your message has been received. Our support team will get back to you within 24 hours.');
    }
}
