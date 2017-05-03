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
        $this->logger::debug('User requested Show URL List View', [
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
        $this->logger::debug('User requested Add Url View', [
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
            $this->logger::notice('User tried to edit unowned ShortURL', [
                'user_id' => Auth::id(),
                'email' => Auth::user()->email,
                'url_id' => $id,
            ]);

            return redirect()->route('account_urls_list')
                             ->withErrors(__('auth/account/shorturls/edit.no_permission'));
        }

        $this->logger::debug('User requested Edit ShortURL View', [
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
            $this->checkExpiresDate($expires);

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
            $this->logger::notice('URL deleted', [
                'user_id' => Auth::id(),
                'url_id' => $url->id,
                'url' => $url->url,
                'hash_name' => $url->hash_name,
            ]);
            $url->delete();
        } catch (ModelNotFoundException $e) {
            $this->logger::notice('User tried to delete unowned ShortURL', [
                'user_id' => Auth::id(),
                'email' => Auth::user()->email,
                'url_id' => $request->id,
            ]);

            return back()->withErrors(__('auth/account/shorturls/edit.no_permission'));
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
        try {
            Auth::user()->shortURLs()->findOrFail($request->id);
        } catch (ModelNotFoundException $e) {
            $this->logger::notice('User tried to forge ShortURL edit request', [
                'user_id' => Auth::id(),
                'provided_id' => $request->get('user_id'),
                'url_id' => $request->id,
                'url' => $request->get('url'),
                'hash_name' => $request->get('hash_name'),
            ]);

            return back()->withErrors(__('auth/account/shorturls/edit.no_permission'));
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
            return back()->withErrors($e->getMessage())
                         ->withInput(Input::all());
        }

        return redirect()->route('account_urls_list');
    }

    /**
     * @param $date
     *
     * @throws ExpiredException
     */
    private function checkExpiresDate($date) : void
    {
        if (!is_null($date)) {
            $expires = Carbon::parse($date);
            if ($expires->lte(Carbon::now())) {
                throw new ExpiredException('Expires date can\'t be in the past');
            }
        }
    }
}
