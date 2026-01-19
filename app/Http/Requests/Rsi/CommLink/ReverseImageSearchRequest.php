<?php

declare(strict_types=1);

namespace App\Http\Requests\Rsi\CommLink;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ReverseImageSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => ['required', 'image', 'max:5120'],
            'similarity' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function similarity(): int
    {
        return $this->integer('similarity', 75);
    }

    public function imageContents(): string
    {
        $file = $this->file('image');

        if (! $file instanceof UploadedFile) {
            throw new HttpException(422, 'Image file is required.');
        }

        $contents = file_get_contents($file->getPathname());

        if ($contents === false) {
            throw new HttpException(422, 'Unable to read uploaded image.');
        }

        return $contents;
    }
}
