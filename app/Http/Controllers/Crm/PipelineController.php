<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Opportunity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PipelineController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Opportunity::class);

        $stages = Opportunity::STAGES;

        $columns = collect($stages)->mapWithKeys(function ($label, $stage) {
            $query = Opportunity::ofCompany()->where('stage', $stage);

            if ($stage === 'won') {
                $value = (float) Opportunity::ofCompany()->where('stage', 'won')->sum('expected_value');
            } else {
                $value = (float) (clone $query)->sum('expected_value');
            }

            return [$stage => [
                'label' => $label,
                'count' => $query->count(),
                'value' => $value,
                'items' => $query->with('customer', 'assignedTo')->orderByDesc('expected_value')->get(),
            ]];
        });

        $totals = [
            'opportunities' => Opportunity::ofCompany()->whereNotIn('stage', ['won', 'lost'])->count(),
            'pipeline_value' => (float) Opportunity::ofCompany()->whereNotIn('stage', ['won', 'lost'])->sum('expected_value'),
            'weighted_value' => (float) Opportunity::ofCompany()->whereNotIn('stage', ['won', 'lost'])->get()->sum(fn ($o) => (float) $o->expected_value * ((float) $o->probability / 100)),
            'won_value' => (float) Opportunity::ofCompany()->where('stage', 'won')->sum('expected_value'),
        ];

        return view('crm.pipeline.index', compact('columns', 'totals', 'stages'));
    }

    public function move(Request $request, Opportunity $opportunity): RedirectResponse
    {
        $this->authorize('update', $opportunity);

        $data = $request->validate([
            'stage' => ['required', 'in:'.implode(',', array_keys(Opportunity::STAGES))],
        ]);

        $opportunity->update([
            'stage' => $data['stage'],
            'probability' => $data['stage'] === 'won' ? 100 : ($data['stage'] === 'lost' ? 0 : $opportunity->probability),
        ]);

        return back()->with('success', 'Opportunity moved to '.Opportunity::STAGES[$data['stage']].'.');
    }
}
