<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Game\EntityTag;
use App\Models\Game\ItemData;
use Closure;
use Illuminate\Support\Facades\Log;

/**
 * Entity-tag taxonomy: which entity_tag_map entries descend from which
 * role row ('Set', 'Part', 'Manufacturer', ...). Owns the chain walking,
 * role-row lookups and their memos so callers only fold results.
 *
 * Callable-backed lookups so DB-less tests can inject parent chains.
 */
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

    // null = not resolved yet; false = unique 'Set' lookup failed
    private string|false|null $setTagUuid = null;

    /** @var array<string, string|null> uuid -> parent_uuid */
    private array $tagParentCache = [];

    /** @var array<string, array<string, string>> "Role|item id" => tag uuid => name */
    private array $leafNameCache = [];

    /** @var array<string, array<int, string>> role name -> taxonomy row uuids */
    private array $roleRowUuidCache = [];

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

    /**
     * The unique row named 'Set', when exactly one exists.
     */
    public function setRowUuid(): ?string
    {
        if ($this->setTagUuid !== null) {
            return $this->setTagUuid === false ? null : $this->setTagUuid;
        }

        $uuids = $this->setTagUuids();

        if (count($uuids) !== 1) {
            Log::warning('game_entity_tags has no unique "Set" row; entity-tag set names disabled', [
                'set_tag_uuid_count' => count($uuids),
            ]);

            $this->setTagUuid = false;

            return null;
        }

        return $this->setTagUuid = $uuids[0];
    }

    /** @return array<int, string> row uuids named $role; 'Set' needs the unique-row memo */
    public function roleRowUuids(string $role): array
    {
        if ($role === 'Set') {
            return array_filter([$this->setRowUuid()]);
        }

        return $this->roleRowUuidCache[$role] ??= $this->tagUuidsNamed($role);
    }

    /** True when $uuid's parent chain passes $rowUuid; cycles terminate the walk. */
    public function chainPassesRow(string $uuid, string $rowUuid): bool
    {
        $visited = [];
        $current = $uuid;

        while ($current !== null) {
            if ($current === $rowUuid) {
                return true;
            }

            if (isset($visited[$current])) {
                return false;
            }

            $visited[$current] = true;

            if (! array_key_exists($current, $this->tagParentCache)) {
                $this->tagParentCache[$current] = $this->parentUuidOf($current);
            }

            $current = $this->tagParentCache[$current];
        }

        return false;
    }

    /**
     * @return array<string, string> tag uuid => name of entries passing the
     *                               Set row. Deliberately unlike roleLeafNames('Set'): bare
     *                               'Set'-named rows stay counted toward the 2/3 coverage when
     *                               CIG leaves a member untagged next to named siblings.
     */
    public function setTagCandidates(ItemData $itemData, string $setRowUuid): array
    {
        $candidates = [];

        foreach ($this->entityTagMapEntries($itemData) as $uuid => $name) {
            if ($this->chainPassesRow($uuid, $setRowUuid)) {
                $candidates[$uuid] = $name;
            }
        }

        return $candidates;
    }

    /**
     * @return array<string, string> named leaf uuid => name, whose chain
     *                               passes a row named $role (bare role-named rows stay out)
     */
    public function roleLeafNames(ItemData $itemData, string $role): array
    {
        $cacheKey = $role.'|'.$itemData->id;

        if (array_key_exists($cacheKey, $this->leafNameCache)) {
            return $this->leafNameCache[$cacheKey];
        }

        $leaves = [];

        foreach ($this->entityTagMapEntries($itemData) as $uuid => $name) {
            foreach ($this->roleRowUuids($role) as $rowUuid) {
                if ($name !== $role && $this->chainPassesRow($uuid, $rowUuid)) {
                    $leaves[$uuid] = $name;
                }
            }
        }

        return $this->leafNameCache[$cacheKey] = $leaves;
    }

    /** @return array<string, string> tag uuid => name from the item's raw entity_tag_map */
    public function entityTagMapEntries(ItemData $itemData): array
    {
        $tagMap = $itemData->data['entity_tag_map'] ?? null;

        if (! is_array($tagMap)) {
            return [];
        }

        $entries = [];

        foreach ($tagMap as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $uuid = $entry['tag'] ?? null;
            $name = $entry['name'] ?? null;

            if (is_string($uuid) && $uuid !== '' && is_string($name) && $name !== '') {
                $entries[$uuid] = $name;
            }
        }

        return $entries;
    }
}
