<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Rsi\CommLink;

use App\Services\Parser\CommLink\Image as ImageParser;
use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Http\Requests\Rsi\CommLink\CommLinkSearchRequest;
use App\Http\Requests\Rsi\CommLink\ReverseImageLinkSearchRequest;
use App\Http\Requests\Rsi\CommLink\ReverseImageSearchRequest;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Image\Image;
use App\Models\Rsi\CommLink\Image\ImageHash as ImageHashModel;
use App\Services\ImageHash\Implementations\PerceptualHash2;
use App\Transformers\Api\V1\Rsi\CommLink\CommLinkTransformer;
use App\Transformers\Api\V1\Rsi\CommLink\Image\ImageHashTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Jenssegers\ImageHash\ImageHash;
use Jenssegers\ImageHash\Implementations\AverageHash;
use Jenssegers\ImageHash\Implementations\DifferenceHash;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Comm-Link Search API
 * Scraped Comm-Links from Roberts Space Industries
 *
 * @Resource("Comm-Links", uri="/comm-links")
 */
class CommLinkSearchController extends ApiController
{
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
     * Search for Comm-Links by title
     *
     * Returns matching Comm-Links
     *
     * @Post("/search")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("keyword", type="string", required=true, description="(Partial) Comm-Link title"),
     *     @Parameter(
     *          "include",
     *          type="string",
     *          required=false,
     *          description="Relations to include. Valid relations are shown in the meta data"
     *     ),
     * })
     *
     * @Transaction({
     * @Request({"keyword": "Welcome"}, headers={"Accept": "application/x.StarCitizenWikiApi.v1+json"}),
     * @Response(200, body={"data":{{"id":12663,"title":"Welcome to the Comm-Link!","rsi_url":"https:\/\/robertsspaceindustries.com\/comm-link\/transmission\/12663-Welcome-To-The-Comm-Link","api_url":"https:\/\/api.star-citizen.wiki\/api\/comm-links\/12663","api_public_url":"https:\/\/api.star-citizen.wiki\/comm-links\/12663","channel":"Transmission","category":"General","series":"None","images":2,"links":1,"comment_count":130,"created_at":"2012-09-04T22:00:00.000000Z"},{"id":13098,"title":"WelcometoRSIPrime","rsi_url":"https:\/\/robertsspaceindustries.com\/comm-link\/transmission\/13098-Welcome-To-RSI-Prime","api_url":"https:\/\/api.star-citizen.wiki\/api\/comm-links\/13098","api_public_url":"https:\/\/api.star-citizen.wiki\/comm-links\/13098","channel":"Transmission","category":"General","series":"None","images":0,"links":0,"comment_count":32,"created_at":"2013-06-27T22:00:00.000000Z"},{"id":13132,"title":"WelcomeNewCitizens!","rsi_url":"https:\/\/robertsspaceindustries.com\/comm-link\/transmission\/13132-Welcome-New-Citizens","api_url":"https:\/\/api.star-citizen.wiki\/api\/comm-links\/13132","api_public_url":"https:\/\/api.star-citizen.wiki\/comm-links\/13132","channel":"Transmission","category":"General","series":"None","images":1,"links":8,"comment_count":86,"created_at":"2013-07-07T22:00:00.000000Z"},{"id":14157,"title":"LOREBUILDER:FOURTEEN:Welcometov2","rsi_url":"https:\/\/robertsspaceindustries.com\/comm-link\/spectrum-dispatch\/14157-LORE-BUILDER-FOURTEEN-Welcome-To-V2","api_url":"https:\/\/api.star-citizen.wiki\/api\/comm-links\/14157","api_public_url":"https:\/\/api.star-citizen.wiki\/comm-links\/14157","channel":"SpectrumDispatch","category":"Lore","series":"LoreBuilder","images":3,"links":3,"comment_count":526,"created_at":"2014-09-18T22:00:00.000000Z"},{"id":14927,"title":"WelcometoArcCorp-StarCitizen1.2Released","rsi_url":"https:\/\/robertsspaceindustries.com\/comm-link\/transmission\/14927-Welcome-To-ArcCorp-Star-Citizen-12-Released","api_url":"https:\/\/api.star-citizen.wiki\/api\/comm-links\/14927","api_public_url":"https:\/\/api.star-citizen.wiki\/comm-links\/14927","channel":"Transmission","category":"General","series":"None","images":17,"links":2,"comment_count":373,"created_at":"2015-08-28T22:00:00.000000Z"},{"id":15256,"title":"Ship Shape :Welcome Aboard the Starfarer","rsi_url":"https:\/\/robertsspaceindustries.com\/comm-link\/transmission\/15256-Ship-Shape-Welcome-Aboard-The-Starfarer","api_url":"https:\/\/api.star-citizen.wiki\/api\/comm-links\/15256","api_public_url":"https:\/\/api.star-citizen.wiki\/comm-links\/15256","channel":"Transmission","category":"General","series":"None","images":0,"links":1,"comment_count":219,"created_at":"2016-03-17T23:00:00.000000Z"},{"id":17342,"title":"WelcomeHub&GuideSystem","rsi_url":"https:\/\/robertsspaceindustries.com\/comm-link\/transmission\/17342-Welcome-Hub-Guide-System","api_url":"https:\/\/api.star-citizen.wiki\/api\/comm-links\/17342","api_public_url":"https:\/\/api.star-citizen.wiki\/comm-links\/17342","channel":"Transmission","category":"General","series":"None","images":6,"links":3,"comment_count":51,"created_at":"2019-11-21T23:00:00.000000Z"}},"meta":{"processed_at":"2020-12-0819:51:58","valid_relations":{"images","links","english","german"}}}),
     *
     * @Request({"keyword": "Keyword"}, headers={"Accept": "application/x.StarCitizenWikiApi.v1+json"}),
     * @Response(200, body={{"data":{},"meta":{"processed_at":"2020-12-0819:54:01","valid_relations":{"images","links","english","german"}}}}),
     *
     * @Request({"keyword": ""}, headers={"Accept": "application/x.StarCitizenWikiApi.v1+json"}),
     * @Response(422, body={"message":"The given datawasinvalid.","errors":{"keyword":{"keyword muss ausgefüllt sein."}},"status_code":422}),
     * })
     *
     * @param Request $request
     *
     * @return Response
     */
    public function searchByTitle(Request $request): Response
    {
        $request->validate((new CommLinkSearchRequest())->rules());

        return $this->disablePagination()
            ->getResponse(
                CommLink::query()
                    ->where('title', 'LIKE', sprintf('%%%s%%', $request->get('keyword')))
                    ->limit(100)
            );
    }

    /**
     * Performs a reverse comm-link search with a provided image url
     *
     * Returns matching Comm-Links
     *
     * @Post("/reverse-image-link-search")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("url", type="string", required=true, description="Url to an image hosted on (media.)robertsspaceindustries.com"),
     *     @Parameter(
     *          "include",
     *          type="string",
     *          required=false,
     *          description="Relations to include. Valid relations are shown in the meta data"
     *     ),
     * })
     *
     * @Transaction({
     * @Request({"url": "https://robertsspaceindustries.com/media/bluo97w6u7n1ur/post_section_header/Starshipbridge.jpg"}, headers={"Accept": "application/x.StarCitizenWikiApi.v1+json"}),
     * @Response(200, body={"data": {{"id": 12663,"title": "Welcome to the Comm-Link!","rsi_url": "https:\/\/robertsspaceindustries.com\/comm-link\/transmission\/12663-Welcome-To-The-Comm-Link","api_url": "http:\/\/api\/api\/comm-links\/12663","api_public_url": "http:\/\/localhost:8000\/comm-links\/12663","channel": "Transmission","category": "General","series": "None","images": 1,"links": 1,"comment_count": 132,"created_at": "2012-09-04T22:00:00.000000Z"},{"id": 12667,"title": "A Message from Chris Roberts","rsi_url": "https:\/\/robertsspaceindustries.com\/comm-link\/transmission\/12667-A-Message-From-Chris-Roberts","api_url": "http:\/\/api\/api\/comm-links\/12667","api_public_url": "http:\/\/localhost:8000\/comm-links\/12667","channel": "Transmission","category": "General","series": "None","images": 2,"links": 0,"comment_count": 146,"created_at": "2012-09-10T22:00:00.000000Z"},{"..."},},"meta": {"processed_at": "2020-12-08 20:06:30","valid_relations": {"images","links","english","german"}}}),
     *
     * @Request({"url": "https://i.imgur.com/example.png"}, headers={"Accept": "application/x.StarCitizenWikiApi.v1+json"}),
     * @Response(422, body={{"message": "The given data was invalid.","errors": {"url": {"url Format ist ungültig."}},"status_code": 422}}),
     *
     * @Request({"url": ""}, headers={"Accept": "application/x.StarCitizenWikiApi.v1+json"}),
     * @Response(422, body={"message": "The given data was invalid.","errors": {"url": {"url muss ausgefüllt sein."}},"status_code": 422}),
     * })
     *
     * @param Request $request
     *
     * @return array|Response
     */
    public function reverseImageLinkSearch(Request $request)
    {
        $request->validate((new ReverseImageLinkSearchRequest())->rules());

        /** @var Image $image */
        $image = Image::query()
            ->where(
                'dir',
                $this->getDirHashFromImageUrl($request->get('url', ''))
            )
            ->firstOr(
                ['*'],
                function () {
                    return [];
                }
            );

        return $this->disablePagination()
            ->getResponse(optional($image)->commLinks);
    }

    /**
     * Performs a reverse search by comparing image hashes
     * This is still very experimental
     *
     * Returns matching Comm-Links
     *
     * @Post("/reverse-image-search")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("image", type="file", required=true, description="JPEG / PNG File"),
     *     @Parameter("similarity", type="number", required=true, description="Similairty value between 1 and 100. Where 100 denotes a perfect match."),
     *     @Parameter("method", type="string", required=true, description="Available methods: perceptual, difference, average"),
     *     @Parameter(
     *          "include",
     *          type="string",
     *          required=false,
     *          description="Relations to include. Valid relations are shown in the meta data"
     *     ),
     * })
     *
     * @Transaction({
     * @Request({"image": "file", "similarity": 90, "method": "perceptual"}, headers={"Accept": "application/x.StarCitizenWikiApi.v1+json", "Content-Type", "multipart/form-data"}),
     * @Response(200, body={"data": {{"rsi_url": "https:\/\/robertsspaceindustries.com\/media\/7e1mr7g2ycanhr\/source\/MarsTerraform_Final2b.jpg","api_url": null,"alt": "","size": 720981,"mime_type": "image\/jpeg","last_modified": "2013-07-19T03:30:36.000000Z","similarity": 73,"hashes": {"perceptual_hash": "d881a1df9f2a7d9b","difference_hash": "2b2e1f6fc9c3533b","average_hash": "7ffffce080040430"},"commLinks": {"data": {{"api_url": "http:\/\/api\/api\/comm-links\/12670"}}}},{"rsi_url": "https:\/\/robertsspaceindustries.com\/media\/ve1gus81zoixrr\/source\/Marssurface3_FI.jpg","api_url": null,"alt": "","size": 179006,"mime_type": "image\/jpeg","last_modified": "2013-10-01T17:44:56.000000Z","similarity": 64,"hashes": {"perceptual_hash": "c00fe7ddce33fdff","difference_hash": "1f1f173b797f7f67","average_hash": "3c7efff7e04000"},"commLinks": {"data": {{"api_url": "http:\/\/api\/api\/comm-links\/12675"}}}},{"rsi_url": "https:\/\/robertsspaceindustries.com\/media\/bluo97w6u7n1ur\/source\/Starshipbridge.jpg","api_url": null,"alt": "","size": 1504015,"mime_type": "image\/jpeg","last_modified": "2013-07-19T03:30:55.000000Z","similarity": 63,"hashes": {"perceptual_hash": "c1fbf0f2960db45d","difference_hash": "63898e4ece2f9b47","average_hash": "9dfffcf8b004"},"commLinks": {"data": {{"api_url": "http:\/\/api\/api\/comm-links\/12663"},{"..."},}}},},"meta": {"processed_at": "2020-12-08 20:18:34","valid_relations": {"hashes","comm_links"}}}),
     * })
     *
     * @param Request $request
     *
     * @return Response
     */
    public function reverseImageSearch(Request $request): Response
    {
        $this->checkExtensionsLoaded();

        $request->validate((new ReverseImageSearchRequest())->rules());

        $this->transformer = new ImageHashTransformer();
        $this->transformer->includeAllAvailableIncludes();

        $hashConfig = $this->getHashConfigForMethod($request->get('method'));
        $hashConfig['similarity'] = (int)$request->get('similarity');
        $hashData = $this->hashImage($hashConfig['hasher'], $request->file('image'));

        return $this->disablePagination()
            ->getResponse(
                $this->getHashesFromDatabase($hashConfig, $hashData)
                    ->map(
                        function (object $data) {
                            $id = $data->comm_link_image_id;
                            $image = Image::query()->find($id);
                            $image->similarity = round((1 - $data->distance / 64) * 100);

                            return $image;
                        }
                    )
                    ->sortByDesc('similarity')
            );
    }

    /**
     * Returns the RSI directory hash of an image url
     *
     * @param string $url The RSI Media URl
     *
     * @return string The directory hash of the image
     */
    private function getDirHashFromImageUrl(string $url): string
    {
        return ImageParser::getDirHash(
            parse_url(
                ImageParser::cleanImgSource($url),
                PHP_URL_PATH
            )
        );
    }

    /**
     * Checks if either GD or Imagick is loaded
     *
     * @throws HttpException
     */
    private function checkExtensionsLoaded(): void
    {
        if (!extension_loaded('gd') && !extension_loaded('imagick')) {
            app('Log')::error('Required extension "GD" or "Imagick" not available.');

            $this->response->error('Required extension "GD" or "Imagick" not available.', 501);
        }
    }

    /**
     * Hash config based on hash method
     *
     * @param string $hashMethod
     *
     * @return array
     */
    private function getHashConfigForMethod(string $hashMethod): array
    {
        switch ($hashMethod) {
            case 'average':
                return [
                    'hasher' => new ImageHash(new AverageHash()),
                    'prefix' => 'a',
                    'table' => 'average_hash',
                ];

            case 'difference':
                return [
                    'hasher' => new ImageHash(new DifferenceHash()),
                    'prefix' => 'd',
                    'table' => 'difference_hash',
                ];

            case 'perceptual':
            default:
                return [
                    'hasher' => new ImageHash(new PerceptualHash2()),
                    'prefix' => 'p',
                    'table' => 'perceptual_hash',
                ];
        }
    }

    /**
     * Hashes an uploaded image
     *
     * @param ImageHash    $hasher The hasher with set hash method
     * @param UploadedFile $file   The uploaded file
     *
     * @return array
     */
    private function hashImage(ImageHash $hasher, $file): array
    {
        $hash = $hasher->hash($file)->toHex();

        return [
            'hash' => $hash,
            'decoded' => array_map('hexdec', (str_split($hash, strlen($hash) / 2))),
        ];
    }

    /**
     * Return hashes based on database connection type
     *
     * @param array $hashConfig
     * @param array $hashData
     *
     * @return Builder[]|Collection|\Illuminate\Support\Collection
     */
    private function getHashesFromDatabase(array $hashConfig, array $hashData)
    {
        // Since SQLITE does not support the BIT_COUNT operation we only search for exact hash matches
        if (config('database.default') === 'sqlite') {
            return $this->getHashesFromSQLiteStore($hashConfig['table'], $hashData['hash']);
        }

        return $this->getHashesFromSQLStore(
            $hashConfig['prefix'],
            $hashData['decoded'],
            $hashConfig['similarity']
        );
    }

    /**
     * Get the image hashes that equal the provided hash
     *
     * @param string $hashMethod Hash method average, distance, perceptual
     * @param string $hash       The image hash
     *
     * @return Builder[]|Collection
     */
    private function getHashesFromSQLiteStore(string $hashMethod, string $hash)
    {
        return ImageHashModel::query()
            ->where($hashMethod, $hash)
            ->get('comm_link_image_id');
    }

    /**
     * Get the image hashes matching the provided hash method and hamming distance
     *
     * @param string $prefix      Hash Attribute prefix
     * @param array  $decodedHash Image hash split in the middle and hex decoded
     * @param int    $distance    The maximum hamming distance
     *
     * @return \Illuminate\Support\Collection
     */
    private function getHashesFromSQLStore(
        string $prefix,
        array $decodedHash,
        int $distance
    ): \Illuminate\Support\Collection {
        return DB::table('comm_link_image_hashes')
            ->select('comm_link_image_id')
            ->selectRaw(
                'BIT_COUNT(' . $prefix . '_hash_1 ^ ?) + BIT_COUNT(' . $prefix . '_hash_2 ^ ?) AS distance',
                [
                    $decodedHash[0],
                    $decodedHash[1],
                ]
            )
            ->havingRaw('distance <= ?', [$distance])
            ->limit(50)
            ->get();
    }
}
