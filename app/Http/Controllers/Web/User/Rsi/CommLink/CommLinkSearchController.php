<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User\Rsi\CommLink;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rsi\CommLink\CommLinkSearchRequest;
use App\Http\Requests\Rsi\CommLink\ReverseImageLinkSearchRequest;
use App\Http\Requests\Rsi\CommLink\ReverseImageSearchRequest;
use Dingo\Api\Dispatcher;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\View\View;

/**
 * Comm-Link Controller.
 */
class CommLinkSearchController extends Controller
{
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
        $this->api = $dispatcher;
        $this->api->be(Auth::user());
    }

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

        $links = $this->api->with(
            [
                'keyword' => $data['keyword'],
            ]
        )->post('api/comm-links/search');

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
        $options = [
            'limit' => 250,
        ];

        if ($request->has('page')) {
            $options['page'] = $request->get('page');
        }

        return $this->handleSearchResult(
            $this->api
                ->with(
                    array_merge(
                        [
                            'url' => $request->get('url'),
                        ],
                        $options
                    )
                )->post('api/comm-links/reverse-image-link-search'),
            'user.rsi.comm_links.index'
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
        $data = $request->validated();

        return $this->handleSearchResult(
            $this->api
                ->with(
                    [
                        'method' => $data['method'],
                        'similarity' => $data['similarity'],
                    ]
                )
                ->attach(
                    [
                        'image' => $data['image'],
                    ]
                )
                ->post('api/comm-links/reverse-image-search'),
            'user.rsi.comm_links.images.index'
        );
    }

    /**
     * Things to do after a search request was done
     *
     * @param $results
     * @param string $view
     * @return Application|Factory|View
     */
    private function handleSearchResult($results, string $view)
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
                'images' => $results,
            ]
        );
    }
}
