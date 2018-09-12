<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\Admin\Rsi\CommLink;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommLinkRequest;
use App\Jobs\Rsi\CommLink\Parser\Element\Content;
use App\Jobs\Rsi\CommLink\Parser\ParseCommLink;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\CommLinkTranslation;
use App\Models\System\ModelChangelog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Comm Link Controller
 */
class CommLinkController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('web.admin.rsi.comm_links.view');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $links = CommLink::orderByDesc('cig_id')->paginate(100);

        return view(
            'admin.rsi.comm_links.index',
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
        $this->authorize('web.admin.rsi.comm_links.view');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $previous = CommLink::where('id', '<', $commLink->id)->orderBy('id', 'desc')->first(['cig_id']);
        $next = CommLink::where('id', '>', $commLink->id)->orderBy('id')->first(['cig_id']);

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
            'admin.rsi.comm_links.show',
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
     * @param null|string                       $previewVersion
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function edit(CommLink $commLink, ?string $previewVersion = null)
    {
        $this->authorize('web.admin.rsi.comm_links.update');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $versions = Storage::disk('comm_links')->files($commLink->cig_id);
        $versions = array_map(
            function ($value) {
                $file = preg_split('#(?<=)[/\\\]#', $value)[1];

                return str_replace('.html', '', $file);
            },
            $versions
        );
        rsort($versions);

        $versionData = [];
        foreach ($versions as $version) {
            $output = Carbon::createFromFormat('Y-m-d_His', $version)->format('d.m.Y H:i');

            if ("{$version}.html" === $commLink->file) {
                $output = sprintf('%s: %s', __('Aktuell'), $output);
            }

            $versionData[] = [
                'output' => $output,
                'file' => "{$version}.html",
            ];
        }

        $data = [
            'commLink' => $commLink,
            'versions' => $versionData,
        ];

        if (null !== $previewVersion) {
            $data['preview'] = $this->getPreviewVersionContent($commLink, $previewVersion);
        }

        return view(
            'admin.rsi.comm_links.edit',
            $data
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\CommLinkRequest $request
     * @param \App\Models\Rsi\CommLink\CommLink  $commLink
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function update(CommLinkRequest $request, CommLink $commLink)
    {
        $this->authorize('web.admin.rsi.comm_links.update');
        $data = $request->validated();

        // TODO Hacky
        if ($request->has('preview')) {
            return $this->edit($commLink, $data['version']);
        }

        app('Log')::debug(make_name_readable(__FUNCTION__));


        if (isset($data['version']) && $data['version'] !== $commLink->file) {
            $message = __('Comm Link Import gestartet');

            dispatch(new ParseCommLink($commLink->cig_id, $data['version'], $commLink, true));
        } else {
            unset($data['version']);

            $commLink->update(
                [
                    'title' => array_pull($data, 'title'),
                    'url' => array_pull($data, 'url'),
                    'created_at' => array_pull($data, 'created_at'),
                ]
            );

            foreach ($data as $localeCode => $translation) {
                if (config('language.english') !== $localeCode && null !== $translation) {
                    $commLink->translations()->updateOrCreate(
                        ['locale_code' => $localeCode],
                        ['translation' => $translation]
                    );
                }
            }

            $message = __('crud.updated', ['type' => __('Comm Link')]);
        }

        return redirect()->route('web.admin.rsi.comm_links.show', $commLink->getRouteKey())->withMessages(
            [
                'success' => [
                    $message,
                ],
            ]
        );
    }

    /**
     * @param \App\Models\Rsi\CommLink\CommLink $commLink
     * @param string                            $version
     *
     * @return string
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function getPreviewVersionContent(CommLink $commLink, string $version)
    {
        $this->authorize('web.admin.rsi.comm_links.update_settings');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $content = Storage::disk('comm_links')->get("{$commLink->cig_id}/{$version}");
        $crawler = new Crawler();
        $crawler->addHtmlContent($content, 'UTF-8');

        $contentParser = new Content($crawler);

        return $contentParser->getContent();
    }
}
