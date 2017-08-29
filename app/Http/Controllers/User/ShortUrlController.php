<?php declare(strict_types = 1);

namespace App\Http\Controllers\User;

use App\Events\UrlShortened;
use App\Exceptions\ExpiredException;
use App\Exceptions\HashNameAlreadyAssignedException;
use App\Exceptions\UrlNotWhitelistedException;
use App\Http\Controllers\Controller;
use App\Models\ShortUrl\ShortUrl;
use Carbon\Carbon;
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

        return view('user.shorturls.index')->with(
            'urls',
            Auth::user()->shortUrls()->simplePaginate(100)
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

        return view('user.shorturls.add')->with(
            'user',
            Auth::user()
        );
    }

    /**
     * Returns the Edit ShortUrl View
     *
     * @param int $id The ShortUrl ID to edit
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function showEditUrlView(int $id): View
    {
        app('Log')::info(make_name_readable(__FUNCTION__), ['id' => $id]);

        try {
            $url = Auth::user()->shortUrls()->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            app('Log')::notice("User tried to edit unowned ShortUrl with ID: {$id}");

            abort(403);
        }

        return view('user.shorturls.edit')->with(
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
        $data = [
            'url'        => ShortUrl::sanitizeUrl($request->get('url')),
            'hash'       => $request->get('hash'),
            'expired_at' => $request->get('expired_at'),
        ];

        $rules = [
            'url'        => 'required|url|max:255|unique:short_urls',
            'hash'       => 'nullable|alpha_dash|max:32|unique:short_urls',
            'expired_at' => 'nullable|date',
        ];

        validate_array($data, $rules, $request);

        $data['user_id'] = Auth::id();
        if (!is_null($data['expired_at'])) {
            $data['expired_at'] = Carbon::parse($data['expired_at']);
        }

        $expiredAt = $request->get('expired_at');
        try {
            ShortUrl::checkIfDateIsPast($expiredAt);
            $url = ShortUrl::createShortUrl($data);
        } catch (HashNameAlreadyAssignedException | UrlNotWhitelistedException | ExpiredException $e) {
            return redirect()->route('account_urls_add_form')->withErrors($e->getMessage())->withInput(Input::all());
        }
        event(new UrlShortened($url));

        return redirect()->route('account_urls_list')->with(
            'hash',
            $url->hash
        );
    }

    /**
     * Updates a ShortUrl by its ID
     *
     * @param \Illuminate\Http\Request $request The Update Request
     *
     * @param int                      $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateUrl(Request $request, int $id): RedirectResponse
    {
        if ($request->exists('delete')) {
            return $this->deleteUrl($request, $id);
        }

        $data = [
            'url'        => ShortUrl::sanitizeUrl($request->get('url')),
            'hash'       => $request->get('hash'),
            'expired_at' => $request->get('expired_at'),
        ];

        $rules = [
            'url'        => 'required|url|max:255',
            'hash'       => 'required|alpha_dash|max:32',
            'expired_at' => 'nullable|date',
        ];

        validate_array($data, $rules, $request);

        $data['id'] = $id;
        $data['user_id'] = Auth::id();
        if (!is_null($data['expired_at'])) {
            $data['expired_at'] = Carbon::parse($data['expired_at']);
        }

        try {
            Auth::user()->shortUrls()->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            app('Log')::notice(
                'User tried to forge ShortUrl edit request',
                [
                    'provided_id' => $request->get('user_id'),
                    'url_id'      => $id,
                    'url'         => $request->get('url'),
                    'hash'        => $request->get('hash'),
                ]
            );

            abort(403);
        }

        try {
            ShortUrl::updateShortUrl($data);
        } catch (UrlNotWhitelistedException | HashNameAlreadyAssignedException $e) {
            return back()->withErrors($e->getMessage())->withInput(Input::all());
        }

        return redirect()->route('account_urls_list')->with('message', __('crud.updated', ['type' => 'ShortUrl']));
    }

    /**
     * Deletes a Users ShortUrl
     *
     * @param \Illuminate\Http\Request $request
     *
     * @param int                      $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteUrl(Request $request, int $id): RedirectResponse
    {
        try {
            $url = Auth::user()->shortUrls()->findOrFail($id);
            app('Log')::notice(
                'URL deleted',
                [
                    'user_id' => Auth::id(),
                    'url_id'  => $url->id,
                    'url'     => $url->url,
                    'hash'    => $url->hash,
                ]
            );
            $url->delete();
        } catch (ModelNotFoundException $e) {
            app('Log')::notice("User tried to delete unowned ShortUrl with ID: {$id}");
            abort(403);
        }

        return back()->with('message', __('crud.deleted', ['type' => 'ShortUrl']));
    }
}
