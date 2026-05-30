<?php

declare(strict_types=1);

namespace App\Services\Game;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class SlugService
{
    private const int MAX_RETRIES = 10;

    /**
     * Assign a unique slug to a model and persist it.
     *
     * Uses retry-on-conflict: attempts to save, catches unique constraint
     * violations (SQLSTATE 23505), and retries with an incremented suffix.
     * This is safe for parallel imports - no TOCTOU gap.
     *
     * @param  class-string<Model>  $modelClass
     * @param  list<string>  $localMap  Slugs already claimed in this batch (passed by reference)
     * @param  string  $slugColumn  The column holding the slug
     */
    public function assignUniqueSlug(Model $model, string $baseName, string $fallback, string $slugColumn = 'slug'): string
    {
        $baseSlug = Str::slug($baseName);

        if ($baseSlug === '') {
            $baseSlug = $fallback;
        }

        $counter = 1;

        for ($attempt = 0; $attempt <= self::MAX_RETRIES; $attempt++) {
            $slug = $counter === 1 ? $baseSlug : $baseSlug.'-'.$counter;

            $model->$slugColumn = $slug;

            try {
                DB::transaction(static fn () => $model->save());

                return $slug;
            } catch (UniqueConstraintViolationException $e) {
                $counter++;

                continue;
            }
        }

        $model->$slugColumn = $fallback;

        try {
            DB::transaction(static fn () => $model->save());

            return $fallback;
        } catch (UniqueConstraintViolationException $e) {
        }

        throw new RuntimeException(sprintf(
            'Could not generate a unique slug for %s after %d attempts.',
            $model::class,
            self::MAX_RETRIES
        ));
    }

    /**
     * Generate a unique slug for batch upsert operations.
     *
     * Checks both a local in-memory map and the database for conflicts.
     * The actual upsert call handles atomicity - this method just
     * reduces unnecessary collisions.
     *
     * @param  class-string<Model>  $modelClass
     * @param  list<string>  $localMap  Slugs already claimed in this batch (passed by reference)
     * @param  string  $slugColumn  The column holding the slug
     * @param  array<string, list<string>>  $ignoredValuesByColumn  Existing rows to ignore while checking database conflicts
     */
    public function generateUniqueSlugForBatch(
        string $baseSlug,
        array &$localMap,
        string $modelClass,
        string $slugColumn = 'slug',
        array $ignoredValuesByColumn = [],
    ): string {
        $slug = $baseSlug;
        $counter = 2;

        while (in_array($slug, $localMap, true) || $this->slugExists($modelClass, $slugColumn, $slug, $ignoredValuesByColumn)) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        $localMap[] = $slug;

        return $slug;
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @param  array<string, list<string>>  $ignoredValuesByColumn
     */
    private function slugExists(string $modelClass, string $slugColumn, string $slug, array $ignoredValuesByColumn): bool
    {
        $query = $modelClass::query()->where($slugColumn, $slug);

        foreach ($ignoredValuesByColumn as $column => $values) {
            if ($values !== []) {
                $query->whereNotIn($column, $values);
            }
        }

        return $query->exists();
    }
}
