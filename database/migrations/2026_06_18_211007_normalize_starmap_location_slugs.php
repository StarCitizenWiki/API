<?php

declare(strict_types=1);

use App\Models\Game\StarmapLocation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Heal slugs that drifted under repeated imports: singletons drop a stale -N
 * suffix; duplicate name families are renumbered by uuid. ImportStarmapData
 * now preserves slugs, so this cannot recur.
 */
return new class extends Migration
{
    public function up(): void
    {
        $proposed = $this->proposedSlugs();

        if ($proposed === []) {
            return;
        }

        if (count(array_unique($proposed)) !== count($proposed)) {
            throw new RuntimeException('Proposed slug set is not unique; refusing to heal.');
        }

        DB::transaction(function () use ($proposed): void {
            $table = (new StarmapLocation)->getTable();

            $current = DB::table($table)->pluck('slug', 'id')->all();

            $targets = array_flip($proposed);
            $toPark = [];
            foreach ($proposed as $id => $newSlug) {
                $slug = $current[$id] ?? null;

                if ($newSlug !== $slug || array_key_exists($slug, $targets)) {
                    $toPark[$id] = $newSlug;
                }
            }

            foreach ($toPark as $id => $newSlug) {
                DB::table($table)->where('id', $id)->update([
                    'slug' => '__tmp_'.$id,
                    'updated_at' => now(),
                ]);
            }

            foreach ($toPark as $id => $newSlug) {
                DB::table($table)->where('id', $id)->update([
                    'slug' => $newSlug,
                    'updated_at' => now(),
                ]);
            }
        });
    }

    public function down(): void {}

    /**
     * Canonical slug per row (id => slug), keyed by family: the first member by
     * uuid claims the base, the rest get sequential -N. Singletons with a stale
     * suffix heal to the clean base.
     *
     * @return array<int, string>
     */
    private function proposedSlugs(): array
    {
        $rows = StarmapLocation::query()
            ->get(['id', 'uuid', 'slug'])
            ->sortBy('uuid')
            ->values();

        $byBase = [];
        foreach ($rows as $location) {
            $base = preg_replace('/-[0-9]+$/', '', (string) $location->slug) ?? (string) $location->slug;
            $byBase[$base][] = $location;
        }

        $proposed = [];
        foreach ($byBase as $base => $members) {
            $familySize = count($members);

            foreach ($members as $index => $location) {
                $hasSuffix = preg_match('/-[0-9]+$/', (string) $location->slug) === 1;

                if ($familySize === 1 && $hasSuffix) {
                    $proposed[$location->id] = $base;
                } elseif ($familySize === 1) {
                    $proposed[$location->id] = $location->slug;
                } elseif ($index === 0) {
                    $proposed[$location->id] = $base;
                } else {
                    $proposed[$location->id] = $base.'-'.($index + 1);
                }
            }
        }

        return $proposed;
    }
};
