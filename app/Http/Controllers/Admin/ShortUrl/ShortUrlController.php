<?php declare(strict_types = 1);

namespace App\Http\Controllers\Admin\ShortUrl;

use App\Exceptions\HashNameAlreadyAssignedException;
use App\Exceptions\UrlNotWhitelistedException;
use App\Helpers\Hasher;
use App\Http\Controllers\Controller;
use App\Models\ShortUrl\ShortUrl;
use App\Models\User;
use App\Traits\ProfilesMethodsTrait;
use Carbon\Carbon;
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
        $this->middleware('auth:admin');
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
            ShortUrl::withTrashed()->orderBy('deleted_at')->simplePaginate(100)
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
            $url = ShortUrl::withTrashed()->findOrFail($id);
            $this->stopProfiling(__FUNCTION__);

            return view('admin.shorturls.edit')->with(
                'url',
                $url
            );
        } catch (ModelNotFoundException $e) {
            app('Log')::warning("URL with ID: {$id} not found");
        }

        $this->stopProfiling(__FUNCTION__);

        return redirect()->route('admin_urls_list')->withErrors([__('crud.not_found', ['type' => 'ShortUrl'])]);
    }

    /**
     * Updates a ShortUrl by ID
     *
     * @param \Illuminate\Http\Request $request The Update Request
     *
     * @param int                      $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function updateUrl(Request $request, int $id)
    {
        if ($request->exists('delete')) {
            return $this->deleteUrl($request, $id);
        }

        if ($request->exists('restore')) {
            return $this->restoreUrl($request, $id);
        }

        $this->startProfiling(__FUNCTION__);

        $data = [
            'url'        => ShortUrl::sanitizeUrl($request->get('url')),
            'hash'       => $request->get('hash'),
            'user_id'    => Hasher::decode($request->get('user_id')),
            'expired_at' => $request->get('expired_at'),
        ];

        $rules = [
            'url'        => 'required|url|max:255',
            'hash'       => 'required|alpha_dash|max:32',
            'user_id'    => 'required|integer|exists:users,id',
            'expired_at' => 'nullable|date',
        ];

        validate_array($data, $rules, $request);

        $data['id'] = $id;
        if (!is_null($data['expired_at'])) {
            $data['expired_at'] = Carbon::parse($data['expired_at']);
        }

        try {
            $this->addTrace('Updating ShortUrl', __FUNCTION__, __LINE__);
            ShortUrl::updateShortUrl($data);
        } catch (ModelNotFoundException | UrlNotWhitelistedException | HashNameAlreadyAssignedException $e) {
            $this->addTrace(get_class($e), __FUNCTION__, __LINE__);
            $this->stopProfiling(__FUNCTION__);

            return back()->withErrors($e->getMessage())->withInput(Input::all());
        }

        $this->stopProfiling(__FUNCTION__);

        return redirect()->route('admin_urls_list');
    }


    /**
     * Deletes a ShortUrl by ID
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

        $type = 'message';
        $message = __('crud.deleted', ['type' => 'ShortUrl']);

        try {
            $url = ShortUrl::findOrFail($id);

            app('Log')::notice(
                'URL deleted',
                [
                    'deleted_by' => Auth::id(),
                    'url_id'     => $url->id,
                    'url'        => $url->url,
                    'hash'       => $url->hash,
                ]
            );
            $url->delete();
        } catch (ModelNotFoundException $e) {
            $type = 'errors';
            $message = __('crud.not_found', ['type' => 'ShortUrl']);
        }

        $this->stopProfiling(__FUNCTION__);

        return redirect()->route('admin_urls_list')->with($type, $message);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restoreUrl(Request $request, int $id)
    {
        $type = 'message';
        $message = __('crud.restored', ['type' => 'ShortUrl']);

        try {
            ShortUrl::onlyTrashed()->findOrFail($id)->restore();
        } catch (ModelNotFoundException $e) {
            $type = 'errors';
            $message = __('crud.not_found', ['type' => 'ShortUrl']);
        }

        return redirect()->route('admin_urls_list')->with($type, $message);
    }
}
