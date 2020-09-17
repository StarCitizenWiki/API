<?php declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Rsi\CommLink;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Http\Requests\Rsi\CommLink\CommLinkSearchRequest;
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
class CommLinkSearchController extends ApiController
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
     * @param Request $request
     * @param CommLinkTransformer $transformer
     */
    public function __construct(Request $request, CommLinkTransformer $transformer)
    {
        $this->transformer = $transformer;
        parent::__construct($request);
    }

    /**
     * Search for Comm-Links by title
     *
     * @param Request $request
     *
     * @return Response
     */
    public function searchByTitle(Request $request): Response
    {
        $request->validate((new CommLinkSearchRequest())->rules());

        $keyword = $request->get('keyword');

        $commLinks = CommLink::query()
            ->where('title', 'LIKE', sprintf('%%%s%%', $keyword))
            ->limit(100);

        // Disable pagination
        $this->limit = 0;

        return $this->getResponse($commLinks);
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
            return $this->getResponse(collect([]));
        }

        try {
            /** @var Image $image */
            $image = Image::query()->where('dir', $dir)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return $this->getResponse(collect([]));
        }

        $this->transformer->setDefaultIncludes($this->transformer->getAvailableIncludes());

        // Disable pagination
        $this->limit = 0;

        return $this->getResponse(optional($image)->commLinks);
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
        if (!extension_loaded('gd') && !extension_loaded('imagick')) {
            app('Log')::error('Required extension "GD" or "Imagick" not available.');

            $this->response->error('Required extension "GD" or "Imagick" not available.', 501);
        }

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
                $table = 'average_hash';
                break;

            case 'difference':
                $hasher = new ImageHash(new DifferenceHash());
                $prefix = 'd';
                $table = 'difference_hash';
                break;

            case 'perceptual':
            default:
                $hasher = new ImageHash(new PerceptualHash2());
                $prefix = 'p';
                $table = 'perceptual_hash';
                break;
        }

        $hex = $hasher->hash($request->file('image'))->toHex();
        $hash = $hex;
        $hex = str_split($hex, strlen($hex) / 2);
        $hex = array_map('hexdec', $hex);

        // Since SQLITE does not support the BIT_COUNT operation we only search for exact hash matches
        if (config('database.default') === 'sqlite') {
            $hashes = \App\Models\Rsi\CommLink\Image\ImageHash::query()->where($table, $hash)->get(
                'comm_link_image_id'
            );
        } else {
            $hashes = DB::table('comm_link_image_hashes')
                ->select('comm_link_image_id')
                ->selectRaw(
                    'BIT_COUNT('.$prefix.'_hash_1 ^ ?) + BIT_COUNT('.$prefix.'_hash_2 ^ ?) AS distance',
                    [$hex[0], $hex[1]]
                )
                ->havingRaw('distance <= ?', [$distance])
                ->limit(50)
                ->get();
        }

        if ($hashes->isEmpty()) {
            return $this->getResponse(collect([]));
        }

        $images = $hashes->map(
            function (object $data) {
                $id = $data->comm_link_image_id;
                $image = Image::query()->find($id);
                $image->similarity = round((1 - $data->distance / 64) * 100);

                return $image;
            }
        )->sortByDesc('similarity');

        // Disable pagination
        $this->limit = 0;

        return $this->getResponse($images);
    }
}
