<?php

namespace App\Http\Controllers\ShortURL;

use App\Events\URLShortened;
use App\Exceptions\HashNameAlreadyAssignedException;
use App\Exceptions\InvalidDataException;
use App\Exceptions\URLNotWhitelistedException;
use App\Exceptions\UserBlacklistedException;
use App\Http\Controllers\Controller;
use App\Models\ShortURL\ShortURL;
use App\Models\ShortURL\ShortURLWhitelist;
use App\Models\User;
use App\Traits\TransformesData;
use App\Transformers\Tools\ShortURLTransformer;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Spatie\Fractal\Fractal;

/**
 * Class ShortURLController
 *
 * @package App\Http\Controllers\ShortURL
 */
class ShortURLController extends Controller
{
    use TransformesData;

    /**
     * ShortURLController constructor.
     */
    public function __construct()
    {
        $this->transformer = new ShortURLTransformer();
    }

    /**
     * Returns the ShortURL Index View
     *
     * @return View
     */
    public function showShortURLView() : View
    {
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
        return view('shorturl.resolve');
    }

    /**
     * Resolves a hash to a url and redirects
     *
     * @param String $hashName Hash to resolve
     *
     * @return RedirectResponse
     */
    public function resolveAndRedirect(String $hashName)
    {
        try {
            $url = ShortURL::resolve($hashName);
        } catch (ModelNotFoundException $e) {
            return redirect()->route('short_url_index')
                             ->withErrors('No URL found');
        } catch (UserBlacklistedException $e) {
            return redirect()->route('short_url_index')
                             ->withErrors('User is blacklisted');
        }

        return redirect($url->url, HTTP_REDIRECT_PERM);
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
        $this->validate(
            $request,
            ['url' => 'required|url',]
        );

        $url = $request->get('url');
        $url = parse_url($url);

        if (
            !isset($url['host']) ||
            ($url['host'] != config('app.shorturl_url')) ||
            !isset($url['path'])
        ) {
            return redirect()->route('short_url_resolve')
                             ->withErrors('Invalid Short URL');
        }

        $path = str_replace('/', '', $url['path']);

        try {
            $url = ShortURL::resolve($path);
        } catch (ModelNotFoundException $e) {
            return redirect()->route('short_url_resolve_form')
                             ->withErrors('No URL found');
        } catch (UserBlacklistedException $e) {
            return redirect()->route('short_url_index')
                             ->withErrors('User is blacklisted, can\'t resolve URL');
        }

        return redirect()->route('short_url_resolve')->with('url', $url->url);
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
        $this->validate(
            $request,
            ['hash_name' => 'required|alpha_dash',]
        );

        try {
            $url = ShortURL::resolve($request->get('hash_name'));
            $this->item();
        } catch (ModelNotFoundException $e) {
            $url = [];
        }

        return $this->transform($url)->asArray();
    }

    /**
     * Updates a ShortURL by ID
     *
     * @param Request $request Update Request and Data
     * @param int     $id      ShortURL ID
     *
     * @return bool
     */
    public function update(Request $request, int $id)
    {
        $data = [
            'url' => ShortURL::sanitizeURL($request->get('url')),
            'hash_name' => $request->get('hash_name'),
            'user_id' => $request->get('user_id')
        ];

        $rules = [
            'url' => 'required|active_url|max:255',
            'hash_name' => 'required|alpha_dash|max:32',
            'user_id' => 'required|integer|exists:users,id'
        ];

        validate_array($data, $rules, $request);

        $url = ShortURL::updateShortURL(
            [
                'id' => $id,
                'url' => ShortURL::sanitizeURL($request->get('url')),
                'hash_name' => $request->get('hash_name'),
                'user_id' => $request->get('user_id'),
            ]
        );

        return $url;
    }

    /**
     * Creates a ShortURL
     *
     * @param Request $request Create Request
     *
     * @return array
     */
    public function create(Request $request)
    {
        $user_id = AUTH_ADMIN_IDS[0];

        $data = [
            'url' => ShortURL::sanitizeURL($request->get('url')),
            'hash_name' => $request->get('hash_name')
        ];

        $rules = [
            'url' => 'required|active_url|max:255|unique:short_urls',
            'hash_name' => 'nullable|alpha_dash|max:32|unique:short_urls'
        ];

        validate_array($data, $rules, $request);

        $key = $request->get(AUTH_KEY_FIELD_NAME, null);

        if (!is_null($key)) {
            $user = User::where('api_token', $key)->first();
            if (!is_null($user)) {
                $user_id = $user->id;
            }
        }

        $url = ShortURL::createShortURL(
            [
                'url' => ShortURL::sanitizeURL($request->get('url')),
                'hash_name' => $request->get('hash_name'),
                'user_id' => $user_id
            ]
        );

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
        try {
            $url = $this->create($request);
        } catch (HashNameAlreadyAssignedException | URLNotWhitelistedException $e) {
            return redirect()->route('short_url_index')
                             ->withErrors($e->getMessage());
        }

        return redirect()->route('short_url_index')
                         ->with('hash_name', $url['data'][0]['hash_name']);
    }

    /**
     * Deletes a ShortURL by ID
     *
     * @param int $id ShortURL ID
     *
     * @return void
     */
    public function delete(int $id) : void
    {
        $url = ShortURL::find($id);

        Log::info(
            'URL Deleted',
            [
                'id' => $url->id,
                'owner' => $url->user()->first()->email,
                'deleted_by' => Auth::user()->email
            ]
        );

        $url->delete();
    }
}
