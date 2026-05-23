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

    /**
     * Adds canonical resource identity metadata to the meta block
     *
     * @param  string  $type  Resource type, e.g. 'item', 'vehicle'
     * @param  string  $uuid  Entity UUID
     * @param  string|null  $slug  Entity slug (nullable for resources without slugs)
     * @param  string  $apiUrl  Canonical API URL for this resource
     * @param  string  $webUrl  Canonical web URL for this resource
     * @param  string|null  $version  Game version code, e.g. '4.8.0-LIVE.11825000'
     */
    protected function setCanonicalResource(string $type, string $uuid, ?string $slug, string $apiUrl, string $webUrl, ?string $version = null): void
    {
        $this->additional['meta']['resource'] = array_filter([
            'type' => $type,
            'uuid' => $uuid,
            'slug' => $slug,
            'api_url' => $apiUrl,
            'web_url' => $webUrl,
            'version' => $version,
        ], static fn (mixed $value): bool => $value !== null);
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
