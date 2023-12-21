<?php

declare(strict_types=1);

namespace App\Http\Requests\Rsi\CommLink\Image;

use App\Models\Account\User\UserGroup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class NewImageTagRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'unique:comm_link_image_tags',
            ],
        ];
    }
}
