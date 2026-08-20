<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'customer_code' => ['nullable', 'string', 'max:32'],
            'tax_id' => ['nullable', 'string', 'max:64'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'website' => ['nullable', 'url', 'max:255'],
            'industry' => ['nullable', 'string', 'max:128'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:128'],
            'state' => ['nullable', 'string', 'max:128'],
            'postal_code' => ['nullable', 'string', 'max:16'],
            'country_id' => ['nullable', 'exists:countries,id'],
            'currency_code' => ['nullable', 'string', 'max:8'],
            'status' => ['sometimes', 'string', 'in:active,inactive,prospect'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
