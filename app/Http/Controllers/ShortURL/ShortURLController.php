<?php

namespace App\Http\Controllers\ShortURL;

use App\Exceptions\HashNameAlreadyAssignedException;
use App\Exceptions\URLNotWhitelistedException;
use App\Http\Controllers\Controller;
use App\Models\ShortUrl\ShortUrl;
use App\Models\ShortUrl\ShortUrlWhitelist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ShortURLController extends Controller
{
    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'url' => 'required|active_url|max:255|unique:short_urls',
            'hash_name' => 'nullable|alpha_dash|max:32|unique:short_urls'
        ]);
    }

    public function getIndex()
    {
        return view('shorturl.index');
    }

    public function resolveURL(String $hashName)
    {
        try {
            $this->_checkHashNameInDB($hashName);
        } catch (HashNameAlreadyAssignedException $e) {
            $url = ShortUrl::where('hash_name', '=', $hashName)->first();
            return redirect($url->url, 301);
        }
        return redirect()->route('short_url_index')->withErrors('No URL found');
    }

    public function update(Request $request, int $id)
    {
        $this->_checkURLinWhitelist($request->get('url'));
        $url = ShortUrl::find($id);

        if ($url->hash_name !== $request->get('hash_name')) {
            $this->_checkHashNameInDB($request->get('hash_name'));
        }

        $this->validate($request, [
            'url' => 'required|active_url|max:255',
            'hash_name' => 'required|alpha_dash|max:32',
            'user_id' => 'required|integer|exists:users,id'
        ]);


        $url->url = $request->get('url');
        $url->hash_name = $request->get('hash_name');
        $url->user_id = $request->get('user_id');
        $url->save();

        return $url;
    }

    public function create(Request $request)
    {
        try {
            $this->_validateAndCheckRequest($request);
        } catch (HashNameAlreadyAssignedException | URLNotWhitelistedException $e) {
            return redirect()->route('short_url_index')->withErrors($e->getMessage());
        }

        $hashName = $request->get('hash_name');

        if (is_null($hashName) || empty($hashName)) {
            $hashName = $this->_generateShortURLHash();
        }

        $user_id = Auth::id();
        if (is_null($user_id)) {
            $user_id = AUTH_ADMIN_IDS[0];
        }

        ShortUrl::create([
            'url' => $request->get('url'),
            'hash_name' => $hashName,
            'user_id' => $user_id
        ]);

        //event(new UserRegistered($user, $password));

        return redirect()->route('short_url_index')->with('hash_name', $hashName);
    }

    public function delete(int $id)
    {
        $url = ShortUrl::find($id);
        $url->delete();
    }

    private function _generateShortURLHash() : String
    {
        do {
            $hashName = Str::random(6);
        } while(ShortUrl::where('hash_name', '=', $hashName)->count() > 0);
        return $hashName;
    }

    private function _validateAndCheckRequest(Request $request)
    {
        $this->validator($request->all())->validate();
        $this->_checkURLinWhitelist($request->get('url'));
        $this->_checkHashNameInDB($request->get('hash_name'));

    }

    private function _checkHashNameInDB(String $hashName)
    {
        if (ShortUrl::where('hash_name', '=', $hashName)->count() > 0) {
            throw new HashNameAlreadyAssignedException('Name already assigned');
        }
    }

    private function _checkURLinWhitelist(String $url)
    {
        $url = parse_url($url)['host'];
        if (ShortUrlWhitelist::where('url', '=', $url)->count() !== 1) {
            throw new URLNotWhitelistedException('Url '.$url.' not whitelisted');
        }
    }
}
