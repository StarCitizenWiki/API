<?php

namespace App\Http\Controllers\ShortURL;

use App\Events\URLShortened;
use App\Exceptions\ExpiredException;
use App\Exceptions\HashNameAlreadyAssignedException;
use App\Exceptions\InvalidDataException;
use App\Exceptions\URLNotWhitelistedException;
use App\Exceptions\UserBlacklistedException;
use App\Http\Controllers\Controller;
use App\Models\ShortURL\ShortURL;
use App\Models\ShortURL\ShortURLWhitelist;
use App\Models\User;
use App\Traits\TransformesDataTrait;
use App\Transformers\ShortURL\ShortURLTransformer;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;

/**
 * Class ShortURLController
 *
 * @package App\Http\Controllers\ShortURL
 */
class ShortURLController extends Controller
{
    use TransformesDataTrait;

    /**
     * ShortURLController constructor.
     */
    public function __construct()
    {
        Log::debug('Setting Transformer', [
            'method' => __METHOD__,
        ]);
        $this->transformer = new ShortURLTransformer();
    }

    /**
     * Returns the ShortURL Index View
     *
     * @return View
     */
    public function showShortURLView() : View
    {
        Log::debug('ShortURL Index requested', [
            'method' => __METHOD__,
        ]);

        return view('shorturl.index')
                    ->with(
                        'whitelistedURLs',
                        ShortURLWhitelist::all()
                            ->sortBy('url')
                            ->where('internal', false)
                    );
    }

    /**
     * Returns the ShortURL resolve Web View
     *
     * @return View
     */
    public function showResolveView() : View
    {
        Log::debug('ShortURL Resolve View requested', [
            'method' => __METHOD__,
        ]);

        return view('shorturl.resolve');
    }

    /**
     * Resolves a hash to a url and redirects
     *
     * @param String $hash Hash to resolve
     *
     * @return RedirectResponse
     */
    public function resolveAndRedirect(String $hash)
    {
        Log::debug('Trying to resolve URL hash', [
            'method' => __METHOD__,
            'hash' => $hash,
        ]);
        $url = $this->resolveExceptionRedirectTo('short_url_index', $hash);

        if ($url instanceof RedirectResponse) {
            return $url;
        }

        return redirect($url->url, 301);
    }

    /**
     * Resolves a ShortURL Hash and displays the underlying Long URL
     *
     * @param Request $request Resolve Request
     *
     * @return RedirectResponse
     */
    public function resolveAndDisplay(Request $request)
    {
        Log::debug('Trying to unshorten URL', [
            'method' => __METHOD__,
            'url' => $request->get('url'),
        ]);

        $this->validate($request, [
            'url' => 'required|url',
        ]);

        $url = $request->get('url');
        $url = parse_url($url);

        if (!isset($url['host']) ||
            ($url['host'] != config('app.shorturl_url')) ||
            !isset($url['path'])
        ) {
            Log::info('URL is invalid', [
                'url' => $request->get('url'),
            ]);

            return redirect()->route('short_url_resolve_display')
                             ->withErrors('Invalid Short URL')
                             ->withInput(Input::all());
        }

        $hash = str_replace('/', '', $url['path']);

        $url = $this->resolveExceptionRedirectTo('short_url_resolve_form', $hash);

        if ($url instanceof RedirectResponse) {
            return $url;
        }

        return redirect()->route('short_url_resolve_display')->with('url', $url->url);
    }

    /**
     * Resolves a hash to a url and transforms it
     *
     * @param Request $request Resolve Request
     *
     * @return array
     *
     * @throws InvalidDataException
     */
    public function resolve(Request $request)
    {
        $this->validate($request, [
            'hash_name' => 'required|alpha_dash',
        ]);

        Log::debug('Resolving Hash', [
            'method' => __METHOD__,
            'hash' => $request->get('hash_name'),
        ]);

        try {
            $url = ShortURL::resolve($request->get('hash_name'));
        } catch (ModelNotFoundException | ExpiredException $e) {
            $url = [];
        }

        Log::debug('Hash Resolved', [
            'method' => __METHOD__,
            'url' => (array) $url,
        ]);

        return $this->transform($url)->asArray();
    }

    /**
     * Creates a ShortURL
     *
     * @param Request $request Create Request
     *
     * @return array
     *
     * @throws ExpiredException
     */
    public function create(Request $request)
    {
        $user_id = AUTH_ADMIN_IDS[0];

        $data = [
            'url' => ShortURL::sanitizeURL($request->get('url')),
            'hash_name' => $request->get('hash_name'),
            'expires' => $request->get('expires'),
        ];

        Log::debug('Creating ShortURL', [
            'method' => __METHOD__,
            'data' => $data,
        ]);

        $rules = [
            'url' => 'required|url|max:255|unique:short_urls',
            'hash_name' => 'nullable|alpha_dash|max:32|unique:short_urls',
            'expires' => 'nullable|date',
        ];

        validate_array($data, $rules, $request);

        $expires = $request->get('expires');
        if (!is_null($request->get('expires'))) {
            Log::debug('ShortURL has Expires Field set', [
                'method' => __METHOD__,
                'expires' => $expires,
            ]);
            $expires = Carbon::parse($request->get('expires'));
            if ($expires->lte(Carbon::now())) {
                throw new ExpiredException('Expires date can\'t be in the past');
            }
        }

        $key = $request->get(AUTH_KEY_FIELD_NAME, null);

        if (!is_null($key)) {
            $user = User::where('api_token', $key)->first();
            if (!is_null($user)) {
                $user_id = $user->id;
            }
        }

        $url = ShortURL::createShortURL([
            'url' => ShortURL::sanitizeURL($request->get('url')),
            'hash_name' => $request->get('hash_name'),
            'user_id' => $user_id,
            'expires' => $expires,
        ]);

        event(new URLShortened($url));

        return $this->transform($url)->asArray();
    }

    /**
     * Creates a ShortURL and redirects to the Index with the URL Hash
     *
     * @param Request $request Create Request
     *
     * @return RedirectResponse
     */
    public function createAndRedirect(Request $request)
    {
        Log::debug('Creating ShortURL', [
            'method' => __METHOD__,
        ]);
        try {
            $url = $this->create($request);
        } catch (HashNameAlreadyAssignedException | URLNotWhitelistedException | ExpiredException $e) {
            return redirect()->route('short_url_index')
                             ->withErrors($e->getMessage())
                             ->withInput(Input::all());
        }

        return redirect()->route('short_url_index')
                         ->with('hash_name', $url['data'][0]['hash_name']);
    }

    /**
     * Tries to resolve a given hash, renders Exceptions to Responses
     *
     * @param String $route
     * @param String $hash
     *
     * @return ShortURL | RedirectResponse
     */
    private function resolveExceptionRedirectTo(String $route, String $hash)
    {
        try {
            $url = ShortURL::resolve($hash);
        } catch (ModelNotFoundException $e) {
            return redirect()->route($route)
                ->withErrors('No URL found')
                ->withInput(Input::all());
        } catch (UserBlacklistedException | ExpiredException $e) {
            return redirect()->route($route)
                ->withErrors($e->getMessage())
                ->withInput(Input::all());
        }

        return $url;
    }
}
