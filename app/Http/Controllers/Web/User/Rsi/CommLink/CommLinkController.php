<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User\Rsi\CommLink;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rsi\CommLink\CommLinkRequest;
use App\Models\Rsi\CommLink\Category\Category;
use App\Models\Rsi\CommLink\Channel\Channel;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Series\Series;
use App\Models\System\ModelChangelog;
use App\Services\Parser\CommLink\Content;
use Carbon\Carbon;
use Dingo\Api\Dispatcher;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
     * @param int $commLinkId
     *
     * @return View
     *
     * @throws AuthorizationException
     */
    public function show(int $commLinkId): View
    {
        $this->authorize('web.user.rsi.comm-links.view');

        $commLink = CommLink::query()
            ->with(
                [
                    'images',
                    'links',
                    'translations',
                    'translationChangelogs',
                    'images.commLinks',
                ]
            )
            ->where('cig_id', $commLinkId)
            ->firstOrFail();

        /** @var Collection $changelogs */
        $changelogs = $commLink->changelogs;

        $changelogs = $changelogs->merge($commLink->translationChangelogs);

        $commLink->textChanges = 0;

        $changelogs->each(
            static function (ModelChangelog $changelog) use ($commLink) {
                if (!isset($changelog->changelog['changes']['translation'])) {
                    return;
                }

                $commLink->textChanges++;

                $builder = new StrictUnifiedDiffOutputBuilder(
                    [
                        'collapseRanges' => true,
                        'commonLineThreshold' => 1,
                        // number of same lines before ending a new hunk and creating a new one (if needed)
                        'contextLines' => 0,
                        // like `diff:  -u, -U NUM, --unified[=NUM]`, for patch/git apply compatibility best to keep at least @ 3
                        'fromFile' => $commLink->created_at->toString(),
                        'fromFileDate' => '',
                        'toFile' => $changelog->created_at->toString(),
                        'toFileDate' => '',
                    ]
                );

                $differ = new Differ($builder);

                $changelog->diff = ($differ->diff(
                    $changelog->changelog['changes']['translation']['old'],
                    $changelog->changelog['changes']['translation']['new'],
                ));
            }
        );

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
    public function preview(CommLink $commLink, string $version): View
    {
        $this->authorize('web.user.rsi.comm-links.preview');

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
}
