<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('customers.update') ?? false;
    }

    public function rules(): array
    {
        $customerId = $this->route('customer');
        $customerId = $customerId instanceof \App\Models\Customer ? $customerId->id : $customerId;

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('customers', 'email')
                    ->where('company_id', $this->user()->company_id)
                    ->ignore($customerId),
            ],

            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'status' => [
                'sometimes',
                'required',
                Rule::in([
                    'active',
                    'inactive',
                ]),
            ],
        ];
    }
}
