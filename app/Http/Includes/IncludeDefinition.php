<?php

declare(strict_types=1);

namespace App\Http\Includes;

use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\Includes\IncludeInterface;

/**
 * Single source of truth for a controller's include.
 * Each instance carries the public name and the optional Spatie AllowedInclude.
 * Controllers define one array of these and derive both allowedIncludes() and includeNames().
 */
final class IncludeDefinition
{
    /**
     * @param  string  $name  Public-facing include name (e.g. 'shops.items')
     * @param  AllowedInclude|null  $spatieInclude  The Spatie AllowedInclude, or null for always-loaded relations
     */
    public function __construct(
        public readonly string $name,
        public readonly ?AllowedInclude $spatieInclude = null,
    ) {}

    /**
     * Standard Eloquent relationship include.
     */
    public static function relationship(string $name, ?string $relation = null): self
    {
        return new self(
            $name,
            AllowedInclude::relationship($name, $relation ?? $name),
        );
    }

    /**
     * Custom include using an IncludeInterface implementation (e.g. CustomEagerLoadInclude).
     */
    public static function custom(string $name, IncludeInterface $include): self
    {
        return new self(
            $name,
            AllowedInclude::custom($name, $include),
        );
    }

    /**
     * Callback-based include for scoped eager loading.
     *
     * @param  callable  $callback  function (Illuminate\Database\Eloquent\Relation $query): void
     */
    public static function callback(string $name, callable $callback): self
    {
        return new self(
            $name,
            AllowedInclude::callback($name, $callback),
        );
    }

    /**
     * A relation that is always loaded via ->with() in the controller.
     * Not registered with Spatie, but still advertised in metadata for documentation.
     */
    public static function alwaysLoaded(string $name): self
    {
        return new self($name);
    }

    /**
     * Resolve to an AllowedInclude for Spatie's QueryBuilder.
     * Returns null for always-loaded includes.
     */
    public function toSpatieInclude(): ?AllowedInclude
    {
        return $this->spatieInclude;
    }

    /**
     * @param  array<int, IncludeDefinition>  $definitions
     * @return array<int, AllowedInclude>
     */
    public static function toSpatieIncludes(array $definitions): array
    {
        return array_values(array_filter(
            array_map(static fn (self $d): ?AllowedInclude => $d->toSpatieInclude(), $definitions),
        ));
    }

    /**
     * @param  array<int, IncludeDefinition>  $definitions
     * @return array<int, string>
     */
    public static function toNames(array $definitions): array
    {
        return array_map(static fn (self $d): string => $d->name, $definitions);
    }
}
