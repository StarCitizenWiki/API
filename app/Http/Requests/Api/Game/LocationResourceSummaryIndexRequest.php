<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Game;

use App\Traits\NormalizesBooleanFilters;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LocationResourceSummaryIndexRequest extends FormRequest
{
    use NormalizesBooleanFilters;

    protected function prepareForValidation(): void
    {
        $filters = $this->input('filter');

        if (! is_array($filters)) {
            return;
        }

        $this->merge([
            'filter' => $this->normalizeFilterFields($filters, ['ship', 'fps', 'ground_vehicle', 'harvestable', 'salvage']),
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
            'filter.name' => ['sometimes', 'string'],
            'filter.system' => ['sometimes', 'string'],
            'filter.type_name' => ['sometimes', 'string'],
            'filter.kind' => ['sometimes'],
            'filter.resource' => ['sometimes', 'string'],
            'filter.ship' => ['sometimes', 'boolean'],
            'filter.fps' => ['sometimes', 'boolean'],
            'filter.ground_vehicle' => ['sometimes', 'boolean'],
            'filter.harvestable' => ['sometimes', 'boolean'],
            'filter.salvage' => ['sometimes', 'boolean'],
        ];
    }
}
