<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Activity::class);

        $activities = Activity::ofCompany()
            ->with('assignedTo', 'subject')
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q');
                $q->where('title', 'like', "%{$search}%");
            })
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('assigned_to'), fn ($q) => $q->where('assigned_to', $request->integer('assigned_to')))
            ->when($request->filled('mine') || ! $request->filled('assigned_to'), function ($q) use ($request) {
                if ($request->filled('mine')) {
                    $q->where('assigned_to', auth()->id());
                }
            })
            ->latest('due_at')
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'open' => Activity::ofCompany()->where('status', 'open')->count(),
            'due_today' => Activity::ofCompany()->whereDate('due_at', today())->where('status', 'open')->count(),
            'overdue' => Activity::ofCompany()->where('status', 'open')->whereDate('due_at', '<', today())->count(),
            'completed' => Activity::ofCompany()->where('status', 'completed')->count(),
        ];

        return view('crm.activities.index', [
            'activities' => $activities,
            'summary' => $summary,
            'users' => User::ofCompany()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Activity::class);

        return view('crm.activities.form', [
            'activity' => new Activity,
            'customers' => Customer::ofCompany()->orderBy('name')->get(),
            'leads' => Lead::ofCompany()->orderBy('contact_name')->get(),
            'opportunities' => Opportunity::ofCompany()->orderBy('name')->get(),
            'users' => User::ofCompany()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Activity::class);

        $data = $request->validate($this->rules());

        $activity = Activity::create([
            'company_id' => CompanyContext::id(),
            'created_by' => auth()->id(),
            'assigned_to' => $data['assigned_to'] ?? auth()->id(),
            'subject_type' => $data['subject_type'],
            'subject_id' => $data['subject_id'],
            'status' => 'open',
            'type' => $data['type'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'due_at' => $data['due_at'] ?? null,
        ]);

        AuditLogger::created($activity);

        return redirect()->route('crm.activities.index')->with('success', 'Activity scheduled.');
    }

    public function complete(Activity $activity): RedirectResponse
    {
        $this->authorize('update', $activity);

        if ((int) $activity->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        $activity->update(['status' => 'completed', 'completed_at' => now()]);
        AuditLogger::action($activity, 'complete', 'activities');

        return redirect()->route('crm.activities.index')->with('success', 'Activity completed.');
    }

    public function reopen(Activity $activity): RedirectResponse
    {
        $this->authorize('update', $activity);

        if ((int) $activity->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        $activity->update(['status' => 'open', 'completed_at' => null]);
        AuditLogger::action($activity, 'reopen', 'activities');

        return redirect()->route('crm.activities.index')->with('success', 'Activity reopened.');
    }

    public function destroy(Activity $activity): RedirectResponse
    {
        $this->authorize('delete', $activity);

        if ((int) $activity->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        $activity->delete();
        AuditLogger::deleted($activity);

        return redirect()->route('crm.activities.index')->with('success', 'Activity deleted.');
    }

    protected function rules(): array
    {
        return [
            'type' => ['required', 'in:call,meeting,email,task,note,follow_up'],
            'subject_type' => ['required', 'in:customer,lead,opportunity'],
            'subject_id' => ['required', 'integer', function ($attribute, $value, $fail) {
                $modelClass = match (request('subject_type')) {
                    'customer' => Customer::class,
                    'lead' => Lead::class,
                    'opportunity' => Opportunity::class,
                    default => null,
                };
                if ($modelClass) {
                    $model = $modelClass::ofCompany()->find($value);
                    if (! $model) {
                        $fail('The selected subject does not exist in your company.');
                    }
                }
            }],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_at' => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ];
    }
}
