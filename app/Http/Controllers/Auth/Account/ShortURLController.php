<?php

namespace App\Http\Controllers\Auth\Account;

use App\Events\URLShortened;
use App\Exceptions\ExpiredException;
use App\Exceptions\HashNameAlreadyAssignedException;
use App\Exceptions\URLNotWhitelistedException;
use App\Http\Controllers\Controller;
use App\Models\ShortURL\ShortURL;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

/**
 * Class ShortURLController
 * @package App\Http\Controllers\Auth\Account
 */
class ShortURLController extends Controller
{
    /**
     * Returns the View which lists all associated ShortURLs
     *
     * @return View
     */
    public function showURLsListView() : View
    {
        $user = Auth::user();
        Log::debug('User requested Show URL List View', [
            'method' => __METHOD__,
            'user_id' => Auth::id(),
        ]);

        return view('auth.account.shorturls.index')->with(
            'urls',
            $user->shortURLs()->get()
        );
    }

    /**
     * Returns the add short url view
     *
     * @return View
     */
    public function showAddURLView() : View
    {
        $user = Auth::user();
        Log::debug('User requested Add Url View', [
            'method' => __METHOD__,
            'user_id' => $user->id,
        ]);

        return view('auth.account.shorturls.add')->with(
            'user',
            $user
        );
    }

    /**
     * Returns the Edit ShortURL View
     *
     * @param int $id The ShortURL ID to edit
     *
     * @return View | RedirectResponse
     */
    public function showEditURLView(int $id)
    {
        try {
            $url = Auth::user()->shortURLs()->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            Log::info('User tried to edit unowned ShortURL', [
                'user_id' => Auth::id(),
                'email' => Auth::user()->email,
                'url_id' => $id,
            ]);

            return redirect()->route('account_urls_list');
        }

        Log::debug('User requested Edit ShortURL View', [
            'method' => __METHOD__,
            'url' => (array) $url,
        ]);

        return view('auth.account.shorturls.edit')->with(
            'url',
            $url
        );
    }

    /**
     * Validates the url add Request and creates a new ShortURL
     *
     * @param Request $request The Request
     *
     * @return RedirectResponse|Redirect
     *
     * @throws ExpiredException
     */
    public function addURL(Request $request)
    {
        $data = [
            'url' => ShortURL::sanitizeURL($request->get('url')),
            'hash_name' => $request->get('hash_name'),
            'expires' => $request->get('expires'),
        ];

        $rules = [
            'url' => 'required|url|max:255|unique:short_urls',
            'hash_name' => 'nullable|alpha_dash|max:32|unique:short_urls',
            'expires' => 'nullable|date',
        ];

        validate_array($data, $rules, $request);

        $expires = $request->get('expires');
        try {
            if (!is_null($request->get('expires'))) {
                $expires = Carbon::parse($request->get('expires'));
                if ($expires->lte(Carbon::now())) {
                    throw new ExpiredException('Expires date can\'t be in the past');
                }
            }

            $url = ShortURL::createShortURL([
                'url' => ShortURL::sanitizeURL($request->get('url')),
                'hash_name' => $request->get('hash_name'),
                'user_id' => Auth::id(),
                'expires' => $expires,
            ]);
        } catch (HashNameAlreadyAssignedException | URLNotWhitelistedException | ExpiredException $e) {
            return redirect()->route('account_urls_add_form')
                ->withErrors($e->getMessage())
                ->withInput(Input::all());
        }

        event(new URLShortened($url));

        return redirect()->route('account_urls_list')->with(
            'hash_name',
            $url->hash_name
        );
    }

    /**
     * Deletes a Users ShortURL
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function deleteURL(Request $request) : RedirectResponse
    {
        $this->validate($request, [
            'id' => 'required|exists:short_urls|int',
        ]);

        try {
            $url = Auth::user()->shortURLs()->findOrFail($request->id);
            Log::info('URL deleted', [
                'user_id' => Auth::id(),
                'url_id' => $url->id,
                'url' => $url->url,
                'hash_name' => $url->hash_name,
            ]);
            $url->delete();
        } catch (ModelNotFoundException $e) {
            Log::info('User tried to delete unowned ShortURL', [
                'user_id' => Auth::id(),
                'email' => Auth::user()->email,
                'url_id' => $request->id,
            ]);
        }

        return back();
    }

    /**
     * Updates a ShortURL by its ID
     *
     * @param Request $request The Update Request
     *
     * @return RedirectResponse
     */
    public function updateURL(Request $request) : RedirectResponse
    {
        if (Auth::user()->shortURLs()->find($request->id)->count() === 0) {
            Log::warning('User tried to forge ShortURL edit request', [
                'user_id' => Auth::id(),
                'provided_id' => $request->get('user_id'),
                'url_id' => $request->id,
                'url' => $request->get('url'),
                'hash_name' => $request->get('hash_name'),
            ]);

            return abort(401, 'No permission');
        }

        $data = [
            'url' => ShortURL::sanitizeURL($request->get('url')),
            'hash_name' => $request->get('hash_name'),
            'expires' => $request->get('expires'),
        ];

        $rules = [
            'url' => 'required|url|max:255',
            'hash_name' => 'required|alpha_dash|max:32',
            'expires' => 'nullable|date',
        ];

        validate_array($data, $rules, $request);

        try {
            ShortURL::updateShortURL([
                'id' => $request->id,
                'url' => ShortURL::sanitizeURL($request->get('url')),
                'hash_name' => $request->get('hash_name'),
                'user_id' => Auth::id(),
                'expires' => $request->get('expires'),
            ]);
        } catch (URLNotWhitelistedException | HashNameAlreadyAssignedException $e) {
            return back()->withErrors($e->getMessage())->withInput(Input::all());
        }

        return redirect()->route('account_urls_list');
    }
}
