<?php

namespace App\Http\Requests\Transcript;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TranscriptStoreRequest extends FormRequest
{
    private const WIKI_ID = 'wiki_id';

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->can('web.user.transcripts.create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'source_title' => 'required|string|min:1|max:255',
            'source_url' => 'nullable|url|min:15|max:255',
            'source_published_at' => 'nullable|date',

            'title' => 'required|string|min:1|max:255',
            'youtube_url' => 'nullable|url|min:15|max:255',
            'published_at' => 'required|date',
            'format' => 'required|string|exists:video_formats,id',
            self::WIKI_ID => [
                'required',
                'integer',
                'min:1',
                'max:100000',
                Rule::unique('transcripts'),
            ],
            'en_EN' => 'nullable|string',
            'de_DE' => 'nullable|string',
        ];
    }
}
