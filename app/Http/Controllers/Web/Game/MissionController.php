<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Game;

use App\Http\Controllers\Controller;
use App\Models\Game\StarmapLocation;
use App\Services\ApiJsonRequest;
use App\Support\Missions\MissionTableConfig;
use App\Support\Seo\MissionShowSeoData;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class MissionController extends Controller
{
    public function __construct(
        private readonly ApiJsonRequest $apiJsonRequest,
        private readonly MissionTableConfig $missionTableConfig,
        private readonly MissionShowSeoData $missionShowSeoData,
    ) {}

    public function index(Request $request): View
    {
        $endpointFilters = $this->normalizeFilterParams($request->input('filter', []));
        $apiRequest = $this->prepareApiRequest($request, $endpointFilters);
        $tableConfig = $this->missionTableConfig->build();

        $initialTableData = $this->apiJsonRequest->request(route('missions.index', [], false), $apiRequest);
        $filterPayload = $this->apiJsonRequest->request(route('missions.filters', [], false), $apiRequest);

        $locationUuid = $request->input('filter.location');
        $activeLocationFilter = null;

        if (is_string($locationUuid) && $locationUuid !== '') {
            $location = StarmapLocation::where('uuid', $locationUuid)->first();
            $locationName = $location?->dataForVersion()?->value('name');

            if ($locationName !== null) {
                $activeLocationFilter = [
                    'name' => $locationName,
                    'url' => route('web.locations.show', $locationUuid),
                ];
            }
        }

        return view('missions.index', [
            'initialTableData' => $initialTableData,
            'initialHeaderFilter' => Arr::get($filterPayload, 'filters', []),
            'initialFilters' => $this->buildInitialFilters($endpointFilters, $tableConfig['headerFilterOptionsMap']),
            'pageTitle' => $tableConfig['title'],
            'tableColumns' => $tableConfig['columns'],
            'externalFilters' => $tableConfig['externalFilters'] ?? [],
            'headerFilterOptionsMap' => $tableConfig['headerFilterOptionsMap'],
            'locationFilter' => $locationUuid,
            'activeLocationFilter' => $activeLocationFilter,
            'seo' => $this->buildIndexSeo($request, $activeLocationFilter),
        ]);
    }

    public function show(Request $request, string $mission): View
    {
        $apiRequest = $request->duplicate();
        $apiRequest->query->set('include', 'faction,starmapLocations,blueprints,rewardItems,unlocks,requiredBy,prerequisites');

        $payload = $this->apiJsonRequest->request(route('missions.show', ['mission' => $mission], false), $apiRequest);
        $data = Arr::get($payload, 'data', []);

        if ($data === []) {
            abort(404);
        }

        $seo = $this->missionShowSeoData->build($data, $request);

        return view('missions.show', [
            'resource' => $data,
            'pageTitle' => Arr::get($data, 'title', 'Mission'),
            'seo' => $seo,
        ]);
    }

    /**
     * @param  array<string, string>  $filters
     * @param  array<string, string>  $headerFilterOptionsMap
     * @return array<int, array{field: string, value: string}>
     */
    private function buildInitialFilters(array $filters, array $headerFilterOptionsMap): array
    {
        if ($filters === []) {
            return [];
        }

        $initialFilters = [];
        $apiFieldToColumnFieldMap = array_flip($headerFilterOptionsMap);

        foreach ($filters as $field => $value) {
            $initialFilters[] = [
                'field' => $apiFieldToColumnFieldMap[$field] ?? $field,
                'value' => $value,
            ];
        }

        return $initialFilters;
    }

    /**
     * @return array<string, string>
     */
    private function normalizeFilterParams(mixed $filters): array
    {
        if (! is_array($filters) || $filters === []) {
            return [];
        }

        $normalized = [];

        foreach ($filters as $field => $value) {
            if (! is_string($field) || $field === '') {
                continue;
            }

            $normalizedValue = $this->normalizeFilterValue($value);

            if ($normalizedValue === null) {
                continue;
            }

            $normalized[$field] = $normalizedValue;
        }

        return $normalized;
    }

    private function normalizeFilterValue(mixed $value): ?string
    {
        if (is_array($value)) {
            $values = array_map(static fn (mixed $entry): string => trim((string) $entry), $value);
            $values = array_values(array_filter($values, static fn (string $entry): bool => $entry !== ''));

            if ($values === []) {
                return null;
            }

            return implode(',', $values);
        }

        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    /**
     * @param  array<string, string>  $filters
     */
    private function prepareApiRequest(Request $request, array $filters): Request
    {
        $apiRequest = $request->duplicate();

        if ($filters === []) {
            $apiRequest->query->remove('filter');

            return $apiRequest;
        }

        $apiRequest->query->set('filter', $filters);

        return $apiRequest;
    }

    /**
     * @param  array{name: string, url: string}|null  $activeLocationFilter
     * @return array<string, mixed>
     */
    private function buildIndexSeo(Request $request, ?array $activeLocationFilter): array
    {
        $version = null;

        if ($request->hasSession()) {
            $version = $request->session()->get('game_version_code');
        }

        if ($version === null) {
            $version = $request->query('version');
        }

        $versionParams = is_string($version) && $version !== '' ? ['version' => $version] : [];
        $canonicalUrl = route('web.missions.index', $versionParams);

        $metaDescription = $activeLocationFilter !== null
            ? 'Browse Star Citizen missions available at '.$activeLocationFilter['name'].'. Filter by faction, type, legality, and more.'
            : 'Browse Star Citizen missions. Filter by faction, type, location, legality, and more.';

        return [
            'canonicalUrl' => $canonicalUrl,
            'metaDescription' => $metaDescription,
            'ogTitle' => 'Star Citizen Missions',
            'ogDescription' => $metaDescription,
            'twitterTitle' => 'Star Citizen Missions',
            'twitterDescription' => $metaDescription,
        ];
    }
}
