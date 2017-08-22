<?php declare(strict_types = 1);

namespace App\Http\Controllers\User;

use App\Events\UrlShortened;
use App\Exceptions\ExpiredException;
use App\Exceptions\HashNameAlreadyAssignedException;
use App\Exceptions\UrlNotWhitelistedException;
use App\Http\Controllers\Controller;
use App\Models\ShortUrl\ShortUrl;
use App\Traits\ProfilesMethodsTrait;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;

/**
 * Class ShortUrlController
 *
 * @package App\Http\Controllers\User
 */
class ShortUrlController extends Controller
{
    use ProfilesMethodsTrait;

    /**
     * ShortUrlController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
    }

    /**
     * Returns the View which lists all associated ShortUrls
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function showUrlsListView(): View
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return view('api.auth.account.shorturls.index')->with(
            'urls',
            Auth::user()->shortUrls()->get()
        );
    }

    /**
     * Returns the add short url view
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function showAddUrlView(): View
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return view('api.auth.account.shorturls.add')->with(
            'user',
            Auth::user()
        );
    }

    /**
     * Returns the Edit ShortUrl View
     *
     * @param int $id The ShortUrl ID to edit
     *
     * @return \Illuminate\Contracts\View\View | \Illuminate\Http\RedirectResponse
     */
    public function showEditUrlView(int $id)
    {
        $this->startProfiling(__FUNCTION__);

        app('Log')::info(make_name_readable(__FUNCTION__), ['id' => $id]);

        try {
            $this->addTrace("Getting ShortUrl with ID: {$id}", __FUNCTION__, __LINE__);
            $url = Auth::user()->shortUrls()->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            app('Log')::notice("User tried to edit unowned ShortUrl with ID: {$id}");
            $this->addTrace("ShortUrl with ID: {$id} not found", __FUNCTION__, __LINE__);
            $this->stopProfiling(__FUNCTION__);

            return redirect()->route('account_urls_list')->withErrors(__('auth/account/shorturls/edit.no_permission'));
        }

        $this->stopProfiling(__FUNCTION__);

        return view('api.auth.account.shorturls.edit')->with(
            'url',
            $url
        );
    }

    /**
     * Validates the url add Request and creates a new ShortUrl
     *
     * @param \Illuminate\Http\Request $request The Request
     *
     * @return \Illuminate\Http\RedirectResponse | \Illuminate\Routing\Redirector
     *
     * @throws \App\Exceptions\ExpiredException
     */
    public function addUrl(Request $request)
    {
        $this->startProfiling(__FUNCTION__);

        $data = [
            'url'       => ShortUrl::sanitizeUrl($request->get('url')),
            'hash' => $request->get('hash'),
            'expired_at'   => $request->get('expired_at'),
        ];

        $rules = [
            'url'       => 'required|url|max:255|unique:short_urls',
            'hash' => 'nullable|alpha_dash|max:32|unique:short_urls',
            'expired_at'   => 'nullable|date',
        ];

        validate_array($data, $rules, $request);

        $expired_at = $request->get('expired_at');
        try {
            $this->addTrace("Checking if date {$expired_at} is in the past", __FUNCTION__, __LINE__);
            ShortUrl::checkIfDateIsPast($expired_at);

            $this->addTrace('Creating ShortUrl', __FUNCTION__, __LINE__);
            $url = ShortUrl::createShortUrl(
                [
                    'url'       => ShortUrl::sanitizeUrl($request->get('url')),
                    'hash' => $request->get('hash'),
                    'user_id'   => Auth::id(),
                    'expired_at'   => $expired_at,
                ]
            );
        } catch (HashNameAlreadyAssignedException | UrlNotWhitelistedException | ExpiredException $e) {
            $this->addTrace(get_class($e), __FUNCTION__, __LINE__);

            return redirect()->route('account_urls_add_form')->withErrors($e->getMessage())->withInput(Input::all());
        }
        event(new UrlShortened($url));

        $this->stopProfiling(__FUNCTION__);

        return redirect()->route('account_urls_list')->with(
            'hash',
            $url->hash
        );
    }

    /**
     * Deletes a Users ShortUrl
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteUrl(Request $request): RedirectResponse
    {
        $this->startProfiling(__FUNCTION__);

        $this->validate(
            $request,
            [
                'id' => 'required|exists:short_urls|int',
            ]
        );

        try {
            $this->addTrace("Getting ShortUrl with ID: {$request->id}", __FUNCTION__, __LINE__);
            $url = Auth::user()->shortUrls()->findOrFail($request->id);
            app('Log')::notice(
                'URL deleted',
                [
                    'user_id'   => Auth::id(),
                    'url_id'    => $url->id,
                    'url'       => $url->url,
                    'hash' => $url->hash,
                ]
            );
            $url->delete();
        } catch (ModelNotFoundException $e) {
            app('Log')::notice("User tried to delete unowned ShortUrl with ID: {$request->id}");
            $this->stopProfiling(__FUNCTION__);

            return back()->withErrors(__('auth/account/shorturls/edit.no_permission'));
        }

        $this->stopProfiling(__FUNCTION__);

        return back();
    }

    /**
     * Updates a ShortUrl by its ID
     *
     * @param \Illuminate\Http\Request $request The Update Request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateUrl(Request $request): RedirectResponse
    {
        $this->startProfiling(__FUNCTION__);

        try {
            $this->addTrace("Getting ShortUrl with ID: {$request->id}", __FUNCTION__, __LINE__);
            Auth::user()->shortUrls()->findOrFail($request->id);
        } catch (ModelNotFoundException $e) {
            app('Log')::notice(
                'User tried to forge ShortUrl edit request',
                [
                    'provided_id' => $request->get('user_id'),
                    'url_id'      => $request->id,
                    'url'         => $request->get('url'),
                    'hash'   => $request->get('hash'),
                ]
            );

            $this->stopProfiling(__FUNCTION__);

            return back()->withErrors(__('auth/account/shorturls/edit.no_permission'));
        }

        $data = [
            'url'       => ShortUrl::sanitizeUrl($request->get('url')),
            'hash' => $request->get('hash'),
            'expired_at'   => $request->get('expired_at'),
        ];

        $rules = [
            'url'       => 'required|url|max:255',
            'hash' => 'required|alpha_dash|max:32',
            'expired_at'   => 'nullable|date',
        ];

        validate_array($data, $rules, $request);

        try {
            $this->addTrace("Starting to update ShortUrl", __FUNCTION__, __LINE__);
            ShortUrl::updateShortUrl(
                [
                    'id'        => $request->id,
                    'url'       => ShortUrl::sanitizeUrl($request->get('url')),
                    'hash' => $request->get('hash'),
                    'user_id'   => Auth::id(),
                    'expired_at'   => $request->get('expired_at'),
                ]
            );
        } catch (UrlNotWhitelistedException | HashNameAlreadyAssignedException $e) {
            $this->addTrace(get_class($e), __FUNCTION__, __LINE__);
            $this->stopProfiling(__FUNCTION__);

            return back()->withErrors($e->getMessage())->withInput(Input::all());
        }

        $this->stopProfiling(__FUNCTION__);

        return redirect()->route('account_urls_list');
    }
}
