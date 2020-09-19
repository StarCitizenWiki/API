<?php declare(strict_types=1);

namespace App\Http\Requests\StarCitizen\Manufacturer;

use App\Models\System\Language;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * Class ManufacturerTranslationRequest
 */
class ManufacturerTranslationRequest extends FormRequest
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
                $rules["description_{$code}"] = 'required'.$rule;
                $rules["known_for_{$code}"] = 'required'.$rule;
            } else {
                $rules["description_{$code}"] = 'present'.$rule;
                $rules["known_for_{$code}"] = 'present'.$rule;
            }
        }

        return $rules;
    }
}
