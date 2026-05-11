<?php

namespace App\Http\Requests;

use App\Models\JobApplication;
use App\Support\RichTextSanitizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJobApplicationRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->input('analysis_percentage') === '' || $this->input('analysis_percentage') === null) {
            $this->merge(['analysis_percentage' => null]);
        }

        $desc = RichTextSanitizer::sanitize($this->input('description'));
        $this->merge(['description' => $desc === '' ? null : $desc]);

        if ($this->input('outcome_status') !== JobApplication::OUTCOME_REJECTED) {
            $this->merge(['rejection_reason' => null]);
        }
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
            'outcome_status' => ['required', Rule::in(JobApplication::outcomeStatuses())],
            'rejection_reason' => ['required_if:outcome_status,'.JobApplication::OUTCOME_REJECTED, 'nullable', 'string', 'max:10000'],
            'pipeline_stage_id' => ['required', 'exists:pipeline_stages,id'],
            'country_id' => ['required', 'exists:countries,id'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'platform_id' => ['required', 'exists:platforms,id'],
            'analysis_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'applied_on' => ['required', 'date'],
            'resume' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'remove_resume' => ['sometimes', 'boolean'],
        ];
    }
}
