<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Rsi;

use App\Attributes\CacheTag;
use App\Http\Controllers\Controller;
use App\Http\Resources\Rsi\CommLink\Image\ImageHashResource;
use App\Http\Resources\Rsi\CommLink\Image\ImageResource;
use App\Models\Rsi\CommLink\Image\Image;
use App\Models\Rsi\CommLink\Image\ImageHash as ImageHashModel;
use App\Services\ApiJsonRequest;
use App\Services\ImageHash\PdqHasher;
use App\Services\Parser\CommLink\Image as ImageParser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[CacheTag('comm-links')]
class CommLinkController extends Controller
{
    public function __construct(private readonly ApiJsonRequest $apiJsonRequest) {}

    public function index(Request $request): View
    {
        $searchType = $request->query('search');
        $searchQuery = trim((string) $request->query('query', ''));
        $searchUrl = trim((string) $request->query('url', ''));
        $initialFilters = [];
        $searchCommLinks = [];

        $apiRequest = $request->duplicate();

        if ($searchType === 'title' && $searchQuery !== '') {
            $filters = [];

            if (ctype_digit($searchQuery)) {
                $filters['id'] = $searchQuery;
                $initialFilters[] = ['field' => 'id', 'value' => $searchQuery];
            } else {
                $filters['title'] = $searchQuery;
                $initialFilters[] = ['field' => 'title', 'value' => $searchQuery];
            }

            $apiRequest->query->set('filter', array_merge(
                (array) $apiRequest->query->get('filter', ''),
                $filters
            ));
        }

        if ($searchType === 'content' && $searchQuery !== '') {
            $apiRequest->query->set('filter', array_merge(
                (array) $apiRequest->query->get('filter', ''),
                ['content' => $searchQuery]
            ));
        }

        if ($searchType === 'media-url' && $searchUrl !== '') {
            $searchCommLinks = $this->commLinksForMediaUrl($searchUrl);
        }

        $initialTableData = $this->apiJsonRequest->request(route('comm-links.index', [], false), $apiRequest);
        $filterPayload = $this->apiJsonRequest->request(route('comm-links.filters', [], false), $apiRequest);

        $allowedFilterValues = Arr::get($filterPayload, 'filters', []);

        return view('comm-links.index', [
            'initialTableData' => $initialTableData,
            'initialHeaderFilter' => $allowedFilterValues,
            'initialFilters' => $initialFilters,
            'searchType' => $searchType,
            'searchQuery' => $searchQuery,
            'searchUrl' => $searchUrl,
            'searchCommLinks' => $searchCommLinks,
        ]);
    }

    public function show(Request $request, int $id): View
    {
        $include = array_filter(array_map('trim', explode(',', (string) $request->query('include', ''))));
        $include = array_values(array_unique(array_merge($include, ['images', 'links'])));

        $apiRequest = $request->duplicate();
        $apiRequest->query->set('include', implode(',', $include));

        $payload = $this->apiJsonRequest->request(route('comm-links.show', ['id' => $id], false), $apiRequest);
        $commLinkData = Arr::get($payload, 'data', []);

        if ($commLinkData === []) {
            abort(Response::HTTP_NOT_FOUND);
        }

        return view('comm-links.show', [
            'commLink' => $commLinkData,
            'commLinkMeta' => Arr::get($payload, 'meta', []),
            'pageTitle' => Arr::get($commLinkData, 'title', 'Comm-Link'),
        ]);
    }

    public function imagesIndex(Request $request): View
    {
        $payload = $this->apiJsonRequest->request(route('comm-link-images.index', [], false), $request);
        $imageData = Arr::get($payload, 'data', []);
        $pagination = Arr::get($payload, 'meta', []);
        $paginationLinks = Arr::get($payload, 'links', []);

        return view('comm-links.images.index', [
            'images' => $imageData,
            'pagination' => $pagination,
            'paginationLinks' => $paginationLinks,
            'pageTitle' => 'Comm-Link Images',
            'searchType' => null,
            'searchQuery' => null,
        ]);
    }

    public function searchImagesByName(Request $request): View
    {
        $searchQuery = trim((string) $request->input('query', ''));

        $results = $searchQuery === ''
            ? collect()
            : Image::query()
                ->with(['metadata', 'commLinks.channel', 'commLinks.category', 'commLinks.series', 'tags'])
                ->whereNull('base_image_id')
                ->whereRaw('LOWER(src) LIKE ?', [sprintf('%%%s%%', strtolower($searchQuery))])
                ->whereRelation('metadata', 'size', '>', 0)
                ->limit(100)
                ->orderByDesc('created_at')
                ->get();

        $images = ImageResource::collection($results)->resolve();

        return view('comm-links.images.index', [
            'images' => $images,
            'pagination' => $this->paginationSummary(count($images)),
            'paginationLinks' => [],
            'pageTitle' => 'Comm-Link Images',
            'searchType' => 'media-name',
            'searchQuery' => $searchQuery,
        ]);
    }

    public function reverseImageSearch(Request $request, PdqHasher $hasher): View
    {
        $validated = $request->validate([
            'image' => ['required', 'image', 'max:5120'],
            'similarity' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $file = $request->file('image');
        $contents = $file ? file_get_contents($file->getPathname()) : false;

        if ($contents === false) {
            throw new HttpException(422, 'Unable to read uploaded image.');
        }

        try {
            $hashResult = $hasher->hashContents($contents);
        } catch (RuntimeException $exception) {
            throw new HttpException(422, $exception->getMessage(), $exception);
        }

        $similarity = (int) ($validated['similarity'] ?? 75);
        $matches = ImageHashModel::similarImagesForHash($hashResult->toBitString(), $similarity);

        $matches->loadMissing(['metadata', 'commLinks.channel', 'commLinks.category', 'commLinks.series', 'tags']);

        $images = ImageHashResource::collection($matches)->resolve();

        return view('comm-links.images.index', [
            'images' => $images,
            'pagination' => $this->paginationSummary(count($images)),
            'paginationLinks' => [],
            'pageTitle' => 'Comm-Link Images',
            'searchType' => 'reverse-image',
            'searchQuery' => '',
        ]);
    }

    public function showImage(Request $request, int $image): View
    {
        $payload = $this->apiJsonRequest->request(route('comm-link-images.show', ['image' => $image], false), $request);
        $imageData = Arr::get($payload, 'data', []);

        if ($imageData === []) {
            abort(Response::HTTP_NOT_FOUND);
        }

        return view('comm-links.images.show', [
            'image' => $imageData,
            'pageTitle' => sprintf('Comm-Link Image %s', Arr::get($imageData, 'id', '')),
            'similarUrl' => route('web.comm-links.images.similar', $image),
        ]);
    }

    public function similarImages(Request $request, int $image): View
    {
        $validated = $request->validate([
            'similarity' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        /** @var Image $imageModel */
        $imageModel = Image::query()
            ->with(['hash'])
            ->findOrFail($image);

        $similarity = (int) ($validated['similarity'] ?? 50);
        $similarImages = $imageModel->similarImages($similarity, 50);

        $images = ImageHashResource::collection($similarImages)->resolve();

        return view('comm-links.images.index', [
            'images' => $images,
            'pagination' => $this->paginationSummary(count($images)),
            'paginationLinks' => [],
            'pageTitle' => 'Comm-Link Images',
            'searchType' => 'similar-images',
            'searchQuery' => sprintf('Similar to image ID %s', $image),
        ]);
    }

    public function search(): View
    {
        return view('comm-links.search');
    }

    /**
     * @return array<int, array{id: int, title: string, url: string}>
     */
    private function commLinksForMediaUrl(string $url): array
    {
        $image = $this->imageForMediaUrl($url);

        if ($image === null) {
            return [];
        }

        return $image->commLinks()
            ->orderByDesc('cig_id')
            ->get()
            ->map(fn ($commLink) => [
                'id' => $commLink->cig_id,
                'title' => $commLink->title,
                'url' => route('web.comm-links.show', $commLink->cig_id),
            ])
            ->values()
            ->all();
    }

    private function imageForMediaUrl(string $url): ?Image
    {
        $path = parse_url(ImageParser::cleanImgSource($url), PHP_URL_PATH);

        if (! is_string($path) || $path === '') {
            return null;
        }

        $dir = ImageParser::getDirHash($path);
        $query = Image::query();

        if ($dir === 'i') {
            $parts = explode('/', $path);
            array_pop($parts);
            $prefix = implode('/', $parts);

            return $query->where('src', 'LIKE', $prefix.'%')->first();
        }

        return $query->where('dir', $dir)->first();
    }

    /**
     * @return array{current_page: int, last_page: int, total: int}
     */
    private function paginationSummary(int $total): array
    {
        return [
            'current_page' => 1,
            'last_page' => 1,
            'total' => $total,
        ];
    }
}
