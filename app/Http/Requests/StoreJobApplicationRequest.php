<?php

namespace App\Http\Requests;

use App\Support\RichTextSanitizer;
use Illuminate\Foundation\Http\FormRequest;

class StoreJobApplicationRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->input('analysis_percentage') === '' || $this->input('analysis_percentage') === null) {
            $this->merge(['analysis_percentage' => null]);
        }

        $desc = RichTextSanitizer::sanitize($this->input('description'));
        $this->merge(['description' => $desc === '' ? null : $desc]);
    }

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:50000'],
            'country_id' => ['required', 'exists:countries,id'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'platform_id' => ['required', 'exists:platforms,id'],
            'analysis_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'applied_on' => ['required', 'date'],
            'resume' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ];
    }
}
