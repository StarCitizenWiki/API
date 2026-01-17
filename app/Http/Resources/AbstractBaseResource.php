<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Carbon\Carbon;
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
        return [
            'variants',
        ];
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
}
