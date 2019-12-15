<?php

declare(strict_types=1);

namespace App\Http\Requests\Rsi\CommLink;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * Comm-Link Requests.
 */
class CommLinkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->can('web.user.rsi.comm-links.update');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|min:1|max:255',
            'url' => 'nullable|string|min:15|max:255',
            'created_at' => 'required|date',
            'channel' => 'required|string|exists:comm_link_channels,id',
            'series' => 'required|string|exists:comm_link_series,id',
            'category' => 'required|string|exists:comm_link_categories,id',
            //'version' => 'required|string|regex:/\d{4}\-\d{2}\-\d{2}\_\d{6}\.html/',
        ];
    }
}
