<?php

namespace App\Http\Controllers\ShortURL;

use App\Exceptions\HashNameAlreadyAssignedException;
use App\Exceptions\URLNotWhitelistedException;
use App\Http\Controllers\Controller;
use App\Models\ShortURL\ShortURL;
use App\Models\ShortURL\ShortURLWhitelist;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShortURLController extends Controller
{
    public function show()
    {
        return view('shorturl.index')->with('whitelistedURLs', ShortURLWhitelist::all()->sortBy('url'));
    }

    public function resolve(String $hashName)
    {
        try {
            $url = ShortURL::resolve($hashName);
        } catch (ModelNotFoundException $e) {
            return redirect()->route('short_url_index')->withErrors('No URL found');
        }

        if ($url->user()->first()->isBlacklisted()) {
            return redirect()->route('short_url_index')->withErrors('User is blacklisted');
        }

        return redirect($url->url, 301);
    }

    public function update(Request $request, int $id)
    {
        $this->validate($request, [
            'url' => 'required|active_url|max:255',
            'hash_name' => 'required|alpha_dash|max:32',
            'user_id' => 'required|integer|exists:users,id'
        ]);

        return ShortURL::updateShortURL([
            'id' => $id,
            'url' => $request->get('url'),
            'hash_name' => $request->get('hash_name'),
            'user_id' => $request->get('user_id'),
        ]);
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'url' => 'required|active_url|max:255|unique:short_urls',
            'hash_name' => 'nullable|alpha_dash|max:32|unique:short_urls'
        ]);

        $user_id = Auth::id();
        if (is_null($user_id)) {
            $user_id = AUTH_ADMIN_IDS[0];
        }

        try {
            $url = ShortURL::createShortURL([
                'url' => $request->get('url'),
                'hash_name' => $request->get('hash_name'),
                'user_id' => $user_id
            ]);
        } catch (HashNameAlreadyAssignedException | URLNotWhitelistedException $e) {
            return redirect()->route('short_url_index')->withErrors($e->getMessage());
        }

        //event(new UserRegistered($user, $password));

        return redirect()->route('short_url_index')->with('hash_name', $url->hash_name);
    }

    public function delete(int $id)
    {
        ShortURL::find($id)->delete();
    }


}
