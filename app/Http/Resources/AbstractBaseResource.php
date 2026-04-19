<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class AbstractBaseResource extends JsonResource
{
    public function __construct($resource)
    {
        parent::__construct($resource);

        $this->additional['meta'] = [
            'processed_at' => Carbon::now()->toDateTimeString(),
            'valid_relations' => static::validIncludes(),
        ];
    }

    public static function validIncludes(): array
    {
        return [];
    }

    public function addMetadata(mixed $key, mixed $value = null): void
    {
        if (is_array($key)) {
            $this->additional['meta'] = array_replace_recursive($this->additional['meta'], $key);

            return;
        }

        if (
            isset($this->additional['meta'][$key]) &&
            is_array($this->additional['meta'][$key]) &&
            is_array($value)
        ) {
            $this->additional['meta'][$key] = array_replace_recursive($this->additional['meta'][$key], $value);

            return;
        }

        $this->additional['meta'][$key] = $value;
    }

    protected function urlWithVersion(string $url, Request $request): string
    {
        $version = $request->query('version');

        if (! is_string($version) || $version === '') {
            return $url;
        }

        return url()->query($url, ['version' => $version]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function arrayString(array $data, string $key): string
    {
        $value = $data[$key] ?? null;

        return is_scalar($value) ? (string) $value : '';
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function arrayNullableString(array $data, string $key): ?string
    {
        $value = $data[$key] ?? null;

        if (! is_scalar($value)) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function arrayNullableInt(array $data, string $key): ?int
    {
        $value = $data[$key] ?? null;

        return is_numeric($value) ? (int) $value : null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function arrayNullableFloat(array $data, string $key): ?float
    {
        $value = $data[$key] ?? null;

        return is_numeric($value) ? (float) $value : null;
    }

    protected function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    protected function nullableInt(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }

    protected function nullableNumeric(mixed $value): int|float|null
    {
        if (! is_numeric($value)) {
            return null;
        }

        $numericValue = $value + 0;

        if (is_float($numericValue) && floor($numericValue) === $numericValue) {
            return (int) $numericValue;
        }

        return $numericValue;
    }
}
