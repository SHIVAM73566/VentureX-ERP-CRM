<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Container;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ContainerController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Container::class);

        $containers = Container::ofCompany()
            ->withCount('shipments')
            ->orderBy('container_number')
            ->paginate(15);

        $summary = [
            'total' => Container::ofCompany()->count(),
            'available' => Container::ofCompany()->where('status', 'available')->count(),
            'in_use' => Container::ofCompany()->whereIn('status', ['in_use', 'in_transit'])->count(),
            'returned' => Container::ofCompany()->where('status', 'returned')->count(),
        ];

        return view('logistics.containers.index', compact('containers', 'summary'));
    }

    public function create(): View
    {
        $this->authorize('create', Container::class);

        return view('logistics.containers.form', ['container' => new Container]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Container::class);

        try {
            $container = DB::transaction(function () use ($request) {
                return Container::create([
                    'company_id' => CompanyContext::id(),
                    ...$request->validate($this->rules()),
                ]);
            });

            AuditLogger::created($container);

            return redirect()->route('logistics.containers.index')->with('success', 'Container registered.');
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to create container: '.$e->getMessage());
        }
    }

    public function show(Container $container): View
    {
        $this->authorize('view', $container);

        if ((int) $container->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        $container->load('shipments', 'landedCosts');

        return view('logistics.containers.show', compact('container'));
    }

    public function edit(Container $container): View
    {
        $this->authorize('update', $container);

        if ((int) $container->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        return view('logistics.containers.form', ['container' => $container]);
    }

    public function update(Request $request, Container $container): RedirectResponse
    {
        $this->authorize('update', $container);

        if ((int) $container->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        try {
            DB::transaction(function () use ($request, $container) {
                $container->update($request->validate($this->rules()));
            });
            AuditLogger::updated($container);

            return redirect()->route('logistics.containers.index')->with('success', 'Container updated.');
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to update container: '.$e->getMessage());
        }
    }

    public function destroy(Container $container): RedirectResponse
    {
        $this->authorize('delete', $container);

        if ((int) $container->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        $container->delete();
        AuditLogger::deleted($container);

        return redirect()->route('logistics.containers.index')->with('success', 'Container deleted.');
    }

    protected function rules(): array
    {
        return [
            'container_number' => ['required', 'string', 'max:32'],
            'size' => ['nullable', 'string', 'max:32'],
            'seal_number' => ['nullable', 'string', 'max:64'],
            'status' => ['required', 'in:available,in_use,in_transit,at_destination,returned'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
