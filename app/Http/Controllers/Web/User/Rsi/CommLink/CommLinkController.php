<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\User\Rsi\CommLink;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rsi\CommLink\CommLinkRequest;
use App\Jobs\Rsi\CommLink\Parser\Element\Content;
use App\Jobs\Rsi\CommLink\Parser\ParseCommLink;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\CommLinkTranslation;
use App\Models\System\ModelChangelog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Comm-Link Controller
 */
class CommLinkController extends Controller
{
    const COMM_LINK_PERMISSION = 'web.user.rsi.comm-links.update';

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
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('web.user.rsi.comm-links.view');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $links = CommLink::query()->orderByDesc('cig_id')->paginate(100);

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
     * @param \App\Models\Rsi\CommLink\CommLink $commLink
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(CommLink $commLink)
    {
        $this->authorize('web.user.rsi.comm-links.view');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $previous = CommLink::query()->where('id', '<', $commLink->id)->orderBy('id', 'desc')->first(['cig_id']);
        $next = CommLink::query()->where('id', '>', $commLink->id)->orderBy('id')->first(['cig_id']);

        /** @var \Illuminate\Support\Collection $changelog */
        $changelog = $commLink->changelogs;
        $commLink->translations->each(
            function (CommLinkTranslation $translation) use (&$changelog) {
                $translation->changelogs->each(
                    function (ModelChangelog $transChange) use (&$changelog) {
                        $changelog->push($transChange);
                    }
                );
            }
        );

        $changelog = $changelog->sortByDesc('created_at');

        return view(
            'user.rsi.comm_links.show',
            [
                'commLink' => $commLink,
                'changelogs' => $changelog,
                'prev' => $previous,
                'next' => $next,
            ]
        );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Rsi\CommLink\CommLink $commLink
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
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
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\Rsi\CommLink\CommLinkRequest $request
     * @param \App\Models\Rsi\CommLink\CommLink               $commLink
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(CommLinkRequest $request, CommLink $commLink)
    {
        $this->authorize(self::COMM_LINK_PERMISSION);
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $data = $request->validated();

        if (isset($data['version']) && $data['version'] !== $commLink->file) {
            $this->authorize(self::COMM_LINK_PERMISSION);
            $message = __('Comm-Link Import gestartet');

            dispatch(new ParseCommLink($commLink->cig_id, $data['version'], $commLink, true));
        } else {
            unset($data['version']);

            $commLink->update(
                [
                    'title' => $data['title'],
                    'url' => $data['url'],
                    'created_at' => $data['created_at'],
                ]
            );

            $message = __('crud.updated', ['type' => __('Comm-Link')]);
        }

        return redirect()->route('web.user.rsi.comm-links.show', $commLink->getRouteKey())->withMessages(
            [
                'success' => [
                    $message,
                ],
            ]
        );
    }

    /**
     * Preview a Comm-Link Version
     *
     * @param \App\Models\Rsi\CommLink\CommLink $commLink
     * @param string                            $version
     *
     * @return \Illuminate\View\View
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
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
     * Returns all Comm-Link Version Files in a Comm-Link Folder
     *
     * @param int $commLinkCigId
     *
     * @return array
     */
    private function getCommLinkVersions(int $commLinkCigId): array
    {
        $versions = Storage::disk('comm_links')->files($commLinkCigId);
        $versions = array_map(
            function ($value) {
                $file = preg_split('#(?<=)[/\\\]#', $value)[1];

                return str_replace('.html', '', $file);
            },
            $versions
        );
        rsort($versions);

        return $versions;
    }

    /**
     * Parses Comm-Link Version Names to a Human readable String, creates Data array to use in views
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
            function ($version) use (&$versionData, $currentVersion) {
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
