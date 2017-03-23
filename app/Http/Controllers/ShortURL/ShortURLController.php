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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Spatie\Fractal\Fractal;

class ShortURLController extends Controller
{
    use TransformesData;

    public function __construct()
    {
        $this->_transformer = new ShortURLTransformer();
    }

    /**
     * @return View
     */
    public function showShortURLView()
    {
        return view('shorturl.index')->with('whitelistedURLs', ShortURLWhitelist::all()->sortBy('url')->where('internal', false));
    }

    public function showResolveView()
    {
        return view('shorturl.resolve');
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

    public function resolveAndReturn(Request $request)
    {
        $this->validate($request, [
            'url' => 'required|url',
        ]);

        $url = $request->get('url');
        $url = parse_url($url);

        if (!isset($url['host']) ||
            ($url['host'] != config('app.shorturl_url')) ||
            !isset($url['path']))
        {
            return redirect()->route('short_url_resolve')->withErrors('Invalid Short URL');
        }

        $path = str_replace('/', '', $url['path']);

        try {
            $url = ShortURL::resolve($path);
        } catch (ModelNotFoundException $e) {
            return redirect()->route('short_url_resolve_form')->withErrors('No URL found');
        } catch (UserBlacklistedException $e) {
            return redirect()->route('short_url_index')->withErrors('User is blacklisted, can\'t resolve URL');
        }

        return redirect()->route('short_url_resolve')->with('url', $url->url);
    }

    /**
     * resolves a hash to a url
     * @param Request $request
     * @return array
     * @throws InvalidDataException
     */
    public function resolve(Request $request)
    {
        $this->validate($request, [
            'hash_name' => 'required|alpha_dash',
        ]);

        try {
            $url = ShortURL::resolve($request->get('hash_name'));
            $this->item();
        } catch (ModelNotFoundException $e) {
            $url = [];
        }

        return $this->transform($url)->asArray();
    }

    /**
     * @param Request $request
     * @param int $id
     * @return bool
     */
    public function update(Request $request, int $id)
    {
        $data = [
            'url' => ShortUrl::sanitizeURL($request->get('url')),
            'hash_name' => $request->get('hash_name'),
            'user_id' => $request->get('user_id')
        ];

        $rules = [
            'url' => 'required|active_url|max:255',
            'hash_name' => 'required|alpha_dash|max:32',
            'user_id' => 'required|integer|exists:users,id'
        ];

        $this->_validateArray($data, $rules, $request);

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

        $data = [
            'url' => ShortUrl::sanitizeURL($request->get('url')),
            'hash_name' => $request->get('hash_name')
        ];

        $rules = [
            'url' => 'required|active_url|max:255|unique:short_urls',
            'hash_name' => 'nullable|alpha_dash|max:32|unique:short_urls'
        ];

        $this->_validateArray($data, $rules, $request);

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

        return $this->transform($url)->asArray();
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

    /**
     * Adds processed_at meta field
     */
    protected function _addMetadataToTransformation()
    {
        $this->_transformedResource->addMeta([
            'processed_at' => Carbon::now()
        ]);
    }

    /**
     * Validates an array with given rules
     * @param array $data
     * @param array $rules
     * @param Request $request
     * @throws ValidationException
     */
    private function _validateArray(array $data, array $rules, Request $request)
    {
        Log::debug('['.__CLASS__.'] Validated data', [
            'data' => $data,
            'rules' => $rules
        ]);

        $validator = $this->getValidationFactory()->make($data, $rules);

        if ($validator->fails()) {
            $this->throwValidationException($request, $validator);
        }

    }
}
