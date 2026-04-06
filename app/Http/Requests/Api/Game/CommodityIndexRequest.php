<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Game;

use App\Traits\NormalizesBooleanFilters;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CommodityIndexRequest extends FormRequest
{
    use NormalizesBooleanFilters;

    protected function prepareForValidation(): void
    {
        $filters = $this->input('filter');

        if (! is_array($filters) || ! array_key_exists('used', $filters)) {
            return;
        }

        $normalized = $this->normalizeBoolean($filters['used']);

        if ($normalized === null) {
            return;
        }

        $filters['used'] = $normalized;

        $this->merge([
            'filter' => $filters,
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'filter' => ['sometimes', 'array'],
            'filter.used' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'filter.array' => 'The commodity filters must be provided as an array.',
            'filter.used.boolean' => 'The used commodity filter must be true or false.',
        ];
    }
}
