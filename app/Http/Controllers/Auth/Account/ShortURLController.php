<?php

namespace App\Http\Controllers\Auth\Account;

use App\Events\URLShortened;
use App\Exceptions\ExpiredException;
use App\Exceptions\HashNameAlreadyAssignedException;
use App\Exceptions\URLNotWhitelistedException;
use App\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\ShortURL\ShortURL;
use Carbon\Carbon;
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
    /**
     * Returns the View which lists all associated ShortURLs
     *
     * @return View
     */
    public function showURLsListView() : View
    {
        Log::info(get_human_readable_name_from_view_function(__FUNCTION__), Auth::user()->getBasicInfoForLog());

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
        Log::info(get_human_readable_name_from_view_function(__FUNCTION__), Auth::user()->getBasicInfoForLog());

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
        self::startExecutionTimer();

        try {
            $url = Auth::user()->shortURLs()->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            Log::notice('User tried to edit unowned ShortURL',
                Auth::user()->getBasicInfoForLog() +
                ['url_id' => $id] +
                self::getExecutionTimeAsAssocArray()
            );

            return redirect()->route('account_urls_list')
                             ->withErrors(__('auth/account/shorturls/edit.no_permission'));
        }

        Log::info('Showing Edit ShortURL View', [
            'url' => (array) $url,
        ]);

        self::endExecutionTimer();

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
        self::startExecutionTimer();

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
            self::endExecutionTimer();
            return redirect()->route('account_urls_add_form')
                             ->withErrors($e->getMessage())
                             ->withInput(Input::all());
        }

        event(new URLShortened($url));

        self::endExecutionTimer();

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
        self::startExecutionTimer();

        $this->validate($request, [
            'id' => 'required|exists:short_urls|int',
        ]);

        try {
            $url = Auth::user()->shortURLs()->findOrFail($request->id);
            Log::notice('URL deleted', [
                'user_id' => Auth::id(),
                'url_id' => $url->id,
                'url' => $url->url,
                'hash_name' => $url->hash_name,
            ]);
            $url->delete();
        } catch (ModelNotFoundException $e) {
            Log::notice('User tried to delete unowned ShortURL', [
                'user_id' => Auth::id(),
                'email' => Auth::user()->email,
                'url_id' => $request->id,
            ]);
            self::endExecutionTimer();

            return back()->withErrors(__('auth/account/shorturls/edit.no_permission'));
        }

        self::endExecutionTimer();

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
        self::startExecutionTimer();

        try {
            Auth::user()->shortURLs()->findOrFail($request->id);
        } catch (ModelNotFoundException $e) {
            Log::notice('User tried to forge ShortURL edit request', [
                'user_id' => Auth::id(),
                'provided_id' => $request->get('user_id'),
                'url_id' => $request->id,
                'url' => $request->get('url'),
                'hash_name' => $request->get('hash_name'),
            ]);
            self::endExecutionTimer();

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
            self::endExecutionTimer();

            return back()->withErrors($e->getMessage())
                         ->withInput(Input::all());
        }

        self::endExecutionTimer();

        return redirect()->route('account_urls_list');
    }

    /**
     * @param $date
     *
     * @throws ExpiredException
     */
    private function checkExpiresDate($date) : void
    {
        self::startExecutionTimer();

        if (!is_null($date)) {
            $expires = Carbon::parse($date);
            if ($expires->lte(Carbon::now())) {
                self::endExecutionTimer();
                throw new ExpiredException('Expires date can\'t be in the past');
            }
        }

        self::endExecutionTimer();
    }
}
