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
use App\Transformers\Tools\ShortURLTransformer;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Spatie\Fractal\Fractal;

class ShortURLController extends Controller
{
    private $_fractalManager;

    function __construct()
    {
        $this->_fractalManager = Fractal::create();
    }

    /**
     * @return View
     */
    public function showShortURLView()
    {
        return view('shorturl.index')->with('whitelistedURLs', ShortURLWhitelist::all()->sortBy('url')->where('internal', false));
    }

    /**
     * resolves a hash to a url and redirects
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function resolveAndRedirect(Request $request, String $hashName)
    {
        try {
            $url = ShortURL::resolve($hashName);
        } catch (ModelNotFoundException $e) {
            return redirect()->route('short_url_index')->withErrors('No URL found');
        } catch (UserBlacklistedException $e) {
            return redirect()->route('short_url_index')->withErrors('User is blacklisted');
        }

        return redirect($url->url, 301);
    }

    /**
     * resolves a hash to a url
     * @param Request $request
     * @return array
     * @throws InvalidDataException
     */
    public function resolve(Request $request)
    {
        if (is_null($request->get('hash_name'))) {
            throw new InvalidDataException('hash_name is missing');
        }

        try {
            $url = ShortURL::resolve($request->get('hash_name'));
            $url = $this->_fractalManager->item($url, new ShortURLTransformer());
        } catch (ModelNotFoundException $e) {
            $url = $this->_fractalManager->data('NullResource', [], new ShortURLTransformer());
        }

        $url->addMeta([
            'processed_at' => Carbon::now()
        ]);

        return $url->toArray();
    }

    /**
     * @param Request $request
     * @param int $id
     * @return bool
     */
    public function update(Request $request, int $id)
    {
        $validator = $this->getValidationFactory()->make(
            [
                'url' => ShortUrl::sanitizeURL($request->get('url')),
                'hash_name' => $request->get('hash_name'),
                'user_id' => $request->get('user_id')
            ],
            [
                'url' => 'required|active_url|max:255',
                'hash_name' => 'required|alpha_dash|max:32',
                'user_id' => 'required|integer|exists:users,id'
            ]
        );

        if ($validator->fails()) {
            $this->throwValidationException($request, $validator);
        }

        $url = ShortURL::updateShortURL([
            'id' => $id,
            'url' => ShortURL::sanitizeURL($request->get('url')),
            'hash_name' => $request->get('hash_name'),
            'user_id' => $request->get('user_id'),
        ]);

        return $url;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function create(Request $request)
    {
        $user_id = AUTH_ADMIN_IDS[0];

        $validator = $this->getValidationFactory()->make(
            [
                'url' => ShortUrl::sanitizeURL($request->get('url')),
                'hash_name' => $request->get('hash_name')
            ],
            [
                'url' => 'required|active_url|max:255|unique:short_urls',
                'hash_name' => 'nullable|alpha_dash|max:32|unique:short_urls'
            ]
        );

        if ($validator->fails()) {
            $this->throwValidationException($request, $validator);
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
            'user_id' => $user_id
        ]);

        event(new URLShortened($url));

        $url = $this->_fractalManager->item($url, new ShortURLTransformer());

        $url->addMeta([
            'processed_at' => Carbon::now()
        ]);

        return $url->toArray();
    }

    /**
     * creates a short url and redirects
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function createAndRedirect(Request $request)
    {
        try {
            $url = $this->create($request);
        } catch (HashNameAlreadyAssignedException | URLNotWhitelistedException $e) {
            return redirect()->route('short_url_index')->withErrors($e->getMessage());
        }

        return redirect()->route('short_url_index')->with('hash_name', $url['data'][0]['hash_name']);
    }

    /**
     * Deletes a url and logs it
     * @param int $id
     */
    public function delete(int $id)
    {
        $url = ShortURL::find($id);

        Log::info('URL Deleted', [
            'id' => $url->id,
            'owner' => $url->user()->first()->email,
            'deleted_by' => Auth::user()->email
        ]);

        $url->delete();
    }
}
