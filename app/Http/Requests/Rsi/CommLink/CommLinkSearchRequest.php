<?php

declare(strict_types=1);

namespace App\Http\Requests\Rsi\CommLink;

use Illuminate\Foundation\Http\FormRequest;

class CommLinkSearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'keyword' => 'required|string|min:3|max:255',
        ];
    }
}
