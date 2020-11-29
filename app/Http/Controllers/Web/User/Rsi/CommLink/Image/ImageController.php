<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User\Rsi\CommLink\Image;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rsi\CommLink\Image\ImageUploadRequest;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Image\Image;
use App\Traits\Jobs\LoginWikiBotAccountTrait as LoginWikiBotAccount;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use StarCitizenWiki\MediaWikiApi\Facades\MediaWikiApi;

/**
 * Class ImageController
 */
class ImageController extends Controller
{
    use LoginWikiBotAccount;

    /**
     * ImageController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
    }

    /**
     * All downloaded Images, excluding those that could not be found
     *
     * @return Factory|View
     *
     * @throws AuthorizationException
     */
    public function index()
    {
        $this->authorize('web.user.rsi.comm-links.view');

        return view(
            'user.rsi.comm_links.images.index',
            [
                'images' => Image::query()
                    ->where('dir', 'NOT LIKE', 'NOT_FOUND')
                    ->orderByDesc('id')
                    ->groupBy('src')
                    ->paginate(20),
            ]
        );
    }

    /**
     * @param ImageUploadRequest $request
     *
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function upload(ImageUploadRequest $request): string
    {
        $this->authorize('web.user.rsi.comm-links.view');

        $params = $request->validated();
        /** @var Image $image */
        $image = Image::query()->findOrFail($params['image']);

        $this->loginWikiBotAccount('services.wiki_upload_image');

        $token = MediaWikiApi::query()->meta('tokens')->request();

        if ($token->hasErrors()) {
            return json_encode($token->getBody());
        }

        $token = $token->getQuery()['tokens']['csrftoken'];

        $firstCommLinkId = $image->commLinks->pluck('cig_id')->min();

        $response = MediaWikiApi::action('upload', 'POST')
            ->withAuthentication()
            ->addParam(
                'filename',
                sprintf(
                    'Comm-Link %d %s%s',
                    $firstCommLinkId,
                    trim($params['filename']),
                    $image->getExtension()
                )
            )
            ->addParam('comment', sprintf('Upload image from %s', $image->getLocalOrRemoteUrl()))
            ->addParam(
                'text',
                sprintf(
                    "%s\n\n%s",
                    $this->makeContent($params, $image),
                    $this->parseCategories($params, $firstCommLinkId)
                )
            )
            ->addParam('url', $image->getLocalOrRemoteUrl())
            ->addParam('filesize', $image->metadata->size)
            ->csrfToken($token)
            ->request();

        return json_encode($response->getBody());
    }

    private function makeContent(array $data, Image $image): string
    {
        /** @var Collection $sources */
        $sources = $image->commLinks->map(
            function (CommLink $commLink) {
                return $commLink->url;
            }
        );

        $sources->push($image->getLocalOrRemoteUrl());

        // Todo this should be dynamic
        return sprintf(
            <<<TEXT
=={{int:filedesc}}==
{{Information
|description={{de|1=%s}}
|date=%s
|source=%s
|author=RSI
|permission=
|other versions=
}}

=={{int:license-header}}==
{{license-rsi}}
TEXT
            ,
            $data['description'],
            $image->metadata->created_at->format('Y-m-d H:i:s'),
            $sources->implode(',')
        );
    }

    /**
     * Parse categories from string
     *
     * @param array $data
     * @param int   $firstCommLinkId
     *
     * @return string
     */
    private function parseCategories(array $data, int $firstCommLinkId): string
    {
        return collect([sprintf('Comm-Link %d', $firstCommLinkId)])
            ->push(...explode(',', $data['categories']))
            ->map(
                function (string $item) {
                    return trim($item);
                }
            )
            ->filter(
                function (string $item) {
                    return strlen($item) > 5;
                }
            )
            ->unique()
            ->map(
                function (string $item) {
                    return str_replace(['Kategorie', 'Categorie', ':'], '', $item);
                }
            )
            ->map(
                function (string $item) {
                    return sprintf('[[Kategorie:%s]]', $item);
                }
            )
            ->implode("\n");
    }
}
