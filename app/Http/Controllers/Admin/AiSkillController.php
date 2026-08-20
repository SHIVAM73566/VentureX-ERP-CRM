<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiSkill;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiSkillController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', AiSkill::class);

        $skills = AiSkill::ofCompany()
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q');
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.ai-skills.index', compact('skills'));
    }

    public function edit(AiSkill $skill): View
    {
        $this->authorize('update', $skill);

        return view('admin.ai-skills.form', compact('skill'));
    }

    public function update(Request $request, AiSkill $skill): RedirectResponse
    {
        $this->authorize('update', $skill);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'temperature' => ['nullable', 'numeric', 'min:0', 'max:2'],
            'top_p' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'max_tokens' => ['nullable', 'integer', 'min:16'],
            'instructions' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $skill->update([
            ...$data,
            'is_active' => $request->boolean('is_active'),
        ]);

        AuditLogger::updated($skill);

        return redirect()->route('admin.ai-skills.index')->with('success', 'AI skill updated.');
    }
}
