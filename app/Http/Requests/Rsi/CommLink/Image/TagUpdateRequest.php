<?php

namespace App\Http\Requests\Rsi\CommLink\Image;

use Illuminate\Foundation\Http\FormRequest;

class TagUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'exists:comm_link_image_tags',
            ],
            'name_en' => [
                'required',
                'string',
                'min:3',
                'max:255',
            ],
        ];
    }
}
