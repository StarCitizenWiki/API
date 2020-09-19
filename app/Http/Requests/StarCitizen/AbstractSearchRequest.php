<?php declare(strict_types=1);

namespace App\Http\Requests\StarCitizen;

use Illuminate\Foundation\Http\FormRequest;

abstract class AbstractSearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'query' => 'required|string|min:1|max:255',
        ];
    }
}
