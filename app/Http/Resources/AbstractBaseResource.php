<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Http\Resources\Game\Concerns\NormalizesValues;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class AbstractBaseResource extends JsonResource
{
    use NormalizesValues;

    public function __construct($resource)
    {
        parent::__construct($resource);

        $this->additional['meta'] = [
            'processed_at' => Carbon::now()->toDateTimeString(),
        ];
    }

    /**
     * Set the valid_relations metadata for this resource.
     * Called by controllers after construction to inject include names.
     *
     * @param  array<int, string>  $includes
     */
    public function setValidIncludes(array $includes): static
    {
        $this->additional['meta']['valid_relations'] = $includes;

        return $this;
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
    protected function arrayNullableFloat(array $data, string $key): ?float
    {
        $value = $data[$key] ?? null;

        return is_numeric($value) ? (float) $value : null;
    }

    protected function nullableInt(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }

    protected function stripItemTypePrefix(?string $type): ?string
    {
        if ($type === null) {
            return null;
        }

        return str_replace('NOITEM_', '', $type);
    }
}
