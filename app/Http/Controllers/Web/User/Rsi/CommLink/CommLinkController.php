<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User\Rsi\CommLink;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rsi\CommLink\CommLinkRequest;
use App\Jobs\Rsi\CommLink\Parser\Element\Content;
use App\Models\Rsi\CommLink\Category\Category;
use App\Models\Rsi\CommLink\Channel\Channel;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Series\Series;
use App\Models\System\ModelChangelog;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\DomCrawler\Crawler;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\StrictUnifiedDiffOutputBuilder;

/**
 * Comm-Link Controller.
 */
class CommLinkController extends Controller
{
    private const COMM_LINK_PERMISSION = 'web.user.rsi.comm-links.update';

    /**
     * CommLinkController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     *
     * @throws AuthorizationException
     */
    public function index()
    {
        $this->authorize('web.user.rsi.comm-links.view');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $links = CommLink::query()->withCount(['changelogs', 'translationChangelogs'])->orderByDesc('cig_id')->paginate(500);

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
     * @return Response
     *
     * @throws AuthorizationException
     */
    public function show(CommLink $commLink)
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
     * @return Response
     *
     * @throws AuthorizationException
     */
    public function edit(CommLink $commLink)
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
     * @param \App\Http\Requests\Rsi\CommLink\CommLinkRequest $request
     * @param CommLink                                        $commLink
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function update(CommLinkRequest $request, CommLink $commLink)
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
