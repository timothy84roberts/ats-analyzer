<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreManagedUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(ManagedUserProfileRules::normalized($this->all()));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge(ManagedUserProfileRules::base(), [
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
        ]);
    }
}
