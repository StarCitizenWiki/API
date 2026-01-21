<?php

declare(strict_types=1);

namespace App\Http\Requests\Rsi\CommLink;

use Illuminate\Foundation\Http\FormRequest;

class SimilarSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'image' => 'required|integer|exists:comm_link_images,id',
            'similarity' => 'nullable|integer|min:1|max:100',
        ];
    }

    /**
     * Get all of the input and files for the request.
     *
     * @return array<string, mixed>
     */
    public function all($keys = null): array
    {
        $input = parent::all($keys);
        $input['image'] = $this->route('image');

        if (isset($input['similarity'])) {
            $input['similarity'] = (int) $input['similarity'];
        }

        return $input;
    }
}
