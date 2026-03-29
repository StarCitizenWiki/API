<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Game;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class BlueprintIndexRequest extends FormRequest
{
    /**
     * @var list<string>
     */
    private const array FILTER_KEYS = [
        'query',
        'output.uuid',
        'output.name',
        'output.class',
        'output.type',
        'default',
        'ingredient',
        'ingredient.uuid',
    ];

    protected function prepareForValidation(): void
    {
        $filters = $this->input('filter');

        if (! is_array($filters)) {
            return;
        }

        $normalizedFilters = $filters;

        foreach (self::FILTER_KEYS as $key) {
            $value = $filters[$key] ?? null;

            if ($key === 'default' && is_string($value)) {
                $normalizedBoolean = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

                if ($normalizedBoolean !== null) {
                    $normalizedFilters[$key] = $normalizedBoolean;

                    continue;
                }
            }

            if ($key === 'ingredient.uuid' && is_array($value)) {
                $normalizedValues = array_values(array_filter(
                    array_map(static fn (mixed $entry): string => trim(urldecode((string) $entry)), $value),
                    static fn (string $entry): bool => $entry !== '',
                ));

                if ($normalizedValues === []) {
                    unset($normalizedFilters[$key]);

                    continue;
                }

                $normalizedFilters[$key] = implode(',', $normalizedValues);

                continue;
            }

            if (! is_string($value)) {
                continue;
            }

            $value = trim(urldecode($value));

            if ($value === '') {
                unset($normalizedFilters[$key]);

                continue;
            }

            $normalizedFilters[$key] = $value;
        }

        $this->merge([
            'filter' => $normalizedFilters,
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'filter' => ['sometimes', 'array'],
            'filter.query' => ['sometimes', 'string', 'min:1', 'max:255'],
            'filter.output\.uuid' => ['sometimes', 'uuid'],
            'filter.output\.name' => ['sometimes', 'string', 'min:1', 'max:255'],
            'filter.output\.class' => ['sometimes', 'string', 'min:1', 'max:255'],
            'filter.output\.type' => ['sometimes', 'string', 'min:1', 'max:255'],
            'filter.default' => ['sometimes', 'boolean'],
            'filter.ingredient' => ['sometimes', 'string', 'min:1', 'max:255'],
            'filter.ingredient\.uuid' => [
                'sometimes',
                'string',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $uuids = array_values(array_filter(
                        array_map(static fn (string $entry): string => trim($entry), explode(',', (string) $value)),
                        static fn (string $entry): bool => $entry !== '',
                    ));

                    if ($uuids === []) {
                        $fail('The '.$attribute.' field must contain at least one UUID.');

                        return;
                    }

                    foreach ($uuids as $uuid) {
                        if (! Str::isUuid($uuid)) {
                            $fail('The '.$attribute.' field must contain valid UUID values.');

                            return;
                        }
                    }
                },
            ],
        ];
    }

    /**
     * @return array{
     *     query?: string,
     *     output.uuid?: string,
     *     output.name?: string,
     *     output.class?: string,
     *     output.type?: string,
     *     default?: bool|string,
     *     ingredient?: string,
     *     ingredient.uuid?: string
     * }
     */
    public function filters(): array
    {
        $filters = data_get($this->validated(), 'filter', []);

        return is_array($filters) ? $filters : [];
    }
}
