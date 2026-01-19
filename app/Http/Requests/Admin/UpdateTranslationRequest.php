<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\System\Language;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTranslationRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'translations' => ['required', 'array'],
            'translations.'.Language::ENGLISH => ['nullable', 'string'],
            'translations.'.Language::GERMAN => ['nullable', 'string'],
            'translations.'.Language::CHINESE => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'translations.required' => 'Translations data is required.',
            'translations.array' => 'Translations must be an array.',
        ];
    }
}
