<?php

namespace App\Http\Requests;

use App\Models\Opportunity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OpportunityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'expected_value' => ['nullable', 'numeric', 'min:0'],
            'currency_code' => ['nullable', 'string', 'max:8'],
            'stage' => ['sometimes', Rule::in(array_keys(Opportunity::STAGES))],
            'probability' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'expected_close_date' => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'source' => ['nullable', 'string', 'max:64'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
