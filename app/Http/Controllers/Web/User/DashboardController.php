<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Models\Account\User\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;
use MediaWiki\OAuthClient\Consumer;
use MediaWiki\OAuthClient\Request;
use MediaWiki\OAuthClient\SignatureMethod\HmacSha1;
use MediaWiki\OAuthClient\Token;

/**
 * Class DashboardController
 */
class DashboardController extends Controller
{
    /**
     * AdminController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
    }

    /**
     * Returns the Dashboard View
     *
     * @return \Illuminate\Contracts\View\View
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(): View
    {
        $this->authorize('web.user.dashboard.view');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $today = Carbon::today()->toDateString();

        $params = [
            'action' => 'query',
            'prop' => 'info',
            'meta' => 'tokens',
            'titles' => 'Test',
            'format' => 'json',
        ];


        $consumer = new Consumer(config('services.mediawiki.client_id'), config('services.mediawiki.client_secret'));

        $userToken = Session::get('oauth.user_token');
        $userSecret = Session::get('oauth.user_secret');
        $accessToken = new Token($userToken, $userSecret);
        $request = Request::fromConsumerAndToken(
            $consumer,
            $accessToken,
            'GET',
            config('api.wiki_url').'/api.php',
            $params
        );
        $request->signRequest(new HmacSha1(), $consumer, $accessToken);
        $header = $request->toHeader();

        $header = explode(':', $header);

        $client = new Client(
            [
                'headers' => [
                    $header[0] => $header[1],
                ],
            ]
        );
        $response = $client->get(
            config('api.wiki_url').'/api.php?'.http_build_query($params)
        );

        $token = json_decode($response->getBody()->getContents(), true)['query']['tokens']['csrftoken'];








        $apiParams = [
            'action' => 'edit',
            'title' => 'Test',
            'summary' => 'TestEdit',
            'text' => 'ABC Test 123',
            'token' => $token,
        ];

        $request = Request::fromConsumerAndToken(
            $consumer,
            $accessToken,
            'POST',
            config('api.wiki_url').'/api.php',
            $apiParams
        );
        $request->signRequest(new HmacSha1(), $consumer, $accessToken);
        $header = $request->toHeader();

        $header = explode(':', $header);

        $client = new Client(
            [
                'headers' => [
                    $header[0] => $header[1],
                ],
            ]
        );
        $response = $client->post(
            config('api.wiki_url').'/api.php',
            [
                'form_params' => $apiParams,
            ]
        );

        dump($response->getStatusCode());
        dump($response->getHeaders());
        dd(json_decode($response->getBody()->getContents()));

        $users = [
            'overall' => User::all()->count(),
            'last' => User::query()->take(5)->orderBy('created_at', 'desc')->get(),
            'registrations' => [
                'counts' => [
                    'last_hour' => User::query()->whereDate('created_at', '>', Carbon::now()->subHour())->count(),
                    'today' => User::query()->whereDate('created_at', '=', $today)->get()->count(),
                    'overall' => User::all()->count(),
                ],
            ],
            'logins' => [
                'counts' => [
                    'last_hour' => User::query()->whereDate('last_login', '>', Carbon::now()->subHour())->count(),
                    'today' => User::query()->whereDate('last_login', '=', $today)->get()->count(),
                    'overall' => User::all()->count(),
                ],
            ],
        ];

        return view(
            'user.dashboard',
            [
                'users' => $users,
            ]
        );
    }
}
