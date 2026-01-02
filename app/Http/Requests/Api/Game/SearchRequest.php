<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Game;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'query' => ['required', 'string', 'min:1', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'query.required' => 'A search query is required.',
            'query.string' => 'The search query must be a string.',
            'query.min' => 'The search query must be at least :min character.',
            'query.max' => 'The search query may not be greater than :max characters.',
        ];
    }
}
