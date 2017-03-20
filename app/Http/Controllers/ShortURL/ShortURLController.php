<?php

namespace App\Http\Controllers\ShortURL;

use App\Events\URLShortened;
use App\Exceptions\HashNameAlreadyAssignedException;
use App\Exceptions\URLNotWhitelistedException;
use App\Http\Controllers\Controller;
use App\Models\ShortURL\ShortURL;
use App\Models\ShortURL\ShortURLWhitelist;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ShortURLController extends Controller
{
    /**
     * @return View
     */
    public function show()
    {
        return view('shorturl.index')->with('whitelistedURLs', ShortURLWhitelist::all()->sortBy('url')->where('internal', false));
    }

    /**
     * resolves a hash to a url
     * @param String $hashName
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function resolveWeb(Request $request, String $hashName)
    {
        try {
            $url = $this->resolve($request);
        } catch (ModelNotFoundException $e) {
            return redirect()->route('short_url_index')->withErrors('No URL found');
        }

        if ($url->user()->first()->isBlacklisted()) {
            return redirect()->route('short_url_index')->withErrors('User is blacklisted');
        }

        return redirect($url->url, 301);
    }

    /**
     * resolves a hash to a url
     * @return ShortURL
     */
    public function resolve(Request $request)
    {
        $url = ShortURL::resolve($request->get('hash_name'));

        return $url;
    }

    /**
     * @param Request $request
     * @param int $id
     * @return bool
     */
    public function update(Request $request, int $id)
    {
        $this->validate($request, [
            'url' => 'required|active_url|max:255',
            'hash_name' => 'required|alpha_dash|max:32',
            'user_id' => 'required|integer|exists:users,id'
        ]);

        $url = ShortURL::updateShortURL([
            'id' => $id,
            'url' => $request->get('url'),
            'hash_name' => $request->get('hash_name'),
            'user_id' => $request->get('user_id'),
        ]);

        return $url;
    }

    /**
     * @param Request $request
     * @return ShortURL
     */
    public function create(Request $request)
    {
        $user_id = AUTH_ADMIN_IDS[0];

        $this->validate($request, [
            'url' => 'required|active_url|max:255|unique:short_urls',
            'hash_name' => 'nullable|alpha_dash|max:32|unique:short_urls'
        ]);

        $key = $request->get(AUTH_KEY_FIELD_NAME, null);

        if (!is_null($key)) {
            $user = User::where('api_token', $key)->first();
            if (!is_null($user)) {
                $user_id = $user->id;
            }
        }

        $url = ShortURL::createShortURL([
            'url' => $request->get('url'),
            'hash_name' => $request->get('hash_name'),
            'user_id' => $user_id
        ]);

        event(new URLShortened($url));

        return $url;
    }

    public function createWeb(Request $request)
    {
        try {
            $url = $this->create($request);
        } catch (HashNameAlreadyAssignedException | URLNotWhitelistedException $e) {
            return redirect()->route('short_url_index')->withErrors($e->getMessage());
        }

        return redirect()->route('short_url_index')->with('hash_name', $url->hash_name);
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
