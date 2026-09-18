<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('users.update') ?? false;
    }

    public function rules(): array
    {
        $userId = $this->route('user');
        $userId = $userId instanceof \App\Models\User ? $userId->id : $userId;

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
                Rule::unique('users', 'email')
                    ->where('company_id', $this->user()->company_id)
                    ->ignore($userId),
            ],

            'password' => [
                'sometimes',
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],

            'role' => [
                'sometimes',
                Rule::in([
                    'admin',
                    'manager',
                    'user',
                ]),
            ],
        ];
    }
}
