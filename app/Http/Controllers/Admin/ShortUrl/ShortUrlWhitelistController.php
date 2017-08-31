<?php declare(strict_types = 1);

namespace App\Http\Controllers\Admin\ShortUrl;

use App\Http\Controllers\Controller;
use App\Models\ShortUrl\ShortUrlWhitelist;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Class ShortUrlWhitelistController
 * @package App\Http\Controllers\Admin\ShortUrl
 */
class ShortUrlWhitelistController extends Controller
{
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
     * Deletes a ShortUrl Whitelisted URL by ID
     *
     * @param \App\Models\ShortUrl\ShortUrlWhitelist $url
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteWhitelistUrl(ShortUrlWhitelist $url): RedirectResponse
    {
        $url->delete();

        return redirect()->route('admin_url_whitelist_list');
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
        $data = $this->validate(
            $request,
            [
                'url'      => 'required|string|max:255|unique:short_url_whitelists|regex:/(\w+\.\w+)$/',
                'internal' => 'nullable',
            ]
        );

        if (array_key_exists('internal', $data) && !is_null($data['internal'])) {
            $data['internal'] = false;
        } else {
            $data['internal'] = true;
        }

        ShortUrlWhitelist::create($data);

        return redirect()->route('admin_url_whitelist_list')->with(
            'message',
            __('crud.created', ['type' => 'WhitelistUrl'])
        );
    }
}
