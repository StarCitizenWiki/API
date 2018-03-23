<?php declare(strict_types = 1);

namespace App\Http\Controllers\Admin\ShortUrl;

use App\Http\Controllers\Controller;
use App\Models\ShortUrl\ShortUrl;
use App\Rules\ShortUrlWhitelisted;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Class AdminShortUrlController
 */
class ShortUrlController extends Controller
{
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
     *
     * @throws \App\Exceptions\WrongMethodNameException
     */
    public function showUrlListView(): View
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return view('admin.shorturls.index')->with(
            'urls',
            ShortUrl::withTrashed()->orderBy('deleted_at')->simplePaginate(100)
        );
    }

    /**
     * Returns the View to edit a ShortUrl
     *
     * @param \App\Models\ShortUrl\ShortUrl|int $url The ShortUrl ID
     *
     * @return \Illuminate\Contracts\View\View
     *
     * @throws \App\Exceptions\WrongMethodNameException
     */
    public function showEditUrlView(ShortUrl $url)
    {
        app('Log')::info(make_name_readable(__FUNCTION__), ['id' => $url]);

        return view('admin.shorturls.edit')->with(
            'url',
            $url
        );
    }

    /**
     * Returns the add short url view
     *
     * @return \Illuminate\Contracts\View\View
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function showAddUrlView(): View
    {
        $this->authorize('create', ShortUrl::class);

        return view('admin.shorturls.add');
    }

    /**
     * Updates a ShortUrl by ID
     *
     * @param \Illuminate\Http\Request      $request The Update Request
     * @param \App\Models\ShortUrl\ShortUrl $url
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     *
     * @throws \Exception
     */
    public function updateUrl(Request $request, ShortUrl $url)
    {
        if ($request->has('delete')) {
            return $this->deleteUrl($url);
        }

        if ($request->has('restore')) {
            return $this->restoreUrl($url);
        }

        $data = $this->validate(
            $request,
            [
                'url' => [
                    'required',
                    'url',
                    'max:255',
                    'unique:short_urls,id,'.$url->id,
                    new ShortUrlWhitelisted(),
                ],
                'hash' => 'required|alpha_dash|max:32|unique:short_urls,id,'.$url->id,
                'expired_at' => 'nullable|date',
            ]
        );

        if (!is_null($data['expired_at'])) {
            $data['expired_at'] = Carbon::parse($data['expired_at']);
        }

        $url->update($data);

        return redirect()->route('admin.url.list')->with(
            'messages',
            [
                __('crud.updated', ['type' => 'ShortUrl']),
            ]
        );
    }

    /**
     * Deletes a ShortUrl by ID
     *
     * @param \App\Models\ShortUrl\ShortUrl $url
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Exception
     */
    public function deleteUrl(ShortUrl $url): RedirectResponse
    {
        $url->delete();

        return redirect()->route('admin.url.list')->with(
            'messages',
            [
                [
                    'danger',
                    __('crud.deleted', ['type' => 'ShortUrl']),
                ],
            ]
        );
    }

    /**
     * @param \App\Models\ShortUrl\ShortUrl $url
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restoreUrl(ShortUrl $url)
    {
        $url->restore();

        return redirect()->route('admin.url.list')->with(
            'messages',
            [
                [
                    'success',
                    __('crud.restored', ['type' => 'ShortUrl']),
                ],
            ]
        );
    }

    /**
     * Validates the url add Request and creates a new ShortUrl
     *
     * @param \Illuminate\Http\Request $request The Request
     *
     * @return \Illuminate\Http\RedirectResponse | \Illuminate\Routing\Redirector
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function addUrl(Request $request)
    {
        $this->authorize('create', ShortUrl::class);

        $data = $request->validate(
            [
                'url' => [
                    'required',
                    'max:255',
                    'url',
                    'unique:short_urls',
                    new ShortUrlWhitelisted(),
                ],
                'hash' => 'nullable|alpha_dash|max:32|unique:short_urls',
                'expired_at' => 'nullable|date|after:'.Carbon::now(),
            ]
        );

        if (isset($data['expired_at'])) {
            $data['expired_at'] = Carbon::parse($data['expired_at']);
        }

        if (!isset($data['hash'])) {
            $data['hash'] = ShortUrl::generateShortUrlHash();
        }

        $url = ShortUrl::create($data);

        return redirect()->route('admin.url.list')->with(
            'messages',
            [
                __('crud.created', ['type' => 'ShortUrl']),
                [
                    'success',
                    $url->getFullShortUrl(),
                ],
            ]
        );
    }
}
