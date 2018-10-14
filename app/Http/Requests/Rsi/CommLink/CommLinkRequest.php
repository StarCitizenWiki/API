<?php declare(strict_types = 1);

namespace App\Http\Requests\Rsi\CommLink;

use App\Models\System\Language;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * Comm-Link Requests
 */
class CommLinkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'title' => 'required|string|min:1|max:255',
            'url' => 'nullable|string|min:15|max:255',
            'created_at' => 'required|date',
        ];

        if (Auth::user()->can('web.user.rsi.comm-links.update')) {
            $rules['version'] = 'required|string|regex:/\d{4}\-\d{2}\-\d{2}\_\d{6}\.html/';
        }

        return $rules;
    }
}
