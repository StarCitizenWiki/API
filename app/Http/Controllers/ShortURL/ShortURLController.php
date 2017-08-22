<?php declare(strict_types = 1);

namespace App\Http\Controllers\ShortURL;

use App\Events\URLShortened;
use App\Exceptions\ExpiredException;
use App\Exceptions\HashNameAlreadyAssignedException;
use App\Exceptions\URLNotWhitelistedException;
use App\Exceptions\UserBlacklistedException;
use App\Http\Controllers\Controller;
use App\Models\ShortURL\ShortURL;
use App\Models\ShortURL\ShortURLWhitelist;
use App\Models\User;
use App\Traits\ProfilesMethodsTrait;
use App\Traits\TransformesDataTrait;
use App\Transformers\ShortURL\ShortURLTransformer;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

/**
 * Class ShortURLController
 *
 * @package App\Http\Controllers\ShortURL
 */
class ShortURLController extends Controller
{
    use TransformesDataTrait;
    use ProfilesMethodsTrait;

    /**
     * ShortURLController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->addTrace('Setting Transformer', __FUNCTION__, 0);
        $this->transformer = new ShortURLTransformer();
        $this->middleware('throttle', ['except' => ['showShortURLView', 'showResolveView']]);
    }

    /**
     * Returns the ShortURL Index View
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function showShortURLView(): View
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return view('shorturl.index')->with(
            'whitelistedURLs',
            ShortURLWhitelist::all()->sortBy('url')->where('internal', false)
        );
    }

    /**
     * Returns the ShortURL resolve Web View
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function showResolveView(): View
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return view('shorturl.resolve');
    }

    /**
     * Resolves a hash to a url and redirects
     *
     * @param string $hash Hash to resolve
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resolveAndRedirect(string $hash)
    {
        $this->startProfiling(__FUNCTION__);

        app('Log')::info("Resolving URL Hash: {$hash}");
        $url = $this->getURLRedirectIfException('short_url_index', $hash);

        if ($url instanceof RedirectResponse) {
            $this->addTrace("No URL for Hash: {$hash} found", __FUNCTION__, __LINE__);
            $this->stopProfiling(__FUNCTION__);

            return $url;
        }
        $this->addTrace("Redirecting to URL: {$url->url}", __FUNCTION__, __LINE__);
        $this->stopProfiling(__FUNCTION__);

        return redirect($url->url, 301);
    }

    /**
     * Resolves a ShortURL Hash and displays the underlying Long URL
     *
     * @param \Illuminate\Http\Request $request Resolve Request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resolveAndDisplay(Request $request)
    {
        $this->startProfiling(__FUNCTION__);
        $this->addTrace("Resolving ShortURL {$request->get('url')}", __FUNCTION__, __LINE__);

        $this->validate(
            $request,
            [
                'url' => 'required|url',
            ]
        );

        $url = $request->get('url');
        $url = parse_url($url);

        if (!isset($url['host']) || ($url['host'] != config('app.shorturl_url')) || !isset($url['path'])) {
            app('Log')::notice('URL is invalid', ['url' => $request->get('url')]);
            $this->stopProfiling(__FUNCTION__);

            return redirect()->route('short_url_resolve_display')->withErrors('Invalid Short URL')->withInput(
                Input::all()
            );
        }

        $hash = str_replace('/', '', $url['path']);

        $url = $this->getURLRedirectIfException('short_url_resolve_form', $hash);

        if ($url instanceof RedirectResponse) {
            $this->addTrace("No Long-URL for Hash: {$hash} found", __FUNCTION__, __LINE__);
            $this->stopProfiling(__FUNCTION__);

            return $url;
        }

        $this->stopProfiling(__FUNCTION__);

        return redirect()->route('short_url_resolve_display')->with('url', $url->url);
    }

    /**
     * Resolves a hash to a url and transforms it
     *
     * @param \Illuminate\Http\Request $request Resolve Request
     *
     * @return array
     *
     * @throws \App\Exceptions\InvalidDataException
     */
    public function resolve(Request $request)
    {
        $this->startProfiling(__FUNCTION__);

        $this->validate(
            $request,
            [
                'hash_name' => 'required|alpha_dash',
            ]
        );

        app('Log')::info("Resolving Hash: {$request->get('hash_name')}");

        try {
            $this->addTrace("Getting URL for Hash: {$request->get('hash_name')}", __FUNCTION__, __LINE__);
            $url = ShortURL::resolve($request->get('hash_name'));
        } catch (ModelNotFoundException | ExpiredException $e) {
            $this->addTrace(get_class($e), __FUNCTION__, __LINE__);
            $url = [];
        }

        $this->stopProfiling(__FUNCTION__);

        return $this->transform($url)->toArray();
    }

    /**
     * Creates a ShortURL and redirects to the Index with the URL Hash
     *
     * @param \Illuminate\Http\Request $request Create Request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function createAndRedirect(Request $request)
    {
        try {
            $url = $this->create($request);
        } catch (HashNameAlreadyAssignedException | URLNotWhitelistedException | ExpiredException $e) {
            return redirect('/')->withErrors($e->getMessage())->withInput(Input::all());
        }

        return redirect('/')->with(
            'hash_name',
            $url['data'][0]['hash_name']
        );
    }

    /**
     * Creates a ShortURL
     *
     * @param \Illuminate\Http\Request $request Create Request
     *
     * @return array
     *
     * @throws \App\Exceptions\ExpiredException
     */
    public function create(Request $request)
    {
        $this->startProfiling(__FUNCTION__);

        $userID = 1;

        $data = [
            'url'       => ShortURL::sanitizeURL($request->get('url')),
            'hash_name' => $request->get('hash_name'),
            'expires'   => $request->get('expires'),
        ];

        app('Log')::info('Creating ShortURL', ['data' => $data]);

        $rules = [
            'url'       => 'required|url|max:255|unique:short_urls',
            'hash_name' => 'nullable|alpha_dash|max:32|unique:short_urls',
            'expires'   => 'nullable|date',
        ];

        validate_array($data, $rules, $request);

        $expires = $request->get('expires');
        ShortURL::checkIfDateIsPast($expires);

        $key = $request->header('Authorization', null);

        if (is_null($key)) {
            $key = $request->query->get('Authorization', null);
        }

        if (!is_null($key)) {
            $this->addTrace("Key: {$key} is not null", __FUNCTION__, __LINE__);
            $user = User::where('api_token', $key)->first();
            if (!is_null($user)) {
                $userID = $user->id;
                $this->addTrace("Provided Key belongs to User {$userID} ({$user->email})", __FUNCTION__, __LINE__);
            }
        }

        $this->addTrace('Creating ShortURL', __FUNCTION__, __LINE__);
        $url = ShortURL::createShortURL(
            [
                'url'       => ShortURL::sanitizeURL($request->get('url')),
                'hash_name' => $request->get('hash_name'),
                'user_id'   => $userID,
                'expires'   => $expires,
            ]
        );
        event(new URLShortened($url));

        $this->stopProfiling(__FUNCTION__);

        return $this->transform($url)->toArray();
    }

    /**
     * Tries to resolve a given hash, renders Exceptions to Responses
     *
     * @param string $route route
     * @param string $hash  urlHash
     *
     * @return \App\Models\ShortURL\ShortURL | \Illuminate\Http\RedirectResponse
     */
    private function getURLRedirectIfException(string $route, string $hash)
    {
        try {
            $url = ShortURL::resolve($hash);
        } catch (ModelNotFoundException $e) {
            return redirect()->route($route)->withErrors('No URL found')->withInput(Input::all());
        } catch (UserBlacklistedException | ExpiredException $e) {
            return redirect()->route($route)->withErrors($e->getMessage())->withInput(Input::all());
        }

        return $url;
    }
}
