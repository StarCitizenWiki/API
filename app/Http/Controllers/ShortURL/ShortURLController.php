<?php

namespace App\Http\Controllers\ShortURL;

use App\Exceptions\HashNameAlreadyAssignedException;
use App\Http\Controllers\Controller;
use App\ShortUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use phpDocumentor\Reflection\Types\String_;

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
            'name' => 'nullable|alpha_dash|max:32|unique:short_urls'
        ]);
    }

    public function getIndex()
    {
        return view('shorturl.index');
    }

    public function shortenURL(Request $request)
    {
        $this->validator($request->all())->validate();
        $hashName = $request->get('hash-name');

        try {
            $this->_checkCustomHashNameInDB($request->get('hash-name'));
        } catch (HashNameAlreadyAssignedException $e) {
            return redirect()->route('short_url_index')->withErrors('Name already assigned');
        }

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

        return redirect()->route('short_url_index')->with('hash-name', $hashName);
    }

    private function _generateShortURLHash() : String
    {
        do {
            $hashName = Str::random(6);
        } while(ShortUrl::where('hash_name', '=', $hashName)->count() > 0);
        return $hashName;
    }

    private function _checkCustomHashNameInDB(String $hashName)
    {
        if (ShortUrl::where('hash_name', '=', $hashName)->count() > 0) {
            throw new HashNameAlreadyAssignedException();
        }
    }
}
