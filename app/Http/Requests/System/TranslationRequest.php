<?php declare(strict_types = 1);

namespace App\Http\Requests\System;

use App\Models\System\Language;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * Class TranslationRequest
 */
class TranslationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $localeCodes = Language::all('locale_code')->keyBy('locale_code');
        $rule = '|string|min:1';
        $rules = [];

        foreach ($localeCodes as $code => $language) {
            if (config('language.english') === $language->locale_code) {
                $rules[$code] = 'required'.$rule;
            } else {
                $rules[$code] = 'present'.$rule;
            }
        }

        return $rules;
    }
}
