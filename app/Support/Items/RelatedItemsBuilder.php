<?php

declare(strict_types=1);

namespace App\Support\Items;

use App\Models\SC\Item\Item;

class RelatedItemsBuilder
{
    /**
     * Build the related_items payload for a given Item.
     *
     * @return array{set_name:?string,base_item:?array,variant_items:array<int,array>,set_items:array<int,array>}
     */
    public function build(Item $item): array
    {
        [$baseItem, $groupItems] = $this->gatherVariantGroup($item);

        $names = collect($groupItems)->pluck('name')->all();
        if ($baseItem !== null) {
            array_unshift($names, $baseItem->name);
        }

        [$setName, $variantNames] = $this->computeSetNameAndVariantNames($names, $baseItem, $groupItems);

        $base = $baseItem !== null ? $this->toBaseLink($baseItem, $setName, true) : null;
        $variants = collect($groupItems)
            ->filter(fn (Item $i) => $i->uuid !== $item->uuid) // exclude current item
            ->map(function (Item $it) use ($variantNames) {
                $link = $this->toBaseLink($it, null, false);
                $link['variant_name'] = $variantNames[$it->uuid] ?? null;

                return $link;
            })
            ->values()
            ->all();

        $setItems = $this->findSetItems($item);

        return [
            'set_name' => $setName,
            'base_item' => $base,
            'variant_items' => $variants,
            'set_items' => $setItems,
        ];
    }

    /**
     * Determine base item and full variant group for an item.
     *
     * @return array{0:?Item,1:array<int,Item>}
     */
    public function gatherVariantGroup(Item $item): array
    {
        if ($item->base_id === null) {
            $base = $item;
            $siblings = $item->variants()->get()->all();
        } else {
            $base = $item->baseVariant()->first();
            $siblings = $base?->variants()->get()->all() ?? [];
        }

        return [$base, $siblings];
    }

    /**
     * Compute set name and per-item variant names.
     * - set name: longest common prefix among names
     * - variant name: item name with the set name prefix removed (trimmed); if empty, "Base".
     *
     * @param  array<int,string>  $names
     * @param  array<int,Item>  $group
     * @return array{0:?string,1:array<string,string>} [setName, map(uuid=>variantName)]
     */
    public function computeSetNameAndVariantNames(array $names, ?Item $base, array $group): array
    {
        $rawPrefix = $this->longestCommonPrefix($names);
        if ($rawPrefix !== null) {
            $endsWithSpace = str_ends_with($rawPrefix, ' ');
            $setName = rtrim($rawPrefix);
            if (! $endsWithSpace) {
                $lastSpace = strrpos($setName, ' ');
                if ($lastSpace !== false) {
                    $setName = substr($setName, 0, $lastSpace);
                }
            }
            $setName = $setName === '' ? null : $setName;
        } else {
            $setName = null;
        }

        $map = [];
        if ($base !== null) {
            $map[$base->uuid] = 'Base';
        }

        foreach ($group as $it) {
            $variant = $this->stripPrefix($it->name, (string) $setName);
            $map[$it->uuid] = $variant === '' ? 'Base' : $variant;
        }

        return [$setName, $map];
    }

    /**
     * Find set items for armor/clothing by replacing the part token in class_name.
     *
     * @return array<int,array{uuid:string,name:string,type:?string,sub_type:?string,link:string}>
     */
    public function findSetItems(Item $item): array
    {
        $className = $item->class_name ?? '';
        if ($className === '') {
            return [];
        }

        $parts = config('item_sets.parts', ['helmet', 'core', 'arms', 'legs']);
        $currentPart = null;
        foreach ($parts as $part) {
            if (str_contains($className, '_'.$part.'_')) {
                $currentPart = $part;
                break;
            }
        }
        if ($currentPart === null) {
            return [];
        }

        $set = [];
        foreach ($parts as $part) {
            if ($part === $currentPart) {
                continue;
            }
            $candidate = $this->replaceFirst('_'.$currentPart.'_', '_'.$part.'_', $className);
            $found = Item::query()->where('class_name', $candidate)->first();
            if ($found !== null && $found->uuid !== $item->uuid) {
                $set[] = [
                    'uuid' => $found->uuid,
                    'name' => $found->name,
                    'type' => $found->type,
                    'sub_type' => $found->sub_type,
                    'link' => $this->makeLink($found->uuid),
                ];
            }
        }

        return $set;
    }

    private function toBaseLink(Item $it, ?string $setName, bool $includeVariantName): array
    {
        $link = [
            'uuid' => $it->uuid,
            'name' => $it->name,
            'link' => $this->makeLink($it->uuid),
        ];
        if ($includeVariantName && $setName !== null) {
            $variant = $this->stripPrefix($it->name, $setName);
            $link['variant_name'] = $variant === '' ? 'Base' : $variant;
        }

        return $link;
    }

    private function makeLink(string $uuid): string
    {
        $baseUrl = rtrim((string) config('app.url'), '/');

        return $baseUrl.'/api/v2/items/'.$uuid;
    }

    private function replaceFirst(string $search, string $replace, string $subject): string
    {
        $pos = strpos($subject, $search);
        if ($pos === false) {
            return $subject;
        }

        return substr($subject, 0, $pos).$replace.substr($subject, $pos + strlen($search));
    }

    private function longestCommonPrefix(array $strings): ?string
    {
        if (count($strings) === 0) {
            return null;
        }
        $prefix = $strings[0];
        foreach ($strings as $s) {
            $i = 0;
            $max = min(strlen($prefix), strlen($s));
            while ($i < $max && $prefix[$i] === $s[$i]) {
                $i++;
            }
            $prefix = substr($prefix, 0, $i);
            if ($prefix === '') {
                return null;
            }
        }

        return $prefix;
    }

    private function stripPrefix(string $name, string $prefix): string
    {
        if ($prefix === '') {
            return $name;
        }
        if (str_starts_with($name, $prefix)) {
            $rest = substr($name, strlen($prefix));

            return ltrim($rest);
        }

        return $name;
    }
}
