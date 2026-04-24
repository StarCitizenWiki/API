<?php

declare(strict_types=1);

namespace App\Services\Game;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;
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
     * This is safe for parallel imports — no TOCTOU gap.
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
                $model->save();

                return $slug;
            } catch (UniqueConstraintViolationException $e) {
                $counter++;

                continue;
            }
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
     * The actual upsert call handles atomicity — this method just
     * reduces unnecessary collisions.
     *
     * @param  class-string<Model>  $modelClass
     * @param  list<string>  $localMap  Slugs already claimed in this batch (passed by reference)
     * @param  string  $slugColumn  The column holding the slug
     */
    public function generateUniqueSlugForBatch(string $baseSlug, array &$localMap, string $modelClass, string $slugColumn = 'slug'): string
    {
        $slug = $baseSlug;
        $counter = 2;

        while (in_array($slug, $localMap, true) || $modelClass::query()->where($slugColumn, $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        $localMap[] = $slug;

        return $slug;
    }
}
