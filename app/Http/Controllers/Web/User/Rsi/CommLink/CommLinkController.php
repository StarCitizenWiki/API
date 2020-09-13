<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User\Rsi\CommLink;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rsi\CommLink\CommLinkRequest;
use App\Jobs\Rsi\CommLink\Parser\Element\Content;
use App\Models\Rsi\CommLink\Category\Category;
use App\Models\Rsi\CommLink\Channel\Channel;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Image\Image;
use App\Models\Rsi\CommLink\Series\Series;
use App\Models\System\ModelChangelog;
use Carbon\Carbon;
use Dingo\Api\Dispatcher;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Jenssegers\ImageHash\ImageHash;
use Jenssegers\ImageHash\Implementations\DifferenceHash;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\StrictUnifiedDiffOutputBuilder;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Comm-Link Controller.
 */
class CommLinkController extends Controller
{
    private const COMM_LINK_PERMISSION = 'web.user.rsi.comm-links.update';

    /**
     * @var Dispatcher
     */
    private Dispatcher $api;

    /**
     * CommLinkController constructor.
     *
     * @param Dispatcher $dispatcher
     */
    public function __construct(Dispatcher $dispatcher)
    {
        parent::__construct();
        $this->middleware('auth');
        $this->api = $dispatcher;
        $this->api->be(Auth::user());
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     *
     * @return View
     *
     * @throws AuthorizationException
     */
    public function index(Request $request): View
    {
        $this->authorize('web.user.rsi.comm-links.view');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $options = [
            'limit' => 250,
        ];

        if ($request->has('page')) {
            $options['page'] = $request->get('page');
        }

        $links = $this->api->get('api/comm-links', $options);
        $links->withPath('/rsi/comm-links');

        return view(
            'user.rsi.comm_links.index',
            [
                'commLinks' => $links,
            ]
        );
    }

    /**
     * Display the specified resource.
     *
     * @param CommLink $commLink
     *
     * @return View
     *
     * @throws AuthorizationException
     */
    public function show(CommLink $commLink): View
    {
        $this->authorize('web.user.rsi.comm-links.view');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $commLink->load('translationChangelogs');

        /** @var Collection $changelogs */
        $changelogs = $commLink->changelogs;

        $changelogs = $changelogs->merge($commLink->translationChangelogs);

        $commLink->textChanges = 0;

        $changelogs->each(static function (ModelChangelog $changelog) use ($commLink)  {
            if (!isset($changelog->changelog['changes']['translation'])) {
                return;
            }

            $commLink->textChanges++;

            $builder = new StrictUnifiedDiffOutputBuilder([
                'collapseRanges'      => true,
                'commonLineThreshold' => 1,    // number of same lines before ending a new hunk and creating a new one (if needed)
                'contextLines'        => 0,    // like `diff:  -u, -U NUM, --unified[=NUM]`, for patch/git apply compatibility best to keep at least @ 3
                'fromFile'            => $commLink->created_at->toString(),
                'fromFileDate'        => '',
                'toFile'              => $changelog->created_at->toString(),
                'toFileDate'          => '',
            ]);

            $differ = new Differ($builder);

            $changelog->diff = ($differ->diff(
                $changelog->changelog['changes']['translation']['old'],
                $changelog->changelog['changes']['translation']['new'],
            ));
        });

        $changelogs = $changelogs->sortByDesc('created_at');

        return view(
            'user.rsi.comm_links.show',
            [
                'commLink' => $commLink,
                'changelogs' => $changelogs,
                'prev' => $commLink->getPrevAttribute(),
                'next' => $commLink->getNextAttribute(),
            ]
        );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param CommLink $commLink
     *
     * @return View
     *
     * @throws AuthorizationException
     */
    public function edit(CommLink $commLink): View
    {
        $this->authorize(self::COMM_LINK_PERMISSION);
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $versions = $this->getCommLinkVersions($commLink->cig_id);
        $versionData = $this->processCommLinkVersions($versions, $commLink->file);

        return view(
            'user.rsi.comm_links.edit',
            [
                'commLink' => $commLink,
                'versions' => $versionData,
                'channels' => Channel::query()->orderBy('name')->get(),
                'categories' => Category::query()->orderBy('name')->get(),
                'series' => Series::query()->orderBy('name')->get(),
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param CommLinkRequest $request
     * @param CommLink        $commLink
     *
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function update(CommLinkRequest $request, CommLink $commLink): RedirectResponse
    {
        $this->authorize(self::COMM_LINK_PERMISSION);

        app('Log')::debug(make_name_readable(__FUNCTION__));

        $data = $request->validated();

        $commLink->update(
            [
                'title' => $data['title'],
                'url' => $data['url'],
                'created_at' => $data['created_at'],
                'channel_id' => $data['channel'],
                'category_id' => $data['category'],
                'series_id' => $data['series'],
            ]
        );

        $message = __('crud.updated', ['type' => __('Comm-Link')]);

        return redirect()->route('web.user.rsi.comm-links.show', $commLink->getRouteKey())->withMessages(
            [
                'success' => [
                    $message,
                ],
            ]
        );
    }

    /**
     * Preview a Comm-Link Version.
     *
     * @param CommLink $commLink
     * @param string   $version
     *
     * @return View
     *
     * @throws AuthorizationException
     * @throws FileNotFoundException
     */
    public function preview(CommLink $commLink, string $version)
    {
        $this->authorize('web.user.rsi.comm-links.preview');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $content = Storage::disk('comm_links')->get("{$commLink->cig_id}/{$version}.html");
        $crawler = new Crawler();
        $crawler->addHtmlContent($content, 'UTF-8');

        $contentParser = new Content($crawler);

        return view(
            'user.rsi.comm_links.preview',
            [
                'commLink' => $commLink,
                'version' => $version,
                'preview' => $contentParser->getContent(),
            ]
        );
    }

    /**
     * Reverse search view
     *
     * @return Application|Factory|View
     * @throws AuthorizationException
     */
    public function reverseSearchImage()
    {
        $this->authorize(self::COMM_LINK_PERMISSION);
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view('user.rsi.comm_links.reverse_search_image');
    }

    /**
     * Reverse searches a comm link by an image url
     *
     * @param Request $request
     *
     * @return Application|Factory|View
     * @throws AuthorizationException
     */
    public function reverseSearchImageLinkPost(Request $request)
    {
        $this->authorize(self::COMM_LINK_PERMISSION);
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $options = [
            'limit' => 250,
        ];

        if ($request->has('page')) {
            $options['page'] = $request->get('page');
        }

        $links = $this->api
            ->with(
                array_merge(
                    [
                        'src' => $request->get('src'),
                    ],
                    $options
                )
            )->post('api/comm-links/reverse-search-image');

        return view(
            'user.rsi.comm_links.index',
            [
                'commLinks' => $links,
            ]
        );
    }

    public function reverseSearchImagePost(Request $request)
    {
        $this->authorize(self::COMM_LINK_PERMISSION);
        app('Log')::debug(make_name_readable(__FUNCTION__));

        if (!$request->has('image')) {
            abort(401);
        }

        $similarity = $request->get('similarity', 10);
        if (!is_numeric($similarity)) {
            $similarity = 10;
        }

        $hasher = new ImageHash(new DifferenceHash());

        $bits = $hasher->hash($request->file('image'))->toBits();

        $hashes = DB::table('comm_link_image_hashes')
            ->select('comm_link_image_id')
            ->selectRaw('BIT_COUNT(hash ^ b\''.$bits.'\') as hamming_distance')
            ->having('hamming_distance', '<', $similarity)->get();

        if ($hashes->isEmpty()) {
            return redirect()->route('web.user.rsi.comm-links.reverse-search-image')->withMessages(
                [
                    'warning' => [
                        'Keine Comm-Links gefunden.',
                    ],
                ]
            );
        }

        $images = $hashes->map(function (object $data) {
            $id = $data->comm_link_image_id;
            $image = Image::query()->find($id);
            $image->similarity = ((1 - $data->hamming_distance / 64) * 100);

            return $image;
        })->sortByDesc('similarity');

        return view(
            'user.rsi.comm_links.images.index_hashes',
            [
                'images' => $images,
            ]
        );
    }

    /**
     * Returns all Comm-Link Version Files in a Comm-Link Folder.
     *
     * @param int $commLinkCigId
     *
     * @return array
     */
    private function getCommLinkVersions(int $commLinkCigId): array
    {
        $versions = Storage::disk('comm_links')->files($commLinkCigId);
        $versions = array_map(
            static function ($value) {
                $file = preg_split('#(?<=)[/\\\]#', $value)[1];

                return str_replace('.html', '', $file);
            },
            $versions
        );
        rsort($versions);

        return $versions;
    }

    /**
     * Parses Comm-Link Version Names to a Human readable String, creates Data array to use in views.
     *
     * @param array  $versions
     * @param string $currentVersion
     *
     * @return array
     */
    private function processCommLinkVersions(array $versions, string $currentVersion): array
    {
        $versionData = [];
        collect($versions)->each(
            static function ($version) use (&$versionData, $currentVersion) {
                $output = Carbon::createFromFormat('Y-m-d_His', $version)->format('d.m.Y H:i');

                if ("{$version}.html" === $currentVersion) {
                    $output = sprintf('%s: %s', __('Aktuell'), $output);
                }

                $versionData[] = [
                    'output' => $output,
                    'file_clean' => $version,
                    'file' => "{$version}.html",
                ];
            }
        );

        return $versionData;
    }
}
