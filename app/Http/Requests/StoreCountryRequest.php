<?php

namespace App\Http\Requests;

use App\Services\CountryCatalogService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCountryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $codes = app(CountryCatalogService::class)->allowedCodes();

        return [
            'code' => [
                'required',
                'string',
                'size:2',
                'alpha',
                Rule::unique('countries', 'code'),
                Rule::in($codes),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $this->merge(['code' => strtoupper($this->input('code'))]);
        }
    }
}
