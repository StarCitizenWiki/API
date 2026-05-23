<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Symfony\Component\Yaml\Yaml;

/**
 * @return array<string, mixed>
 */
function openApiOperationById(array $spec, string $operationId): array
{
    foreach ($spec['paths'] as $pathItem) {
        foreach ($pathItem as $operation) {
            if (is_array($operation) && ($operation['operationId'] ?? '') === $operationId) {
                return $operation;
            }
        }
    }

    return [];
}

describe('OpenAPI specification', function (): void {
    beforeEach(function (): void {
        static $cachedYaml = null;
        static $cachedSpec = null;

        if ($cachedYaml === null) {
            $cachedYaml = File::get(base_path('swagger.yaml'));
            $cachedSpec = Yaml::parse($cachedYaml);
        }

        $this->yaml = $cachedYaml;
        $this->spec = $cachedSpec;
    });

    it('contains stable operation IDs', function (): void {
        foreach ([
            'searchGameData',
            'resolveSearchQuery',
            'getOpenApiSpec',
            'listVehicles',
            'getVehicle',
            'getGroundVehicle',
            'getGravlevVehicle',
            'listItems',
            'getItem',
            'getWeapon',
            'getWeaponAttachment',
            'getClothingItem',
            'getArmor',
            'getFood',
            'getVehicleWeapon',
            'getVehicleItem',
            'listCommodities',
            'getCommodity',
            'getDefaultGameVersion',
            'listGameVersions',
            'getGameVersion',
            'listMissions',
            'getMission',
            'listLocations',
            'getLocation',
            'listBlueprints',
            'getBlueprint',
            'listManufacturers',
            'getManufacturer',
            'listFactions',
            'getFaction',
            'listCommLinks',
            'getCommLink',
            'listStats',
            'getLatestStats',
            'getAuthenticatedUser',
            'findSimilarImages',
        ] as $operationId) {
            expect($this->yaml)->toContain("operationId: {$operationId}");
        }
    });

    it('keeps operation IDs stable and unique', function (): void {
        preg_match_all('/operationId:\s+([a-f0-9]{16,})/', $this->yaml, $hashMatches);
        expect($hashMatches[0])->toBeEmpty('Found hash-like operation IDs: '.implode(', ', $hashMatches[1]));

        preg_match_all('/operationId:\s+(\S+)/', $this->yaml, $operationMatches);
        $duplicates = array_filter(array_count_values($operationMatches[1]), fn (int $count): bool => $count > 1);
        expect($duplicates)->toBeEmpty('Duplicate operation IDs found: '.implode(', ', array_keys($duplicates)));
    });

    it('defines common security, version, pagination, and error components', function (): void {
        expect($this->yaml)
            ->toContain('securitySchemes:')
            ->toContain('sanctum:')
            ->not->toContain('bearerFormat')
            ->and($this->yaml)->toContain('#/components/parameters/version')
            ->and($this->yaml)->toContain('pagination_links')
            ->and($this->yaml)->toContain('pagination_meta');

        foreach ([
            'error_response',
            'validation_error_response',
            'not_found_error_response',
            'rate_limit_error_response',
            'unauthenticated_error_response',
        ] as $schema) {
            expect($this->yaml)->toContain($schema);
        }
    });

    it('documents the public user fields returned by /api/user', function (): void {
        $userSchema = $this->spec['paths']['/api/user']['get']['responses'][200]['content']['application/json']['schema']['properties'] ?? [];

        expect($userSchema)->toHaveKeys([
            'id',
            'name',
            'email',
            'email_verified_at',
            'is_admin',
            'language_id',
            'created_at',
            'updated_at',
        ]);
    });

    it('documents 429 on all throttled endpoints with JSON content', function (): void {
        foreach ([
            'searchGameData',
            'reverseImageLinkSearch',
            'reverseImageSearch',
            'findSimilarImages',
        ] as $operationId) {
            $operation = openApiOperationById($this->spec, $operationId);
            expect($operation)->not->toBeEmpty("Operation {$operationId} not found in spec");

            $response = $operation['responses'][429] ?? $operation['responses']['429'] ?? null;
            expect($response)->not->toBeNull("Expected 429 response on {$operationId}");
            expect($response['content']['application/json']['schema']['$ref'] ?? null)
                ->toBe('#/components/schemas/rate_limit_error_response', "Expected rate_limit_error_response ref on {$operationId}");
        }
    });

    it('marks deprecated endpoints as deprecated', function (): void {
        preg_match_all('/operationId:\s+(\w+Deprecated)/', $this->yaml, $matches);
        expect($matches[1])->not->toBeEmpty('No deprecated endpoints found');

        foreach ($matches[1] as $deprecatedOpId) {
            $pattern = "/operationId:\s+{$deprecatedOpId}\n.*?deprecated:\s+true/s";
            expect($this->yaml)->toMatch($pattern, "Deprecated endpoint {$deprecatedOpId} should have deprecated: true");
        }
    });

    it('does not use broad In-Game or RSI-Website tags', function (): void {
        expect($this->yaml)->not->toContain("'In-Game'");
        expect($this->yaml)->not->toContain("'RSI-Website'");
    });

    it('documents 404 with shared schema on every detail endpoint', function (): void {
        foreach ([
            'getVehicle',
            'getGroundVehicle',
            'getGravlevVehicle',
            'getItem',
            'getWeapon',
            'getWeaponAttachment',
            'getClothingItem',
            'getArmor',
            'getFood',
            'getVehicleWeapon',
            'getVehicleItem',
            'getCommodity',
            'getManufacturer',
            'getGameVersion',
            'getDefaultGameVersion',
            'getBlueprint',
            'getFaction',
            'getMission',
            'getLocation',
            'getCommLink',
            'getGalactapediaArticle',
            'getCelestialObject',
            'getStarsystem',
            'getVersionChangelog',
            'listVersionChangelogChanges',
        ] as $operationId) {
            $operation = openApiOperationById($this->spec, $operationId);
            expect($operation)->not->toBeEmpty("Operation {$operationId} not found in spec");

            $response = $operation['responses'][404] ?? $operation['responses']['404'] ?? null;
            expect($response)->not->toBeNull("Missing 404 response on {$operationId}");
            expect($response['content']['application/json']['schema']['$ref'] ?? null)
                ->toBe('#/components/schemas/not_found_error_response', "Expected not_found_error_response ref on {$operationId}, got: ".json_encode($response));
        }
    });

    it('documents auth and 401 on protected endpoints', function (): void {
        foreach ([
            'getAuthenticatedUser',
            'findSimilarImages',
        ] as $operationId) {
            $operation = openApiOperationById($this->spec, $operationId);
            expect($operation)->not->toBeEmpty("Operation {$operationId} not found in spec");
            expect($operation['security'] ?? null)->not->toBeNull("Expected security declaration on {$operationId}");

            $response = $operation['responses'][401] ?? $operation['responses']['401'] ?? null;
            expect($response)->not->toBeNull("Expected 401 response on {$operationId}");
            expect($response['content']['application/json']['schema']['$ref'] ?? null)
                ->toBe('#/components/schemas/unauthenticated_error_response', "Expected unauthenticated_error_response ref on {$operationId}");
        }
    });

    it('uses data envelope on stats latest', function (): void {
        $operation = openApiOperationById($this->spec, 'getLatestStats');
        expect($operation)->not->toBeEmpty('Operation getLatestStats not found in spec');

        $schema = $operation['responses'][200]['content']['application/json']['schema'] ?? null;
        expect($schema)->not->toBeNull();
        expect($schema)->toHaveKey('properties');
        expect($schema['properties'])->toHaveKey('data');
    });

    it('does not document v2/v3 compatibility routes', function (): void {
        foreach ($this->spec['paths'] as $path => $pathItem) {
            expect($path)->not->toStartWith('/api/v2/', "Unexpected v2 route in spec: {$path}");
            expect($path)->not->toStartWith('/api/v3/', "Unexpected v3 route in spec: {$path}");
        }
    });
});
