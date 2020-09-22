<?php

declare(strict_types=1);

namespace App\Http\Requests\Rsi\CommLink;

use Illuminate\Foundation\Http\FormRequest;

class ReverseImageLinkSearchRequest extends FormRequest
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
            'url' => [
                'required',
                'url',
                'regex:/http?s:\/\/(?:media\.)?robertsspaceindustries.com\/.*/',
            ],
        ];
    }
}
