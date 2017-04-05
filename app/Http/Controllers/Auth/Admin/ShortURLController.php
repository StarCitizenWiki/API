<?php

namespace App\Http\Controllers\Auth\Admin;

use App\Exceptions\HashNameAlreadyAssignedException;
use App\Exceptions\URLNotWhitelistedException;
use App\Models\ShortURL\ShortURL;
use App\Models\ShortURL\ShortURLWhitelist;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

/**
 * Class AdminShortURLController
 * @package App\Http\Controllers\Auth
 */
class ShortURLController extends Controller
{
    /**
     * Returns the ShortURL List View
     *
     * @return View
     */
    public function showURLsListView() : View
    {
        return view('admin.shorturls.index')->with('urls', ShortURL::all());
    }

    /**
     * Returns the ShortUrl Whitelist View
     *
     * @return View
     */
    public function showURLWhitelistView() : View
    {
        return view('admin.shorturls.whitelists.index')->with('urls', ShortURLWhitelist::all());
    }

    /**
     * Returns the View to add a ShortURL Whitelist URL
     *
     * @return View
     */
    public function showAddURLWhitelistView() : View
    {
        return view('admin.shorturls.whitelists.add');
    }

    /**
     * Returns the View to edit a ShortURL
     *
     * @param int $id The ShortURL ID
     *
     * @return View | RedirectResponse
     */
    public function showEditURLView(int $id)
    {
        try {
            $url = ShortURL::findOrFail($id);

            return view('admin.shorturls.edit')
                        ->with('url', $url)
                        ->with('users', User::all());
        } catch (ModelNotFoundException $e) {
            Log::warning('['.__METHOD__.'] URL not found', [
                'id' => $id,
            ]);
        }

        return redirect()->route('admin_urls_list');
    }

    /**
     * Deletes a ShortURL by ID
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function deleteURL(Request $request) : RedirectResponse
    {
        $this->validate($request, [
            'id' => 'required|exists:short_urls|int',
        ]);

        try {
            $url = ShortURL::findOrFail($request->id);
            Log::info('URL deleted', [
                'deleted_by' => Auth::id(),
                'url_id' => $url->id,
                'url' => $url->url,
                'hash_name' => $url->hash_name,
            ]);
            $url->delete();
        } catch (ModelNotFoundException $e) {
            Log::warning('['.__METHOD__.'] URL not found', [
                'id' => $request->id,
            ]);
        }

        return redirect()->route('admin_urls_list');
    }

    /**
     * Deletes a ShortURL Whitelisted URL by ID
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function deleteWhitelistURL(Request $request) : RedirectResponse
    {
        $this->validate($request, [
            'id' => 'required|exists:short_url_whitelists|int',
        ]);

        try {
            $url = ShortURLWhitelist::findOrFail($request->id);
            Log::info('Whitelist URL deleted', [
                'deleted_by' => Auth::id(),
                'url_id' => $url->id,
                'url' => $url->url,
            ]);
            $url->delete();
        } catch (ModelNotFoundException $e) {
            Log::warning('['.__METHOD__.'] Whitelist URL not found', [
                'id' => $request->id,
            ]);
        }

        return redirect()->route('admin_urls_whitelist_list');
    }

    /**
     * Adds a new Whitelisted URL
     *
     * @param Request $request The Add Whitelist URL Request
     *
     * @return Redirect | RedirectResponse
     */
    public function addWhitelistURL(Request $request)
    {
        $data = [
            'url' => ShortURL::sanitizeURL($request->get('url')),
            'internal' => $request->get('internal'),
        ];

        $rules = [
            'url' => 'required|active_url|max:255|unique:short_url_whitelists',
            'internal' => 'required',
        ];

        validate_array($data, $rules, $request);

        ShortURLWhitelist::createWhitelistURL([
            'url' => parse_url($request->get('url'))['host'],
            'internal' => !$request->get('internal')[0],
        ]);

        return redirect()->route('admin_urls_whitelist_list');
    }

    /**
     * Updates a ShortURL by ID
     *
     * @param Request $request The Update Request
     *
     * @return Redirect | RedirectResponse
     */
    public function updateURL(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|exists:short_urls|int',
        ]);

        $data = [
            'url' => ShortURL::sanitizeURL($request->get('url')),
            'hash_name' => $request->get('hash_name'),
            'user_id' => $request->get('user_id'),
            'expires' => $request->get('expires'),
        ];

        $rules = [
            'url' => 'required|url|max:255',
            'hash_name' => 'required|alpha_dash|max:32',
            'user_id' => 'required|integer|exists:users,id',
            'expires' => 'nullable|date',
        ];

        validate_array($data, $rules, $request);

        try {
            ShortURL::updateShortURL([
                'id' => $request->id,
                'url' => ShortURL::sanitizeURL($request->get('url')),
                'hash_name' => $request->get('hash_name'),
                'user_id' => $request->get('user_id'),
                'expires' => $request->get('expires'),
            ]);
        } catch (URLNotWhitelistedException | HashNameAlreadyAssignedException $e) {
            return back()->withErrors($e->getMessage())->withInput(Input::all());
        }

        return redirect()->route('admin_urls_list');
    }
}
