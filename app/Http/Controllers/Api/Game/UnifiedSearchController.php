<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Game;

use App\Http\Controllers\Controller;
use App\Http\Resources\Game\Concerns\ResolvesGameVersion;
use App\Support\Filters\ItemFilterLabel;
use App\Support\Formatting\FormatMissionTitle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class UnifiedSearchController extends Controller
{
    use ResolvesGameVersion;

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
            new OA\Response(response: 422, description: 'Validation error - filter[query] is required and must be at least 2 characters', content: new OA\JsonContent(ref: '#/components/schemas/validation_error_response')),
            new OA\Response(response: 429, description: 'Rate limit exceeded. Search endpoints are limited to 60 requests per minute per IP.', content: new OA\JsonContent(ref: '#/components/schemas/rate_limit_error_response')),
        ],
    )]
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'filter.query' => ['required', 'string', 'min:2'],
        ]);

        $query = $validated['filter']['query'];
        $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $query);
        $like = "%{$escaped}%";
        $versionId = $this->gameVersion()->id;

        $rows = DB::select($this->buildSql(), $this->buildBindings($versionId, $like));

        $grouped = collect($rows)
            ->groupBy('type')
            ->map(fn ($items, string $type) => [
                'type' => $type,
                'label' => $this->typeLabel($type),
                'results' => collect($items)->map(fn ($row) => [
                    'name' => $type === 'missions'
                        ? FormatMissionTitle::format($row->name, $row->extra_label)
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

    public function resolve(string $query): RedirectResponse
    {
        return $this->resolveEntity($query, redirectToApi: false);
    }

    #[OA\Get(
        path: '/api/search/{query}',
        operationId: 'resolveSearchQuery',
        description: 'Resolve a search query to the best-matching entity and redirect to its API URL. Useful for quick lookups where you know the exact name.',
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
    public function apiResolve(string $query): RedirectResponse
    {
        return $this->resolveEntity($query, redirectToApi: true);
    }

    private function resolveEntity(string $query, bool $redirectToApi): RedirectResponse
    {
        $versionId = $this->gameVersion()->id;

        $rows = DB::select($this->buildResolveSql(), $this->buildResolveBindings($versionId, $query));

        if ($rows === []) {
            abort(404, 'No matching entity found.');
        }

        $match = $rows[0];

        $url = $redirectToApi
            ? $this->apiUrl($match->type, $match)
            : $this->webUrl($match->type, $match);

        return redirect($url, 302);
    }

    private function buildSql(): string
    {
        $isPgsql = DB::connection()->getDriverName() === 'pgsql';
        $like = $isPgsql ? 'ILIKE' : 'LIKE';
        $nt = $isPgsql ? '::text' : '';
        $uuidCast = static fn (string $col) => $isPgsql ? "{$col}::text" : $col;

        return <<<SQL
            SELECT * FROM (SELECT 'vehicles' AS type, gvd.name, gvd.class_name, NULL{$nt} AS classification,
                     gv.slug, {$uuidCast('gv.uuid')} AS uuid, gvd.career AS extra_label, NULL{$nt} AS item_type
              FROM game_vehicle_data gvd
              JOIN game_vehicles gv ON gv.id = gvd.vehicle_id
              WHERE gvd.game_version_id = ? AND (gvd.name {$like} ? OR gvd.class_name {$like} ?)
              AND gvd.is_player_relevant = TRUE
              LIMIT 5)

             UNION ALL

             SELECT * FROM (SELECT 'items' AS type, gid.name, gid.class_name, gid.classification,
                    gi.slug, {$uuidCast('gi.uuid')} AS uuid, NULL{$nt} AS extra_label, gid.type AS item_type
             FROM game_item_data gid
             JOIN game_items gi ON gi.id = gid.item_id
             WHERE gid.game_version_id = ? AND gid.type != 'NOITEM_Vehicle' AND gid.name != '<= PLACEHOLDER =>' AND (gid.name {$like} ? OR gid.class_name {$like} ? OR gid.type {$like} ?)
             AND gid.is_player_relevant = TRUE
             LIMIT 5)

             UNION ALL

             SELECT * FROM (SELECT 'locations' AS type, gsld.name, NULL{$nt} AS class_name, gsld.type_name AS classification,
                     {$uuidCast('gsl.uuid')} AS slug, {$uuidCast('gsl.uuid')} AS uuid, gsld.system AS extra_label, NULL{$nt} AS item_type
              FROM game_starmap_location_data gsld
              JOIN game_starmap_locations gsl ON gsl.id = gsld.starmap_location_id
              WHERE gsld.game_version_id = ? AND gsld.system IS NOT NULL AND gsld.name != '<= PLACEHOLDER =>' AND gsld.name {$like} ?
              LIMIT 5)

             UNION ALL

             SELECT * FROM (SELECT 'commodities' AS type, gc.name, NULL{$nt} AS class_name, NULL{$nt} AS classification,
                     gc.slug, {$uuidCast('gc.uuid')} AS uuid, gc.key AS extra_label, NULL{$nt} AS item_type
              FROM game_commodities gc
              WHERE (gc.name {$like} ? OR gc.key {$like} ?)
              LIMIT 5)

             UNION ALL

             SELECT * FROM (SELECT 'blueprints' AS type, gbd.output_name AS name, gbd.output_class AS class_name, NULL{$nt} AS classification,
                     gb.slug, {$uuidCast('gb.uuid')} AS uuid, gbd.key AS extra_label, NULL{$nt} AS item_type
              FROM game_blueprint_data gbd
              JOIN game_blueprints gb ON gb.id = gbd.blueprint_id
              WHERE gbd.game_version_id = ? AND (gbd.output_name {$like} ? OR gbd.output_class {$like} ? OR gbd.key {$like} ?)
              LIMIT 5)

             UNION ALL

             {$this->buildMissionSubquery($isPgsql, $like, $nt, $uuidCast)}
        SQL;
    }

    private function buildMissionSubquery(bool $isPgsql, string $like, string $nt, callable $uuidCast): string
    {
        $base = "SELECT * FROM (SELECT 'missions' AS type, gmd.title AS name, NULL{$nt} AS class_name, gmd.mission_type AS classification,
                    gm.slug, {$uuidCast('gm.uuid')} AS uuid, gmd.debug_name AS extra_label, NULL{$nt} AS item_type
             FROM game_mission_data gmd
             JOIN game_missions gm ON gm.id = gmd.mission_id
             WHERE gmd.game_version_id = ?
               AND (gmd.title {$like} ? OR gmd.description {$like} ? OR gmd.debug_name {$like} ?)";

        if ($isPgsql) {
            return "SELECT * FROM (SELECT DISTINCT ON (gmd.game_version_id, gmd.title, gmd.generator_class,
                        gmd.mission_giver, gmd.faction_id, gmd.illegal, gmd.mission_key)
                        'missions' AS type, gmd.title AS name, NULL::text AS class_name,
                        gmd.mission_type AS classification, gm.slug, gm.uuid::text AS uuid,
                        gmd.debug_name AS extra_label, NULL::text AS item_type
                 FROM game_mission_data gmd
                 JOIN game_missions gm ON gm.id = gmd.mission_id
                 WHERE gmd.game_version_id = ?
                   AND (gmd.title ILIKE ? OR gmd.description ILIKE ? OR gmd.debug_name ILIKE ?)
                   AND gmd.title IS NOT NULL AND gmd.title != ''
                 ORDER BY gmd.game_version_id, gmd.title, gmd.generator_class,
                          gmd.mission_giver, gmd.faction_id, gmd.illegal, gmd.mission_key, gmd.id ASC
                 LIMIT 5)";
        }

        return "{$base} LIMIT 5)";
    }

    private function buildResolveSql(): string
    {
        $isPgsql = DB::connection()->getDriverName() === 'pgsql';
        $uuidCast = static fn (string $col) => $isPgsql ? "{$col}::text" : $col;
        $eq = static fn (string $col) => "LOWER({$col}) = LOWER(?)";

        return <<<SQL
            SELECT * FROM (
                SELECT 1 AS priority, 'vehicles' AS type, gv.slug, {$uuidCast('gv.uuid')} AS uuid
                FROM game_vehicle_data gvd
                JOIN game_vehicles gv ON gv.id = gvd.vehicle_id
                WHERE gvd.game_version_id = ?
                  AND ({$eq('gvd.name')} OR {$eq('gvd.class_name')} OR LOWER({$uuidCast('gv.uuid')}) = LOWER(?))
                LIMIT 1
            ) t

            UNION ALL

            SELECT * FROM (
                SELECT 2 AS priority, 'items' AS type, gi.slug, {$uuidCast('gi.uuid')} AS uuid
                FROM game_item_data gid
                JOIN game_items gi ON gi.id = gid.item_id
                WHERE gid.game_version_id = ? AND gid.type != 'NOITEM_Vehicle' AND gid.name != '<= PLACEHOLDER =>'
                  AND ({$eq('gid.name')} OR {$eq('gid.class_name')} OR LOWER({$uuidCast('gi.uuid')}) = LOWER(?))
                LIMIT 1
            ) t

            UNION ALL

            SELECT * FROM (
                SELECT 3 AS priority, 'missions' AS type, gm.slug, {$uuidCast('gm.uuid')} AS uuid
                FROM game_mission_data gmd
                JOIN game_missions gm ON gm.id = gmd.mission_id
                WHERE gmd.game_version_id = ?
                  AND ({$eq('gmd.title')} OR {$eq('gmd.debug_name')} OR LOWER({$uuidCast('gm.uuid')}) = LOWER(?))
                LIMIT 1
            ) t

            UNION ALL

            SELECT * FROM (
                SELECT 4 AS priority, 'locations' AS type, {$uuidCast('gsl.uuid')} AS slug, {$uuidCast('gsl.uuid')} AS uuid
                FROM game_starmap_location_data gsld
                JOIN game_starmap_locations gsl ON gsl.id = gsld.starmap_location_id
                WHERE gsld.game_version_id = ? AND gsld.system IS NOT NULL AND gsld.name != '<= PLACEHOLDER =>'
                  AND ({$eq('gsld.name')} OR LOWER({$uuidCast('gsl.uuid')}) = LOWER(?))
                LIMIT 1
            ) t

            UNION ALL

            SELECT * FROM (
                SELECT 5 AS priority, 'blueprints' AS type, gb.slug, {$uuidCast('gb.uuid')} AS uuid
                FROM game_blueprint_data gbd
                JOIN game_blueprints gb ON gb.id = gbd.blueprint_id
                WHERE gbd.game_version_id = ?
                  AND ({$eq('gbd.output_name')} OR {$eq('gbd.output_class')} OR {$eq('gbd.key')} OR LOWER({$uuidCast('gb.uuid')}) = LOWER(?))
                LIMIT 1
            ) t

            UNION ALL

            SELECT * FROM (
                SELECT 6 AS priority, 'commodities' AS type, gc.slug, {$uuidCast('gc.uuid')} AS uuid
                FROM game_commodities gc
                WHERE ({$eq('gc.name')} OR {$eq('gc.key')} OR LOWER({$uuidCast('gc.uuid')}) = LOWER(?))
                LIMIT 1
            ) t

            ORDER BY priority
            LIMIT 1
        SQL;
    }

    /**
     * @return array<int, string>
     */
    private function buildResolveBindings(int $versionId, string $query): array
    {
        return [
            $versionId, $query, $query, $query,
            $versionId, $query, $query, $query,
            $versionId, $query, $query, $query,
            $versionId, $query, $query,
            $versionId, $query, $query, $query, $query,
            $query, $query, $query,
        ];
    }

    private function buildBindings(int $versionId, string $like): array
    {
        return [
            // Vehicles
            $versionId, $like, $like,
            // Items
            $versionId, $like, $like, $like,
            // Locations
            $versionId, $like,
            // Commodities (no version)
            $like, $like,
            // Blueprints
            $versionId, $like, $like, $like,
            // Missions
            $versionId, $like, $like, $like,
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
