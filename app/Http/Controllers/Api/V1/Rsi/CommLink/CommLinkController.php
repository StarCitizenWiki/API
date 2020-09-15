<?php declare(strict_types = 1);

namespace App\Http\Controllers\Api\V1\Rsi\CommLink;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Http\Requests\Rsi\CommLink\ReverseImageLinkSearchRequest;
use App\Http\Requests\Rsi\CommLink\ReverseImageSearchRequest;
use App\ImageHash\Implementations\PerceptualHash2;
use App\Jobs\Rsi\CommLink\Parser\Element\Image as ImageParser;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Image\Image;
use App\Transformers\Api\V1\Rsi\CommLink\CommLinkTransformer;
use App\Transformers\Api\V1\Rsi\CommLink\Image\ImageHashTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Jenssegers\ImageHash\ImageHash;
use Jenssegers\ImageHash\Implementations\AverageHash;
use Jenssegers\ImageHash\Implementations\DifferenceHash;
use League\Fractal\TransformerAbstract;

/**
 * Class CommLinkController
 */
class CommLinkController extends ApiController
{
    /**
     * Comm-Link Transformer
     *
     * @var CommLinkTransformer
     */
    protected TransformerAbstract $transformer;

    /**
     * CommLinkController constructor.
     *
     * @param Request             $request
     * @param CommLinkTransformer $transformer
     */
    public function __construct(Request $request, CommLinkTransformer $transformer)
    {
        $this->transformer = $transformer;
        parent::__construct($request);
    }

    /**
     * Ausgabe aller Comm-Links
     *
     * @return Response
     */
    public function index(): Response
    {
        $commLinks = CommLink::query()->orderByDesc('cig_id');

        return $this->getResponse($commLinks);
    }

    /**
     * Returns a singular comm-link by its cig_id
     *
     * @param int $commLink
     *
     * @return Response
     */
    public function show(int $commLink): Response
    {
        try {
            $commLink = CommLink::query()->where('cig_id', $commLink)->firstOrFail();
            $commLink->append(['prev', 'next']);
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $commLink));
        }

        // Don't include translation per default
        $this->transformer->setDefaultIncludes(array_slice($this->transformer->getAvailableIncludes(), 0, 2));

        $this->extraMeta = [
            'prev_id' => optional($commLink->prev)->cig_id ?? -1,
            'next_id' => optional($commLink->next)->cig_id ?? -1,
        ];

        return $this->getResponse($commLink);
    }

    /**
     * Performs a reverse comm-link search with a provided image url
     * URLs e
     *
     * @param Request $request
     *
     * @return array|Response
     */
    public function reverseImageLinkSearch(Request $request)
    {
        $request->validate((new ReverseImageLinkSearchRequest())->rules());

        $url = $request->get('url', '');
        $url = ImageParser::cleanImgSource($url);

        $url = parse_url($url, PHP_URL_PATH);
        $dir = ImageParser::getDirHash($url);

        if ($dir === false) {
            return [];
        }

        try {
            /** @var Image $image */
            $image = Image::query()->where('dir', $dir)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return [];
        }

        $this->transformer->setDefaultIncludes($this->transformer->getAvailableIncludes());

        return $this->getResponse(optional($image)->commLinks());
    }

    /**
     * Performs a reverse search by comparing image hashes
     *
     * @param Request $request
     *
     * @return Response
     */
    public function reverseImageSearch(Request $request): Response
    {
        $request->validate((new ReverseImageSearchRequest())->rules());

        $this->transformer = new ImageHashTransformer();
        $this->transformer->setDefaultIncludes($this->transformer->getAvailableIncludes());

        $distance = $request->get('similarity', 10);
        if (!is_numeric($distance) || $distance < 0 || $distance > 100) {
            $distance = 10;
        }

        $method = $request->get('method');
        switch ($method) {
            case 'average':
                $hasher = new ImageHash(new AverageHash());
                $prefix = 'a';
                break;

            case 'difference':
                $hasher = new ImageHash(new DifferenceHash());
                $prefix = 'd';
                break;

            case 'perceptual':
            default:
                $hasher = new ImageHash(new PerceptualHash2());
                $prefix = 'p';
                break;
        }

        $hex = $hasher->hash($request->file('image'))->toHex();
        $hex = str_split($hex, strlen($hex) / 2);
        $hex = array_map('hexdec', $hex);

        $hashes = DB::table('comm_link_image_hashes')
            ->select('comm_link_image_id')
            ->selectRaw(
                'BIT_COUNT('.$prefix.'_hash_1 ^ ?) + BIT_COUNT('.$prefix.'_hash_2 ^ ?) AS distance',
                [$hex[0], $hex[1]]
            )
            ->havingRaw('distance <= ?', [$distance])
            ->limit(50)
            ->get();

        if ($hashes->isEmpty()) {
            return redirect()->route('web.user.rsi.comm-links.reverse-search-image')->withMessages(
                [
                    'warning' => [
                        'Keine Comm-Links gefunden.',
                    ],
                ]
            );
        }

        $images = $hashes->map(
            function (object $data) {
                $id = $data->comm_link_image_id;
                $image = Image::query()->find($id);
                $image->similarity = round((1 - $data->distance / 64) * 100);

                return $image;
            }
        )->sortByDesc('similarity');

        return $this->getResponse($images);
    }
}
