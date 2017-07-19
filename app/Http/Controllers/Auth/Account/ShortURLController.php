<?php

namespace App\Http\Controllers\Auth\Account;

use App\Events\URLShortened;
use App\Exceptions\ExpiredException;
use App\Exceptions\HashNameAlreadyAssignedException;
use App\Exceptions\URLNotWhitelistedException;
use App\Http\Controllers\Controller;
use App\Models\ShortURL\ShortURL;
use App\Traits\ProfilesMethodsTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

/**
 * Class ShortURLController
 * @package App\Http\Controllers\Auth\Account
 */
class ShortURLController extends Controller
{
    use ProfilesMethodsTrait;

    /**
     * ShortURLController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
    }

    /**
     * Returns the View which lists all associated ShortURLs
     *
     * @return View
     */
    public function showURLsListView() : View
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return view('auth.account.shorturls.index')->with(
            'urls',
            Auth::user()->shortURLs()->get()
        );
    }

    /**
     * Returns the add short url view
     *
     * @return View
     */
    public function showAddURLView() : View
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return view('auth.account.shorturls.add')->with(
            'user',
            Auth::user()
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
        $this->startProfiling(__FUNCTION__);

        app('Log')::info(make_name_readable(__FUNCTION__), ['id' => $id]);

        try {
            $this->addTrace("Getting ShortURL with ID: {$id}", __FUNCTION__, __LINE__);
            $url = Auth::user()->shortURLs()->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            app('Log')::notice("User tried to edit unowned ShortURL with ID: {$id}");
            $this->addTrace("ShortURL with ID: {$id} not found", __FUNCTION__, __LINE__);
            $this->stopProfiling(__FUNCTION__);

            return redirect()->route('account_urls_list')
                             ->withErrors(__('auth/account/shorturls/edit.no_permission'));
        }

        $this->stopProfiling(__FUNCTION__);

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
        $this->startProfiling(__FUNCTION__);

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
            $this->addTrace("Checking if date {$expires} is in the past", __FUNCTION__, __LINE__);
            ShortURL::checkIfDateIsPast($expires);

            $this->addTrace("Creating ShortURL", __FUNCTION__, __LINE__);
            $url = ShortURL::createShortURL([
                'url' => ShortURL::sanitizeURL($request->get('url')),
                'hash_name' => $request->get('hash_name'),
                'user_id' => Auth::id(),
                'expires' => $expires,
            ]);
        } catch (HashNameAlreadyAssignedException | URLNotWhitelistedException | ExpiredException $e) {
            $this->addTrace(get_class($e), __FUNCTION__, __LINE__);

            return redirect()->route('account_urls_add_form')
                             ->withErrors($e->getMessage())
                             ->withInput(Input::all());
        }
        event(new URLShortened($url));

        $this->stopProfiling(__FUNCTION__);

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
        $this->startProfiling(__FUNCTION__);

        $this->validate($request, [
            'id' => 'required|exists:short_urls|int',
        ]);

        try {
            $this->addTrace("Getting ShortURL with ID: {$request->id}", __FUNCTION__, __LINE__);
            $url = Auth::user()->shortURLs()->findOrFail($request->id);
            app('Log')::notice('URL deleted', [
                'user_id' => Auth::id(),
                'url_id' => $url->id,
                'url' => $url->url,
                'hash_name' => $url->hash_name,
            ]);
            $url->delete();
        } catch (ModelNotFoundException $e) {
            app('Log')::notice("User tried to delete unowned ShortURL with ID: {$request->id}");
            $this->stopProfiling(__FUNCTION__);

            return back()->withErrors(__('auth/account/shorturls/edit.no_permission'));
        }

        $this->stopProfiling(__FUNCTION__);

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
        $this->startProfiling(__FUNCTION__);

        try {
            $this->addTrace("Getting ShortURL with ID: {$request->id}", __FUNCTION__, __LINE__);
            Auth::user()->shortURLs()->findOrFail($request->id);
        } catch (ModelNotFoundException $e) {
            app('Log')::notice('User tried to forge ShortURL edit request', [
                'provided_id' => $request->get('user_id'),
                'url_id' => $request->id,
                'url' => $request->get('url'),
                'hash_name' => $request->get('hash_name'),
            ]);

            $this->stopProfiling(__FUNCTION__);

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
            $this->addTrace("Starting to update ShortURL", __FUNCTION__, __LINE__);
            ShortURL::updateShortURL([
                'id' => $request->id,
                'url' => ShortURL::sanitizeURL($request->get('url')),
                'hash_name' => $request->get('hash_name'),
                'user_id' => Auth::id(),
                'expires' => $request->get('expires'),
            ]);
        } catch (URLNotWhitelistedException | HashNameAlreadyAssignedException $e) {
            $this->addTrace(get_class($e), __FUNCTION__, __LINE__);
            $this->stopProfiling(__FUNCTION__);

            return back()->withErrors($e->getMessage())
                         ->withInput(Input::all());
        }

        $this->stopProfiling(__FUNCTION__);

        return redirect()->route('account_urls_list');
    }
}
