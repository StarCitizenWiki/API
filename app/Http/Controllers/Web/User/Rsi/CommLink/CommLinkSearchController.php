<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User\Rsi\CommLink;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rsi\CommLink\CommLinkSearchRequest;
use App\Http\Requests\Rsi\CommLink\ReverseImageLinkSearchRequest;
use App\Http\Requests\Rsi\CommLink\ReverseImageSearchRequest;
use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Comm-Link Controller.
 */
class CommLinkSearchController extends Controller
{

    /**
     * Reverse search view
     *
     * @return Application|Factory|View
     */
    public function search()
    {
        return view('user.rsi.comm_links.search')->with('apiToken', optional(Auth::user())->api_token);
    }

    /**
     * @param CommLinkSearchRequest $request
     *
     * @return View|RedirectResponse
     */
    public function searchByTitle(CommLinkSearchRequest $request)
    {
        $data = $request->validated();

        $query = $data['keyword'];
        $links = CommLink::query()->where('title', 'LIKE', "%{$query}%");

        if ($links->isEmpty()) {
            return redirect()->route('web.user.rsi.comm-links.search')->withMessages(
                [
                    'warning' => [
                        'Keine Comm-Links gefunden.',
                    ],
                ]
            );
        }

        return view(
            'user.rsi.comm_links.index',
            [
                'commLinks' => $links,
            ]
        );
    }

    /**
     * Reverse searches a comm-link by an image url
     *
     * @param ReverseImageLinkSearchRequest $request
     *
     * @return Application|Factory|View
     */
    public function reverseImageLinkSearchPost(ReverseImageLinkSearchRequest $request)
    {
        $controller = new \App\Http\Controllers\Api\V2\Rsi\CommLink\CommLinkSearchController($request);

        return $this->handleSearchResult(
            $controller->reverseImageLinkSearch($request),
            'user.rsi.comm_links.index',
            'commLinks'
        );
    }

    /**
     * Reverse searches a comm-link by an actual image file
     *
     * @param ReverseImageSearchRequest $request
     *
     * @return Application|Factory|View
     */
    public function reverseImageSearchPost(ReverseImageSearchRequest $request)
    {
        $controller = new \App\Http\Controllers\Api\V2\Rsi\CommLink\CommLinkSearchController($request);

        return $this->handleSearchResult(
            $controller->reverseImageSearch($request),
            'user.rsi.comm_links.images.index'
        );
    }

    /**
     * Search for images based on text content of the comm-link
     *
     * @param CommLinkSearchRequest $request
     *
     * @return Application|Factory|View
     */
    public function imageTextSearchPost(CommLinkSearchRequest $request)
    {
        $this->middleware('auth');
        $data = $request->validated();

        $data = $data['query'] ?? $data['keyword'];

        $images = CommLink::query()
            ->whereHas('translations', function (Builder $query) use ($data) {
                return $query->where('translation', 'LIKE', sprintf('%%%s%%', $data));
            })
            ->orderByDesc('created_at')
            ->get()
            ->flatMap(function (CommLink $commLink) {
                return $commLink->images;
            })
            ->unique('url');

        if ($images->isEmpty()) {
            return $this->handleSearchResult($images, 'user.rsi.comm_links.images.index');
        }

        return view(
            'user.rsi.comm_links.images.index',
            [
                'images' => $images,
                'keyword' => $data,
            ]
        );
    }

    /**
     * Things to do after a search request was done
     *
     * @param $results
     * @param string $view
     * @param string $key
     * @return Application|Factory|View
     */
    private function handleSearchResult($results, string $view, string $key = 'images')
    {
        if ($results->isEmpty()) {
            return redirect()->route('web.user.rsi.comm-links.search')->withMessages(
                [
                    'warning' => [
                        'Keine Comm-Links gefunden.',
                    ],
                ]
            );
        }

        return view(
            $view,
            [
                $key => $results,
            ]
        );
    }
}
