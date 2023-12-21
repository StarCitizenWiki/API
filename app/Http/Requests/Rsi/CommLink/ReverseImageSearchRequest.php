<?php

declare(strict_types=1);

namespace App\Http\Requests\Rsi\CommLink;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReverseImageSearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'image' => 'required|file|max:5120', // Limit to 5mb
            'similarity' => 'required|numeric|min:1|max:100',
        ];
    }
}
