<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\Admin\Rsi\CommLink;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommLinkRequest;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\CommLinkTranslation;
use App\Models\System\ModelChangelog;

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
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(CommLink $commLink)
    {
        $this->authorize('web.admin.rsi.comm_links.update');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view(
            'admin.rsi.comm_links.edit',
            [
                'commLink' => $commLink,
            ]
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
     */
    public function update(CommLinkRequest $request, CommLink $commLink)
    {
        $this->authorize('web.admin.rsi.comm_links.update');
        app('Log')::debug(make_name_readable(__FUNCTION__));
        $data = $request->validated();

        $commLink->update(
            [
                'title' => array_pull($data, 'title'),
                'url' => array_pull($data, 'url'),
                'created_at' => array_pull($data, 'created_at'),
            ]
        );

        foreach ($data as $localeCode => $translation) {
            if (config('language.english') !== $localeCode) {
                $commLink->translations()->updateOrCreate(
                    ['locale_code' => $localeCode],
                    ['translation' => $translation]
                );
            }
        }

        return redirect()->route('web.admin.rsi.comm_links.show', $commLink->getRouteKey())->withMessages(
            [
                'success' => [
                    __('crud.updated', ['type' => __('Comm Link')]),
                ],
            ]
        );
    }
}
