<?php declare(strict_types = 1);

namespace App\Http\Requests;

use App\Models\System\Language;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CommLinkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::guard('admin')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $localeCodes = Language::all('locale_code')->keyBy('locale_code');
        $rule = '|string|min:1';
        $rules = [
            'title' => 'required|string|min:1|max:255',
            'url' => 'nullable|string|min:15|max:255',
            'created_at' => 'required|date',
        ];

        foreach ($localeCodes as $code => $language) {
            if (config('language.english') === $language->locale_code) {
                $rules[$code] = 'required'.$rule;
            } else {
                $rules[$code] = 'nullable'.$rule;
            }
        }

        if (Auth::guard('admin')->user()->can('web.admin.rsi.comm-links.update_settings')) {
            $rules['version'] = 'required|string|regex:/\d{4}\-\d{2}\-\d{2}\_\d{6}\.html/';
        }

        return $rules;
    }
}
