<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlatformRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $platform = $this->route('platform');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('platforms', 'slug')->ignore($platform->id)],
            'url' => ['nullable', 'string', 'max:255', 'url'],
            'is_active' => ['nullable', 'in:0,1,true,false'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('slug')) {
            $this->merge(['slug' => \Illuminate\Support\Str::slug($this->input('slug'))]);
        }

        if ($this->filled('url')) {
            $url = trim($this->input('url'));
            if (! preg_match('#^https?://#i', $url)) {
                $url = 'https://' . $url;
            }
            $this->merge(['url' => $url]);
        }
    }
}
