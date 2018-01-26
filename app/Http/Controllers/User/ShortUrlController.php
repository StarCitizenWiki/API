<?php declare(strict_types = 1);

namespace App\Http\Controllers\User;

use App\Events\UrlShortened;
use App\Http\Controllers\Controller;
use App\Models\ShortUrl\ShortUrl;
use App\Rules\ShortUrlWhitelisted;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $this->authorize('create', ShortUrl::class);

        return view('user.shorturls.add')->with(
            'user',
            Auth::user()
        );
    }

    /**
     * Returns the Edit ShortUrl View
     *
     * @param \App\Models\ShortUrl\ShortUrl $url The ShortUrl to edit
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function showEditUrlView(ShortUrl $url): View
    {
        app('Log')::info(make_name_readable(__FUNCTION__), ['url' => $url->id]);
        $this->authorize('view', $url);

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
     */
    public function addUrl(Request $request)
    {
        $this->authorize('create', ShortUrl::class);

        $data = $this->validate(
            $request,
            [
                'url'        => ['required|url|max:255|unique:short_urls', new ShortUrlWhitelisted()],
                'hash'       => 'nullable|alpha_dash|max:32|unique:short_urls',
                'expired_at' => 'nullable|date|after:'.Carbon::now(),
            ]
        );

        if (!is_null($data['expired_at'])) {
            $data['expired_at'] = Carbon::parse($data['expired_at']);
        }

        $url = ShortUrl::create($data);
        event(new UrlShortened($url));

        return redirect()->route('account.url.list')->with(
            'hash',
            $url->hash
        );
    }

    /**
     * Updates a ShortUrl by its ID
     *
     * @param \Illuminate\Http\Request      $request The Update Request
     *
     * @param \App\Models\ShortUrl\ShortUrl $url
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateUrl(Request $request, ShortUrl $url): RedirectResponse
    {
        if ($request->has('delete')) {
            return $this->deleteUrl($url);
        }

        $this->authorize('update', $url);

        $data = $this->validate(
            $request,
            [
                'url'        => [
                    'required',
                    'url',
                    'max:255',
                    'unique:short_urls,id,'.$url->id,
                    new ShortUrlWhitelisted(),
                ],
                'hash'       => 'required|alpha_dash|max:32|unique:short_urls,id,'.$url->id,
                'expired_at' => 'nullable|date',
            ]
        );

        if (!is_null($data['expired_at'])) {
            $data['expired_at'] = Carbon::parse($data['expired_at']);
        }

        $url->update($data);

        return redirect()->route('account.url.list')->with('message', __('crud.updated', ['type' => 'ShortUrl']));
    }

    /**
     * Deletes a Users ShortUrl
     *
     * @param \App\Models\ShortUrl\ShortUrl $url
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteUrl(ShortUrl $url): RedirectResponse
    {
        $this->authorize('delete', $url);

        $url->delete();

        return redirect()->route('account.url.list')->with('message', __('crud.deleted', ['type' => 'ShortUrl']));
    }
}
