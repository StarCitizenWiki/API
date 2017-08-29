<?php declare(strict_types = 1);

namespace App\Http\Controllers\Admin\ShortUrl;

use App\Http\Controllers\Controller;
use App\Models\ShortUrl\ShortUrlWhitelist;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteWhitelistUrl(int $id): RedirectResponse
    {
        $type = 'message';
        $message = __('crud.deleted', ['type' => 'WhitelistUrl']);

        try {
            $url = ShortUrlWhitelist::findOrFail($id);

            app('Log')::notice(
                'Whitelist URL deleted',
                [
                    'deleted_by' => Auth::id(),
                    'url_id'     => $url->id,
                    'url'        => $url->url,
                ]
            );
            $url->delete();
        } catch (ModelNotFoundException $e) {
            $type = 'errors';
            $message = __('crud.not_found', ['type' => 'WhitelistUrl']);
        }

        return redirect()->route('admin_url_whitelist_list')->with($type, $message);
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
        $url = parse_url($request->get('url'))['host'];

        $data = [
            'url'      => $url,
            'internal' => is_null($request->get('internal')) ? true : false,
        ];

        $rules = [
            'url'      => 'required|string|max:255|unique:short_url_whitelists',
            'internal' => 'nullable',
        ];

        validate_array($data, $rules, $request);

        ShortUrlWhitelist::createWhitelistUrl($data);

        return redirect()->route('admin_url_whitelist_list')->with(
            'message',
            __('crud.created', ['type' => 'WhitelistUrl'])
        );
    }
}
