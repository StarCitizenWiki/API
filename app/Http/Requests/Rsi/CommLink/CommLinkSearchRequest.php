<?php

declare(strict_types=1);

namespace App\Http\Requests\Rsi\CommLink;

use App\Http\Requests\StarCitizen\AbstractSearchRequest;
use Illuminate\Foundation\Http\FormRequest;

class CommLinkSearchRequest extends AbstractSearchRequest
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
            'keyword' => 'required_without_all:query|string|min:3|max:255',
            'query' => 'required|string|min:1|max:255',
        ];
    }
}
