<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Game;

use App\Attributes\CacheTag;
use App\Http\Controllers\Controller;
use App\Models\Game\StarmapLocation;
use App\Services\ApiJsonRequest;
use App\Support\Seo\StarmapLocationIndexSeoData;
use App\Support\Seo\StarmapLocationShowSeoData;
use App\Support\Starmap\StarmapLocationShowViewData;
use App\Support\Starmap\StarmapLocationTableConfig;
use App\Traits\NormalizesFilterParams;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\View\View;

#[CacheTag('starmap')]
class StarmapLocationController extends Controller
{
    use NormalizesFilterParams;

    public function __construct(
        private readonly ApiJsonRequest $apiJsonRequest,
        private readonly StarmapLocationTableConfig $starmapLocationTableConfig,
        private readonly StarmapLocationShowViewData $starmapLocationShowViewData,
        private readonly StarmapLocationShowSeoData $starmapLocationShowSeoData,
        private readonly StarmapLocationIndexSeoData $starmapLocationIndexSeoData,
    ) {}

    public function index(Request $request): View
    {
        $endpointFilters = $this->normalizeFilterParams($request->input('filter', []));
        $tableConfig = $this->starmapLocationTableConfig->build();

        return view('starmap.locations.index', [
            'initialHeaderFilter' => [],
            'initialFilters' => $this->buildInitialFilters($endpointFilters, $tableConfig['headerFilterOptionsMap']),
            'pageTitle' => $tableConfig['title'],
            'tableColumns' => $tableConfig['columns'],
            'externalFilters' => $tableConfig['externalFilters'] ?? [],
            'headerFilterOptionsMap' => $tableConfig['headerFilterOptionsMap'],
            'seo' => $this->starmapLocationIndexSeoData->build(['pageTitle' => $tableConfig['title']], $request),
        ]);
    }

    public function show(Request $request, string $identifier): View|RedirectResponse
    {
        $apiRequest = $request->duplicate();
        $apiRequest->query->set('include', 'children,resources,missions');

        $payload = $this->apiJsonRequest->request(
            route('locations.show', ['identifier' => $identifier], false),
            $apiRequest
        );
        $locationData = Arr::get($payload, 'data', []);

        if ($locationData === []) {
            // Drifted slug suffixes from old imports still get hit; redirect to the canonical slug.
            $redirect = $this->resolveNumericSuffixRedirect($request, $identifier);
            if ($redirect !== null) {
                return $redirect;
            }

            abort(Response::HTTP_NOT_FOUND);
        }

        return view('starmap.locations.show', [
            'location' => $locationData,
            'viewData' => $this->starmapLocationShowViewData->build(is_array($locationData) ? $locationData : []),
            'pageTitle' => Arr::get($locationData, 'name', 'Starmap Location'),
            'seo' => $this->starmapLocationShowSeoData->build(is_array($locationData) ? $locationData : [], $request),
        ]);
    }

    /**
     * 301 to the canonical slug when the requested identifier is its base or a
     * numeric-suffix variant. Null when no unique match exists.
     */
    private function resolveNumericSuffixRedirect(Request $request, string $identifier): ?RedirectResponse
    {
        if (Str::isUuid($identifier)) {
            return null;
        }

        // Try the identifier, plus the identifier with one trailing suffix stripped.
        $bases = [$identifier];

        if (preg_match('/^(.+)-[0-9]+$/', $identifier, $matches) === 1 && $matches[1] !== $identifier) {
            $bases[] = $matches[1];
        }

        $bases = array_values(array_unique($bases));

        $patterns = array_map(
            static fn (string $base): string => '^'.preg_quote($base, null).'(-[0-9]+)?$',
            $bases,
        );

        $canonicalSlugs = StarmapLocation::query()
            ->where(function (Builder $query) use ($patterns): void {
                foreach ($patterns as $pattern) {
                    $query->orWhereRaw('slug ~ ?', [$pattern]);
                }
            })
            ->pluck('slug')
            ->unique()
            ->values();

        // Only resolve a single, distinct slug; ambiguous shared names stay 404.
        if ($canonicalSlugs->count() !== 1 || $canonicalSlugs->first() === $identifier) {
            return null;
        }

        $target = route('web.locations.show', ['identifier' => $canonicalSlugs->first()]);
        $queryString = $request->getQueryString();

        if ($queryString !== null && $queryString !== '') {
            $target .= '?'.$queryString;
        }

        return redirect($target, Response::HTTP_MOVED_PERMANENTLY);
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
}
