<?php

namespace App\Http\Requests\Rsi\CommLink\Image;

use App\Models\Account\User\UserGroup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ImageUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Auth::user()->getHighestPermissionLevel() >= UserGroup::USER;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'filename' => [
                'required',
                'string',
                'min:5',
                'max:255',
            ],
            'image' => [
                'required',
                'exists:comm_link_images,id'
            ],
            'description' => [
                'required',
                'string',
                'min:10',
                'max:255'
            ],
            'categories' => [
                'required',
                'string',
                'min:5',
                'max:255',
            ]
        ];
    }
}
