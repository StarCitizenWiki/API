<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Game;

use App\Attributes\CacheTag;
use App\Http\Controllers\Controller;
use App\Models\Game\StarmapLocation;
use App\Services\ApiJsonRequest;
use App\Support\Missions\MissionTableConfig;
use App\Support\Seo\MissionIndexSeoData;
use App\Support\Seo\MissionShowSeoData;
use App\Traits\NormalizesFilterParams;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;

#[CacheTag('missions')]
class MissionController extends Controller
{
    use NormalizesFilterParams;

    public function __construct(
        private readonly ApiJsonRequest $apiJsonRequest,
        private readonly MissionTableConfig $missionTableConfig,
        private readonly MissionShowSeoData $missionShowSeoData,
        private readonly MissionIndexSeoData $missionIndexSeoData,
    ) {}

    public function index(Request $request): View
    {
        $endpointFilters = $this->normalizeFilterParams($request->input('filter', []));
        $apiRequest = $this->prepareApiRequest($request, $endpointFilters);
        $tableConfig = $this->missionTableConfig->build();

        $initialTableData = $this->apiJsonRequest->request(route('missions.index', [], false), $apiRequest);

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
            'initialHeaderFilter' => [],
            'initialFilters' => $this->buildInitialFilters($endpointFilters, $tableConfig['headerFilterOptionsMap']),
            'pageTitle' => $tableConfig['title'],
            'tableColumns' => $tableConfig['columns'],
            'externalFilters' => $tableConfig['externalFilters'] ?? [],
            'headerFilterOptionsMap' => $tableConfig['headerFilterOptionsMap'],
            'locationFilter' => $locationUuid,
            'activeLocationFilter' => $activeLocationFilter,
            'seo' => $this->missionIndexSeoData->build([
                'activeLocationFilter' => $activeLocationFilter,
                'pageTitle' => $tableConfig['title'],
                'total' => Arr::get($initialTableData, 'meta.total', 0),
                'reward_scope' => $endpointFilters['reward_scope'] ?? null,
                'faction' => $endpointFilters['faction'] ?? null,
            ], $request),
        ]);
    }

    public function show(Request $request, string $mission): View
    {
        $apiRequest = $request->duplicate();
        $apiRequest->query->set('include', 'faction,starmapLocations,blueprints,rewardItems,unlocks,requiredBy,prerequisites');
        $apiRequest->query->set('locale', 'all');

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
}
