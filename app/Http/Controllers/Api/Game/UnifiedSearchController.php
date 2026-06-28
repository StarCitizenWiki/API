<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Game;

use App\Http\Controllers\Controller;
use App\Http\Resources\Game\Concerns\ResolvesGameVersion;
use App\Models\Game\Blueprint;
use App\Models\Game\Commodity\Commodity;
use App\Models\Game\Item;
use App\Models\Game\Mission\Mission;
use App\Models\Game\StarmapLocation;
use App\Models\Game\Vehicle;
use App\Support\Filters\ItemFilterLabel;
use App\Support\Formatting\FormatMissionText;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

class UnifiedSearchController extends Controller
{
    use ResolvesGameVersion;

    private const int SEARCH_CACHE_TTL_SECONDS = 86400;

    #[OA\Get(
        path: '/api/search',
        operationId: 'searchGameData',
        description: 'Search across items, vehicles, starmap locations, commodities, blueprints, and missions simultaneously. Returns results grouped by type, limited to 5 results per group.',
        summary: 'Unified Search Across All Game Data',
        tags: ['Search'],
        parameters: [
            new OA\Parameter(
                name: 'filter[query]',
                description: 'Search query (minimum 2 characters). Searches names, class names, and other identifiers.',
                in: 'query',
                required: true,
                schema: new OA\Schema(type: 'string', minLength: 2, example: 'Carrack'),
                examples: [
                    new OA\Examples(example: 'ship_search', summary: 'Search for a ship', value: 'carrack'),
                    new OA\Examples(example: 'item_search', summary: 'Search for an item', value: 'arrow'),
                ],
            ),
            new OA\Parameter(ref: '#/components/parameters/version'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Grouped search results', content: new OA\JsonContent(
                examples: [
                    new OA\Examples(
                        example: 'ship_search_results',
                        summary: 'Grouped results for a ship search',
                        value: [
                            'data' => [
                                [
                                    'type' => 'vehicles',
                                    'label' => 'Vehicles',
                                    'results' => [
                                        [
                                            'name' => 'Carrack',
                                            'class_name' => 'ANVL_Carrack',
                                            'classification' => null,
                                            'classification_label' => null,
                                            'item_type_label' => null,
                                            'extra_label' => 'Exploration',
                                            'web_url' => 'https://star-citizen.wiki/vehicles/carrack',
                                            'api_url' => 'https://api.star-citizen.wiki/api/vehicles/carrack',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ),
                ],
                properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'type', type: 'string', example: 'items'),
                            new OA\Property(property: 'label', type: 'string', example: 'Items'),
                            new OA\Property(property: 'results', type: 'array', items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'name', type: 'string', example: 'Arrow'),
                                    new OA\Property(property: 'class_name', type: 'string', example: 'AEGS_Arrow', nullable: true),
                                    new OA\Property(property: 'classification', type: 'string', example: 'fighter', nullable: true),
                                    new OA\Property(property: 'classification_label', type: 'string', example: 'Fighter', nullable: true),
                                    new OA\Property(property: 'item_type_label', type: 'string', example: 'Weapon', nullable: true),
                                    new OA\Property(property: 'extra_label', type: 'string', example: 'Fighter', nullable: true),
                                    new OA\Property(property: 'web_url', type: 'string', example: 'https://example.com/vehicles/arrow'),
                                    new OA\Property(property: 'api_url', type: 'string', example: 'https://example.com/api/vehicles/arrow'),
                                ],
                            )),
                        ],
                    )),
                ],
            )),
            new OA\Response(response: 422, description: 'Validation error - filter[query] is required and must be at least 3 characters', content: new OA\JsonContent(ref: '#/components/schemas/validation_error_response')),
            new OA\Response(response: 429, description: 'Rate limit exceeded. Search endpoints are limited to 60 requests per minute per IP.', content: new OA\JsonContent(ref: '#/components/schemas/rate_limit_error_response')),
        ],
    )]
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'filter.query' => ['required', 'string', 'min:2', 'max:64', 'regex:/^[\pL\pN\s._-]+$/u'],
        ]);

        $query = $validated['filter']['query'];
        $like = '%'.$this->escapeLike($query).'%';
        $versionId = $this->gameVersion()->id;

        $rows = Cache::remember(
            'unified_search:v1:'.$versionId.':'.$query
                |> trim(...)
                |> mb_strtolower(...)
                |> sha1(...),
            now()->addSeconds(self::SEARCH_CACHE_TTL_SECONDS),
            fn (): array => array_map(static fn ($r) => (array) $r, DB::select($this->buildSearchSql(), $this->buildSearchBindings($versionId, $like))),
        );

        $rows = array_map(static fn ($r) => (object) $r, $rows);

        $grouped = collect($rows)
            ->groupBy('type')
            ->map(fn ($items, string $type) => [
                'type' => $type,
                'label' => $this->typeLabel($type),
                'results' => collect($items)->map(fn ($row) => [
                    'name' => $type === 'missions'
                        ? FormatMissionText::format($row->name, $row->extra_label)
                        : $row->name,
                    'class_name' => $row->class_name,
                    'classification' => $row->classification,
                    'classification_label' => $this->classificationLabel($type, $row->classification),
                    'item_type_label' => $type === 'items' ? ItemFilterLabel::resolveType($row->item_type ?? null, null) : null,
                    'extra_label' => $type === 'missions' ? null : $row->extra_label,
                    'web_url' => $this->webUrl($type, $row),
                    'api_url' => $this->apiUrl($type, $row),
                ])->all(),
            ])
            ->values();

        return response()->json([
            'data' => $grouped,
        ]);
    }

    public function resolve(Request $request, string $query): RedirectResponse
    {
        return $this->resolveEntity($request, $query, redirectToApi: false);
    }

    #[OA\Get(
        path: '/api/search/{query}',
        operationId: 'resolveSearchQuery',
        description: 'Resolve a search query to the best-matching entity and redirect to its API URL, preserving query parameters such as locale, include, and version. Useful for quick lookups where you know the exact name.',
        summary: 'Resolve Search Query',
        tags: ['Search'],
        parameters: [
            new OA\Parameter(name: 'query', description: 'Entity name, class name, or UUID to resolve.', in: 'path', required: true, schema: new OA\Schema(type: 'string', example: 'Carrack')),
            new OA\Parameter(ref: '#/components/parameters/version'),
        ],
        responses: [
            new OA\Response(response: 302, description: 'Redirect to the matched entity\'s API URL.'),
            new OA\Response(response: 404, description: 'No matching entity found.', content: new OA\JsonContent(ref: '#/components/schemas/not_found_error_response')),
        ],
    )]
    public function apiResolve(Request $request, string $query): RedirectResponse
    {
        return $this->resolveEntity($request, $query, redirectToApi: true);
    }

    private function resolveEntity(Request $request, string $query, bool $redirectToApi): RedirectResponse
    {
        $versionId = $this->gameVersion()->id;

        $match = Str::isUuid($query)
            ? $this->resolveByUuid($query)
            : $this->resolveByText($query, $versionId);

        if ($match === null) {
            abort(404, 'No matching entity found.');
        }

        $url = $redirectToApi
            ? $this->apiUrl($match->type, $match)
            : $this->webUrl($match->type, $match);

        $queryString = $request->getQueryString();

        if ($queryString !== null && $queryString !== '') {
            $url .= str_contains($url, '?') ? '&'.$queryString : '?'.$queryString;
        }

        return redirect($url);
    }

    /**
     * Search: By UUID
     */
    private function resolveByUuid(string $uuid): ?object
    {
        return $this->firstMatch([
            ['vehicles', fn () => Vehicle::where('uuid', $uuid)->first(['slug', 'uuid'])],
            ['items', fn () => Item::where('uuid', $uuid)->first(['slug', 'uuid'])],
            ['missions', fn () => Mission::where('uuid', $uuid)->first(['slug', 'uuid'])],
            ['locations', fn () => StarmapLocation::where('uuid', $uuid)->first(['slug', 'uuid'])],
            ['blueprints', fn () => Blueprint::where('uuid', $uuid)->first(['slug', 'uuid'])],
            ['commodities', fn () => Commodity::where('uuid', $uuid)->first(['slug', 'uuid'])],
        ]);
    }

    /**
     * Search: By name, class name, etc.
     */
    private function resolveByText(string $query, int $versionId): ?object
    {
        $escaped = $this->escapeLike($query);
        $fuzzy = "%{$escaped}%";

        return $this->firstMatch([
            ['vehicles', fn () => Vehicle::whereHas('data', fn (Builder $q) => $q
                ->where('game_version_id', $versionId)
                ->where(fn (Builder $q) => $this->matchAny($q, ['name', 'display_name', 'class_name'], $escaped)))
                ->first(['slug', 'uuid'])],

            // fuzzy
            ['vehicles', fn () => Vehicle::whereHas('data', fn (Builder $q) => $q
                ->where('game_version_id', $versionId)
                ->where(fn (Builder $q) => $this->matchAny($q, ['name', 'display_name', 'class_name'], $fuzzy)))
                ->first(['slug', 'uuid'])],

            ['items', fn () => Item::whereHas('data', fn (Builder $q) => $q
                ->where('game_version_id', $versionId)
                ->where('type', '!=', 'NOITEM_Vehicle')
                ->where('name', '!=', '<= PLACEHOLDER =>')
                ->where(fn (Builder $q) => $this->matchAny($q, ['name', 'class_name'], $escaped)))
                ->first(['slug', 'uuid'])],

            ['missions', fn () => Mission::whereHas('data', fn (Builder $q) => $q
                ->where('game_version_id', $versionId)
                ->where(fn (Builder $q) => $this->matchAny($q, ['title', 'debug_name'], $escaped)))
                ->first(['slug', 'uuid'])],

            ['locations', fn () => StarmapLocation::whereHas('data', fn (Builder $q) => $q
                ->where('game_version_id', $versionId)
                ->whereNotNull('system')
                ->where('name', '!=', '<= PLACEHOLDER =>')
                ->where(fn (Builder $q) => $this->matchAny($q, ['name'], $escaped)))
                ->first(['slug', 'uuid'])],

            ['blueprints', fn () => Blueprint::whereHas('data', fn (Builder $q) => $q
                ->where('game_version_id', $versionId)
                ->where(fn (Builder $q) => $this->matchAny($q, ['output_name', 'output_class', 'key'], $escaped)))
                ->first(['slug', 'uuid'])],

            ['commodities', fn () => Commodity::query()
                ->where(fn (Builder $q) => $this->matchAny($q, ['name', 'key'], $escaped))
                ->first(['slug', 'uuid'])],
        ]);
    }

    /**
     * Run lookups in order, returning the first match tagged with its entity type.
     *
     * @param  array<int, array{0: string, 1: callable(): ?object}>  $lookups
     */
    private function firstMatch(array $lookups): ?object
    {
        foreach ($lookups as [$type, $query]) {
            $row = $query();

            if ($row !== null) {
                $row->type = $type;

                return $row;
            }
        }

        return null;
    }

    /**
     * Add an OR-group of ILIKE conditions across the given columns.
     *
     * Uses whereRaw with an explicit ESCAPE clause so the backslash escape in
     * the bound pattern is interpreted correctly.
     *
     * @param  string[]  $columns
     */
    private function matchAny(Builder $query, array $columns, string $value): Builder
    {
        foreach ($columns as $index => $column) {
            $sql = "\"{$column}\" ilike ? ESCAPE '\\'";

            if ($index === 0) {
                $query->whereRaw($sql, [$value]);
            } else {
                $query->orWhereRaw($sql, [$value]);
            }
        }

        return $query;
    }

    private function escapeLike(string $query): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $query);
    }

    private function buildSearchSql(): string
    {
        $nt = '::text';
        $uuidCast = static fn (string $col) => "{$col}::text";

        return <<<SQL
            SELECT * FROM (SELECT 'vehicles' AS type, gvd.name, gvd.class_name, NULL{$nt} AS classification,
                     gv.slug, {$uuidCast('gv.uuid')} AS uuid, gvd.career AS extra_label, NULL{$nt} AS item_type
              FROM game_vehicle_data gvd
              JOIN game_vehicles gv ON gv.id = gvd.vehicle_id
              WHERE gvd.game_version_id = ? AND (gvd.name ILIKE ? OR gvd.display_name ILIKE ? OR gvd.class_name ILIKE ?)
              AND gvd.is_player_relevant = TRUE
              LIMIT 5) as vehicles

             UNION ALL

             SELECT * FROM (SELECT 'items' AS type, gid.name, gid.class_name, gid.classification,
                    gi.slug, {$uuidCast('gi.uuid')} AS uuid, NULL{$nt} AS extra_label, gid.type AS item_type
             FROM game_item_data gid
             JOIN game_items gi ON gi.id = gid.item_id
             WHERE gid.game_version_id = ? AND gid.type != 'NOITEM_Vehicle' AND gid.name != '<= PLACEHOLDER =>' AND (gid.name ILIKE ? OR gid.class_name ILIKE ? OR gid.type ILIKE ?)
             AND gid.is_player_relevant = TRUE
             LIMIT 5) as items

             UNION ALL

             SELECT * FROM (SELECT 'locations' AS type, gsld.name, NULL{$nt} AS class_name, gsld.type_name AS classification,
                     {$uuidCast('gsl.uuid')} AS slug, {$uuidCast('gsl.uuid')} AS uuid, gsld.system AS extra_label, NULL{$nt} AS item_type
              FROM game_starmap_location_data gsld
              JOIN game_starmap_locations gsl ON gsl.id = gsld.starmap_location_id
              WHERE gsld.game_version_id = ? AND gsld.system IS NOT NULL AND gsld.name != '<= PLACEHOLDER =>' AND gsld.name ILIKE ?
              LIMIT 5) as locations

             UNION ALL

             SELECT * FROM (SELECT 'commodities' AS type, gc.name, NULL{$nt} AS class_name, NULL{$nt} AS classification,
                     gc.slug, {$uuidCast('gc.uuid')} AS uuid, gc.key AS extra_label, NULL{$nt} AS item_type
              FROM game_commodities gc
              WHERE (gc.name ILIKE ? OR gc.key ILIKE ?)
              LIMIT 5) as commodities

             UNION ALL

             SELECT * FROM (SELECT 'blueprints' AS type, gbd.output_name AS name, gbd.output_class AS class_name, NULL{$nt} AS classification,
                     gb.slug, {$uuidCast('gb.uuid')} AS uuid, gbd.key AS extra_label, NULL{$nt} AS item_type
              FROM game_blueprint_data gbd
              JOIN game_blueprints gb ON gb.id = gbd.blueprint_id
              WHERE gbd.game_version_id = ? AND (gbd.output_name ILIKE ? OR gbd.output_class ILIKE ? OR gbd.key ILIKE ?)
              LIMIT 5) as blieprints

             UNION ALL

             {$this->buildMissionSubquery()}
        SQL;
    }

    private function buildMissionSubquery(): string
    {
        return "SELECT * FROM (SELECT DISTINCT ON (gmd.game_version_id, gmd.title, gmd.generator_class,
                    gmd.mission_giver, gmd.faction_id, gmd.illegal, gmd.mission_key)
                    'missions' AS type, gmd.title AS name, NULL::text AS class_name,
                    gmd.mission_type AS classification, gm.slug, gm.uuid::text AS uuid,
                    gmd.debug_name AS extra_label, NULL::text AS item_type
             FROM game_mission_data gmd
             JOIN game_missions gm ON gm.id = gmd.mission_id
             WHERE gmd.game_version_id = ?
               AND (gmd.title ILIKE ? OR gmd.debug_name ILIKE ?)
               AND gmd.title IS NOT NULL AND gmd.title != ''
             ORDER BY gmd.game_version_id, gmd.title, gmd.generator_class,
                      gmd.mission_giver, gmd.faction_id, gmd.illegal, gmd.mission_key, gmd.id ASC
             LIMIT 5) as grouped_missions";
    }

    private function buildSearchBindings(int $versionId, string $like): array
    {
        return [
            // Vehicles
            $versionId, $like, $like, $like,
            // Items
            $versionId, $like, $like, $like,
            // Locations
            $versionId, $like,
            // Commodities (no version)
            $like, $like,
            // Blueprints
            $versionId, $like, $like, $like,
            // Missions
            $versionId, $like, $like,
        ];
    }

    private function typeLabel(string $type): string
    {
        return match ($type) {
            'items' => 'Items',
            'vehicles' => 'Vehicles',
            'locations' => 'Locations',
            'commodities' => 'Commodities',
            'blueprints' => 'Blueprints',
            'missions' => 'Missions',
            default => ucfirst($type),
        };
    }

    private function classificationLabel(string $type, ?string $classification): ?string
    {
        if ($classification === null) {
            return null;
        }

        if ($type === 'items') {
            return ItemFilterLabel::resolveClassification($classification, null);
        }

        return $classification;
    }

    private function apiUrl(string $type, object $row): string
    {
        return match ($type) {
            'items' => route('items.show', ['identifier' => $row->slug ?? $row->uuid]),
            'vehicles' => route('vehicles.show', ['vehicle' => $row->slug ?? $row->uuid]),
            'locations' => route('locations.show', ['identifier' => $row->uuid]),
            'commodities' => route('commodities.show', ['commodity' => $row->slug ?? $row->uuid]),
            'blueprints' => route('blueprints.show', ['blueprint' => $row->slug ?? $row->uuid]),
            'missions' => route('missions.show', ['mission' => $row->slug ?? $row->uuid]),
            default => '',
        };
    }

    private function webUrl(string $type, object $row): string
    {
        return match ($type) {
            'items' => route('web.items.show', ['item' => $row->slug ?? $row->uuid]),
            'vehicles' => route('web.vehicles.show', ['vehicle' => $row->slug ?? $row->uuid]),
            'locations' => route('web.locations.show', ['identifier' => $row->uuid]),
            'commodities' => route('web.commodities.show', ['identifier' => $row->slug ?? $row->uuid]),
            'blueprints' => route('web.blueprints.show', ['blueprint' => $row->slug ?? $row->uuid]),
            'missions' => route('web.missions.show', ['mission' => $row->slug ?? $row->uuid]),
            default => '',
        };
    }
}
