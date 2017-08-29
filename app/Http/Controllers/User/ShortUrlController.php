<?php declare(strict_types = 1);

namespace App\Http\Controllers\User;

use App\Events\UrlShortened;
use App\Exceptions\ExpiredException;
use App\Exceptions\HashNameAlreadyAssignedException;
use App\Exceptions\UrlNotWhitelistedException;
use App\Http\Controllers\Controller;
use App\Models\ShortUrl\ShortUrl;
use App\Traits\ProfilesMethodsTrait;
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
        $this->startProfiling(__FUNCTION__);
        app('Log')::info(make_name_readable(__FUNCTION__), ['id' => $id]);

        try {
            $this->addTrace("Getting ShortUrl with ID: {$id}", __FUNCTION__, __LINE__);
            $url = Auth::user()->shortUrls()->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            app('Log')::notice("User tried to edit unowned ShortUrl with ID: {$id}");
            $this->addTrace("ShortUrl with ID: {$id} not found", __FUNCTION__, __LINE__);
            $this->stopProfiling(__FUNCTION__);

            abort(403);
        }
        $this->stopProfiling(__FUNCTION__);

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
        $this->startProfiling(__FUNCTION__);

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
            $this->addTrace("Checking if date {$expiredAt} is in the past", __FUNCTION__, __LINE__);
            ShortUrl::checkIfDateIsPast($expiredAt);

            $this->addTrace('Creating ShortUrl', __FUNCTION__, __LINE__);
            $url = ShortUrl::createShortUrl($data);
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

        $this->startProfiling(__FUNCTION__);

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
            $this->addTrace("Getting ShortUrl with ID: {$id}", __FUNCTION__, __LINE__);
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

            $this->stopProfiling(__FUNCTION__);

            abort(403);
        }

        try {
            $this->addTrace("Starting to update ShortUrl", __FUNCTION__, __LINE__);
            ShortUrl::updateShortUrl($data);
        } catch (UrlNotWhitelistedException | HashNameAlreadyAssignedException $e) {
            $this->addTrace(get_class($e), __FUNCTION__, __LINE__);
            $this->stopProfiling(__FUNCTION__);

            return back()->withErrors($e->getMessage())->withInput(Input::all());
        }

        $this->stopProfiling(__FUNCTION__);

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
        $this->startProfiling(__FUNCTION__);

        try {
            $this->addTrace("Getting ShortUrl with ID: {$id}", __FUNCTION__, __LINE__);
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
            $this->stopProfiling(__FUNCTION__);

            abort(403);
        }

        $this->stopProfiling(__FUNCTION__);

        return back()->with('message', __('crud.deleted', ['type' => 'ShortUrl']));
    }
}
