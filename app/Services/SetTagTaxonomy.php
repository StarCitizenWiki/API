<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Game\EntityTag;
use Closure;

/** Callable-backed lookup so DB-less tests can inject parent chains. */
class SetTagTaxonomy
{
    /**
     * @param  Closure(): array<int, string>  $setTagUuids  uuids of the rows named 'Set'
     * @param  Closure(string): ?string  $parentUuidOf  uuid -> parent_uuid, null at root
     * @param  Closure(string): array<int, string>|null  $tagUuidsNamed  role name -> uuids of rows with that name
     */
    public function __construct(
        private readonly Closure $setTagUuids,
        private readonly Closure $parentUuidOf,
        private readonly ?Closure $tagUuidsNamed = null,
    ) {}

    public static function fromDatabase(): self
    {
        return new self(
            static fn (): array => EntityTag::query()->where('name', 'Set')->pluck('uuid')->all(),
            static fn (string $uuid): ?string => EntityTag::query()->where('uuid', $uuid)->value('parent_uuid'),
            static fn (string $name): array => EntityTag::query()->where('name', $name)->pluck('uuid')->all(),
        );
    }

    /** @return array<int, string> */
    public function setTagUuids(): array
    {
        return ($this->setTagUuids)();
    }

    /** @return array<int, string> */
    public function tagUuidsNamed(string $name): array
    {
        if ($this->tagUuidsNamed === null) {
            return [];
        }

        return ($this->tagUuidsNamed)($name);
    }

    public function parentUuidOf(string $uuid): ?string
    {
        return ($this->parentUuidOf)($uuid);
    }
}
