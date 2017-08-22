<?php declare(strict_types = 1);

namespace App\Http\Controllers\Admin;

use App\Exceptions\HashNameAlreadyAssignedException;
use App\Exceptions\UrlNotWhitelistedException;
use App\Http\Controllers\Controller;
use App\Models\ShortUrl\ShortUrl;
use App\Models\ShortUrl\ShortUrlWhitelist;
use App\Models\User;
use App\Traits\ProfilesMethodsTrait;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;

/**
 * Class AdminShortUrlController
 * @package App\Http\Controllers\Admin
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
        $this->middleware('admin');
    }

    /**
     * Returns the ShortUrl List View
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function showUrlsListView(): View
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return view('admin.shorturls.index')->with(
            'urls',
            ShortUrl::withTrashed()->simplePaginate(100)
        );
    }

    /**
     * Returns the ShortUrl List View
     *
     * @param int $id UserID
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function showUrlsListForUserView(int $id): View
    {
        app('Log')::info(make_name_readable(__FUNCTION__), ['id' => $id]);

        return view('admin.shorturls.index')->with(
            'urls',
            User::find($id)->shortUrls()->simplePaginate(100)
        );
    }

    /**
     * Returns the ShortUrl Whitelist View
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function showUrlWhitelistView(): View
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return view('admin.shorturls.whitelists.index')->with(
            'urls',
            ShortUrlWhitelist::query()->simplePaginate(100)
        );
    }

    /**
     * Returns the View to add a ShortUrl Whitelist URL
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function showAddUrlWhitelistView(): View
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return view('admin.shorturls.whitelists.add');
    }

    /**
     * Returns the View to edit a ShortUrl
     *
     * @param int $id The ShortUrl ID
     *
     * @return \Illuminate\Contracts\View\View | RedirectResponse
     */
    public function showEditUrlView(int $id)
    {
        $this->startProfiling(__FUNCTION__);

        app('Log')::info(make_name_readable(__FUNCTION__), ['id' => $id]);

        try {
            $this->addTrace("Getting ShortUrl for ID: {$id}", __FUNCTION__, __LINE__);
            $url = ShortUrl::findOrFail($id);
            $this->stopProfiling(__FUNCTION__);

            return view('admin.shorturls.edit')->with('url', $url)->with('users', User::all());
        } catch (ModelNotFoundException $e) {
            app('Log')::warning("URL with ID: {$id} not found");
        }

        $this->stopProfiling(__FUNCTION__);

        return redirect()->route('admin_urls_list');
    }

    /**
     * Deletes a ShortUrl by ID
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

        $url = ShortUrl::findOrFail($request->id);
        app('Log')::notice(
            'URL deleted',
            [
                'deleted_by' => Auth::id(),
                'url_id'     => $url->id,
                'url'        => $url->url,
                'hash_name'  => $url->hash_name,
            ]
        );
        $url->delete();

        $this->stopProfiling(__FUNCTION__);

        return redirect()->route('admin_urls_list');
    }

    /**
     * Deletes a ShortUrl Whitelisted URL by ID
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteWhitelistUrl(Request $request): RedirectResponse
    {
        $this->startProfiling(__FUNCTION__);

        $this->validate(
            $request,
            [
                'id' => 'required|exists:short_url_whitelists|int',
            ]
        );

        $url = ShortUrlWhitelist::findOrFail($request->id);
        app('Log')::notice(
            'Whitelist URL deleted',
            [
                'deleted_by' => Auth::id(),
                'url_id'     => $url->id,
                'url'        => $url->url,
            ]
        );
        $url->delete();

        $this->stopProfiling(__FUNCTION__);

        return redirect()->route('admin_urls_whitelist_list');
    }

    /**
     * Adds a new Whitelisted URL
     *
     * @param \Illuminate\Http\Request $request The Add Whitelist URL Request
     *
     * @return \Illuminate\Routing\Redirector | \Illuminate\Http\RedirectResponse
     */
    public function addWhitelistUrl(Request $request)
    {
        $this->startProfiling(__FUNCTION__);

        $data = [
            'url'      => ShortUrl::sanitizeUrl($request->get('url')),
            'internal' => $request->get('internal'),
        ];

        $rules = [
            'url'      => 'required|active_url|max:255|unique:short_url_whitelists',
            'internal' => 'required',
        ];

        validate_array($data, $rules, $request);

        $this->addTrace('Adding WhitelistURL', __FUNCTION__, __LINE__);
        ShortUrlWhitelist::createWhitelistUrl(
            [
                'url'      => parse_url($request->get('url'))['host'],
                'internal' => !$request->get('internal')[0],
            ]
        );

        $this->stopProfiling(__FUNCTION__);

        return redirect()->route('admin_urls_whitelist_list');
    }

    /**
     * Updates a ShortUrl by ID
     *
     * @param \Illuminate\Http\Request $request The Update Request
     *
     * @return \Illuminate\Routing\Redirector | \Illuminate\Http\RedirectResponse
     */
    public function updateUrl(Request $request)
    {
        $this->startProfiling(__FUNCTION__);

        $this->validate(
            $request,
            [
                'id' => 'required|exists:short_urls|int',
            ]
        );

        $data = [
            'url'       => ShortUrl::sanitizeUrl($request->get('url')),
            'hash_name' => $request->get('hash_name'),
            'user_id'   => $request->get('user_id'),
            'expires'   => $request->get('expires'),
        ];

        $rules = [
            'url'       => 'required|url|max:255',
            'hash_name' => 'required|alpha_dash|max:32',
            'user_id'   => 'required|integer|exists:users,id',
            'expires'   => 'nullable|date',
        ];

        validate_array($data, $rules, $request);

        try {
            $this->addTrace('Updating ShortUrl', __FUNCTION__, __LINE__);
            ShortUrl::updateShortUrl(
                [
                    'id'        => $request->id,
                    'url'       => ShortUrl::sanitizeUrl($request->get('url')),
                    'hash_name' => $request->get('hash_name'),
                    'user_id'   => $request->get('user_id'),
                    'expires'   => $request->get('expires'),
                ]
            );
        } catch (UrlNotWhitelistedException | HashNameAlreadyAssignedException $e) {
            $this->addTrace(get_class($e), __FUNCTION__, __LINE__);
            $this->stopProfiling(__FUNCTION__);

            return back()->withErrors($e->getMessage())->withInput(Input::all());
        }

        $this->stopProfiling(__FUNCTION__);

        return redirect()->route('admin_urls_list');
    }
}
