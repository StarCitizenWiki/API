<?php

declare(strict_types=1);

namespace App\Http\Requests\Rsi\CommLink\Image;

use App\Models\Account\User\UserGroup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class AddImageTagsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Auth::user()->getHighestPermissionLevel() >= UserGroup::MITARBEITER;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'tags' => [
                'required',
                'array',
            ],
        ];
    }
}
