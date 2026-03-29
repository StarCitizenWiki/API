<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Game;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ResourceTypeIndexRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $filters = $this->input('filter');

        if (! is_array($filters) || ! array_key_exists('used', $filters)) {
            return;
        }

        $normalizedUsedFilter = $this->normalizeUsedFilter($filters['used']);

        if ($normalizedUsedFilter === null) {
            return;
        }

        $filters['used'] = $normalizedUsedFilter;

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
            'filter.array' => 'The resource type filters must be provided as an array.',
            'filter.used.boolean' => 'The used resource filter must be true or false.',
        ];
    }

    /**
     * @return array{used?: bool}
     */
    public function filters(): array
    {
        $filters = data_get($this->validated(), 'filter', []);

        if (! is_array($filters)) {
            return [];
        }

        if (array_key_exists('used', $filters)) {
            $filters['used'] = $this->normalizeUsedFilter($filters['used']);
        }

        return $filters;
    }

    private function normalizeUsedFilter(mixed $value): ?bool
    {
        return match (true) {
            is_bool($value) => $value,
            $value === 1 || $value === '1' => true,
            $value === 0 || $value === '0' => false,
            is_string($value) && strtolower(trim($value)) === 'true' => true,
            is_string($value) && strtolower(trim($value)) === 'false' => false,
            default => null,
        };
    }
}
