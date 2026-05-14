<?php

namespace App\Http\Requests;

use App\Support\RichTextSanitizer;
use Illuminate\Foundation\Http\FormRequest;

class AnalyzeAtsRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->filled('language') || $this->input('language') === '') {
            $this->merge(['language' => 'English']);
        }

        $content = RichTextSanitizer::sanitize($this->input('content'));
        $this->merge(['content' => $content]);
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
            'content' => ['required', 'string', 'min:20', 'max:50000'],
            'language' => ['required', 'string', 'max:64'],
            'resume' => ['required', 'file', 'mimes:pdf,doc,docx,txt,rtf,jpg,jpeg,jpe,png,tif,tiff', 'max:10240'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'content.min' => __('Job description should be at least :min characters.'),
            'resume.required' => __('Please upload a resume file.'),
            'resume.mimes' => __('Resume must be a PDF, Word document, text, RTF, or common image format (e.g. JPG, PNG).'),
        ];
    }
}
