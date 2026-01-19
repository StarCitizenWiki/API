<?php

declare(strict_types=1);

namespace App\Http\Includes;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Includes\IncludeInterface;

/**
 * A no-op include handler that allows QueryBuilder to accept an include
 * without attempting to eager-load it as an Eloquent relationship.
 *
 * This is useful for computed includes that are handled manually in
 * API Resources (like related_items built by RelatedItemsBuilder).
 */
class PassthroughInclude implements IncludeInterface
{
    public function __invoke(Builder $query, string $include): void
    {
        // Intentionally empty - this is a passthrough
        // The actual include logic is handled in ItemResource
    }
}
