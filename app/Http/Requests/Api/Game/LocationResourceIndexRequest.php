<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Game;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LocationResourceIndexRequest extends FormRequest
{
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
            'filter.query' => ['sometimes', 'string'],
            'filter.kind' => ['sometimes'],
            'filter.group' => ['sometimes'],
            'filter.min_quality' => ['sometimes', 'numeric'],
            'filter.min_max_percentage' => ['sometimes', 'numeric'],
        ];
    }
}
