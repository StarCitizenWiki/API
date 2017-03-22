<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\HashNameAlreadyAssignedException;
use App\Exceptions\URLNotWhitelistedException;
use App\Http\Controllers\Controller;
use App\Models\ShortURL\ShortURL;
use App\Models\ShortURL\ShortURLWhitelist;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    /**
     * @return View
     */
    public function showURLsListView()
    {
        return view('admin.shorturl.index')->with('urls', ShortURL::all());
    }

    /**
     * @return View
     */
    public function showURLWhitelistView()
    {
        return view('admin.shorturl.whitelistindex')->with('urls', ShortURLWhitelist::all());
    }

    public function showAddURLWhitelistView()
    {
        return view('admin.shorturl.whitelistadd');
    }

    /**
     * @return View
     */
    public function showEditURLView(int $id)
    {
        try {
            $url = ShortURL::findOrFail($id);
            return view('admin.shorturl.edit')->with('url', $url)->with('users', User::all());
        } catch (ModelNotFoundException $e) {
            Log::warning('['.__METHOD__.'] URL not found', [
                'id' => $id
            ]);
            return redirect()->route('admin_urls_list');
        }
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function deleteURL(int $id)
    {
        try {
            $url = ShortURL::findOrFail($id);
            Log::info('URL deleted', [
                'deleted_by' => Auth::id(),
                'url_id' => $url->id,
                'url' => $url->url,
                'hash_name' => $url->hash_name
            ]);
            $url->delete();
        } catch (ModelNotFoundException $e) {
            Log::warning('['.__METHOD__.'] URL not found', [
                'id' => $id
            ]);
        }

        return redirect()->route('admin_urls_list');
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function deleteWhitelistURL(int $id)
    {
        try {
            $url = ShortURLWhitelist::findOrFail($id);
            Log::info('Whitelist URL deleted', [
                'deleted_by' => Auth::id(),
                'url_id' => $url->id,
                'url' => $url->url
            ]);
            $url->delete();
        } catch (ModelNotFoundException $e) {
            Log::warning('['.__METHOD__.'] Whitelist URL not found', [
                'id' => $id
            ]);
        }

        return redirect()->route('admin_urls_whitelist_list');
    }


    public function addWhitelistURL(Request $request)
    {
        $validator = $this->getValidationFactory()->make(
            [
                'url' => ShortURL::sanitizeURL($request->get('url')),
                'internal' => $request->get('internal')
            ],
            [
                'url' => 'required|active_url|max:255|unique:short_url_whitelists',
                'internal' => 'required'
            ]
        );

        if ($validator->fails()) {
            $this->throwValidationException($request, $validator);
        }

        try {
            $url = ShortURLWhitelist::createWhitelistURL([
                'url' => parse_url($request->get('url'))['host'],
                'internal' => !$request->get('internal')[0]
            ]);
        } catch (HashNameAlreadyAssignedException | URLNotWhitelistedException $e) {
            return redirect()->route('admin_urls_whitelist_add_form')->withErrors($e->getMessage());
        }

        return redirect()->route('admin_urls_whitelist_list');
    }

    /**
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function updateURL(Request $request, int $id)
    {
        $validator = $this->getValidationFactory()->make(
            [
                'url' => ShortUrl::sanitizeURL($request->get('url')),
                'hash_name' => $request->get('hash_name'),
                'user_id' => $request->get('user_id')
            ],
            [
                'url' => 'required|url|max:255',
                'hash_name' => 'required|alpha_dash|max:32',
                'user_id' => 'required|integer|exists:users,id'
            ]
        );

        if ($validator->fails()) {
            $this->throwValidationException($request, $validator);
        }

        try {
            ShortURL::updateShortURL([
                'id' => $id,
                'url' => ShortUrl::sanitizeURL($request->get('url')),
                'hash_name' => $request->get('hash_name'),
                'user_id' => $request->get('user_id'),
            ]);
        } catch (URLNotWhitelistedException | HashNameAlreadyAssignedException $e) {
            return back()->withErrors($e->getMessage());
        }

        return redirect()->route('admin_urls_list');
    }

    /**
     * @return View
     */
    public function showUsersListView()
    {
        return view('admin.users.index')->with('users', User::all());
    }

    /**
     * @return View
     */
    public function showEditUserView(int $id)
    {
        try {
            $user = User::findOrFail($id);
            return view('admin.users.edit')->with('user', $user);
        } catch (ModelNotFoundException $e) {
            Log::warning('['.__METHOD__.'] User not found', [
                'id' => $id
            ]);
            return redirect()->route('admin_users_list');
        }
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function deleteUser(int $id)
    {
        try {
            $user = User::findOrFail($id);
            Log::info('Account with ID '.$id.' deleted by Admin '.Auth::id());
            $user->delete();
        } catch (ModelNotFoundException $e) {
            Log::warning('['.__METHOD__.'] User not found', [
                'id' => $id
            ]);
        }
        return redirect()->route('admin_users_list');
    }

    /**
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function updateUser(Request $request, int $id)
    {
        $this->validate($request, [
            'name' => 'present',
            'requests_per_minute' => 'required|integer',
            'api_token' => 'required|max:60|min:60|alpha_num',
            'email' => 'required|min:3|email',
            'list' => 'nullable|alpha',
            'notes' => 'nullable',
            'password' => 'present'
        ]);

        $data = [];
        $data['id'] = $id;
        $data['name'] = $request->get('name');
        $data['requests_per_minute'] = $request->get('requests_per_minute');
        $data['api_token'] = $request->get('api_token');
        $data['email'] = $request->get('email');
        $data['notes'] = $request->get('notes');

        if (!is_null($request->get('password')) && !empty($request->get('password'))) {
            $data['password'] = $request->get('password');
        }

        $data['whitelisted'] = false;
        $data['blacklisted'] = false;

        if ($request->has('list')) {
            if ($request->list === 'blacklisted') {
                $data['whitelisted'] = false;
                $data['blacklisted'] = true;
            }

            if ($request->list === 'whitelisted') {
                $data['whitelisted'] = true;
                $data['blacklisted'] = false;
            }
        }

        try {
            User::updateUser($data);
        } catch (ModelNotFoundException $e) {
            Log::warning('['.__METHOD__.'] User not found', [
                'id' => $id
            ]);
        }

        return redirect()->route('admin_users_list');
    }

    /**
     * @return View
     */
    public function showRoutesView()
    {
        return view('admin.routes.index');
    }
}
