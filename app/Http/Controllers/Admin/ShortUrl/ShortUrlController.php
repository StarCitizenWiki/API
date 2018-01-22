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
 * @package App\Http\Controllers\Admin
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
     */
    public function showUrlListView(): View
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return view('admin.shorturls.index')->with(
            'urls',
            ShortUrl::with('user')->withTrashed()->orderBy('deleted_at')->simplePaginate(100)
        );
    }

    /**
     * Returns the View to edit a ShortUrl
     *
     * @param \App\Models\ShortUrl\ShortUrl|int $url The ShortUrl ID
     *
     * @return \Illuminate\Contracts\View\View
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
     * Updates a ShortUrl by ID
     *
     * @param \Illuminate\Http\Request      $request The Update Request
     * @param \App\Models\ShortUrl\ShortUrl $url
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
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
                'url'        => [
                    'required',
                    'url',
                    'max:255',
                    'unique:short_urls,id,'.$url->id,
                    new ShortUrlWhitelisted(),
                ],
                'user_id'    => 'required|exists:users,id',
                'hash'       => 'required|alpha_dash|max:32|unique:short_urls,id,'.$url->id,
                'expired_at' => 'nullable|date',
            ]
        );

        if (!is_null($data['expired_at'])) {
            $data['expired_at'] = Carbon::parse($data['expired_at']);
        }

        $url->update($data);

        return redirect()->route('admin_url_list')->with('message', __('crud.updated', ['type' => 'ShortUrl']));
    }


    /**
     * Deletes a ShortUrl by ID
     *
     * @param \App\Models\ShortUrl\ShortUrl $url
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteUrl(ShortUrl $url): RedirectResponse
    {
        $url->delete();

        return redirect()->route('admin_url_list');
    }

    /**
     * @param \App\Models\ShortUrl\ShortUrl $url
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restoreUrl(ShortUrl $url)
    {
        $url->restore();

        return redirect()->route('admin_url_list');
    }
}
