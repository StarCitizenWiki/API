<?php declare(strict_types = 1);

namespace App\Http\Controllers\Admin\ShortUrl;

use App\Http\Controllers\Controller;
use App\Models\ShortUrl\ShortUrlWhitelist;
use App\Traits\ProfilesMethodsTrait;
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
    use ProfilesMethodsTrait;

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
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteWhitelistUrl(Request $request, int $id): RedirectResponse
    {
        $this->startProfiling(__FUNCTION__);

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

        $this->stopProfiling(__FUNCTION__);

        return redirect()->route('admin_urls_whitelist_list')->with($type, $message);
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

        $url = parse_url($request->get('url'))['host'];

        $data = [
            'url'      => $url,
            'internal' => $request->get('internal'),
        ];

        $rules = [
            'url'      => 'required|string|max:255|unique:short_url_whitelists',
            'internal' => 'nullable',
        ];

        validate_array($data, $rules, $request);

        $this->addTrace('Adding WhitelistURL', __FUNCTION__, __LINE__);
        ShortUrlWhitelist::createWhitelistUrl(
            [
                'url'      => $url,
                'internal' => $request->get('internal')[0],
            ]
        );

        $this->stopProfiling(__FUNCTION__);

        return redirect()->route('admin_urls_whitelist_list')->with('message', __('crud.created', ['type' => 'WhitelistUrl']));
    }
}
