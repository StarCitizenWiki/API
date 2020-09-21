<?php declare(strict_types=1);

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
use Illuminate\View\View;

/**
 * Comm-Link Controller.
 */
class CommLinkSearchController extends Controller
{
    private const COMM_LINK_PERMISSION = 'web.user.rsi.comm-links.view';

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
     * Reverse search view
     *
     * @return Application|Factory|View
     * @throws AuthorizationException
     */
    public function search()
    {
        $this->authorize(self::COMM_LINK_PERMISSION);

        return view('user.rsi.comm_links.search');
    }

    /**
     * @param CommLinkSearchRequest $request
     *
     * @return View|RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function searchByTitle(CommLinkSearchRequest $request)
    {
        $this->authorize(self::COMM_LINK_PERMISSION);

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
     * @throws AuthorizationException
     */
    public function reverseImageLinkSearchPost(ReverseImageLinkSearchRequest $request)
    {
        $this->authorize(self::COMM_LINK_PERMISSION);

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
                        'url' => $request->get('url'),
                    ],
                    $options
                )
            )->post('api/comm-links/reverse-image-link-search');

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
     * Reverse searches a comm-link by an actual image file
     *
     * @param ReverseImageSearchRequest $request
     *
     * @return Application|Factory|View
     * @throws AuthorizationException
     */
    public function reverseImageSearchPost(ReverseImageSearchRequest $request)
    {
        $this->authorize(self::COMM_LINK_PERMISSION);

        $data = $request->validated();

        $links = $this->api
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
            ->post('api/comm-links/reverse-image-search');

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
            'user.rsi.comm_links.images.index',
            [
                'images' => $links,
            ]
        );
    }
}
