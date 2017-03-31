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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;

/**
 * Class AdminController
 *
 * @package App\Http\Controllers\Auth
 */
class AdminController extends Controller
{
    /**
     * Returns the ShortURL List View
     *
     * @return View
     */
    public function showURLsListView() : View
    {
        return view('admin.shorturl.index')->with('urls', ShortURL::all());
    }

    /**
     * Returns the ShortUrl Whitelist View
     *
     * @return View
     */
    public function showURLWhitelistView() : View
    {
        return view('admin.shorturl.whitelistindex')->with('urls', ShortURLWhitelist::all());
    }

    /**
     * Returns the View to add a ShortURL Whitelist URL
     *
     * @return View
     */
    public function showAddURLWhitelistView() : View
    {
        return view('admin.shorturl.whitelistadd');
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

            return view('admin.shorturl.edit')
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
     * @param int $id The ShortURL ID
     *
     * @return RedirectResponse
     */
    public function deleteURL(int $id) : RedirectResponse
    {
        try {
            $url = ShortURL::findOrFail($id);
            Log::info('URL deleted', [
                'deleted_by' => Auth::id(),
                'url_id' => $url->id,
                'url' => $url->url,
                'hash_name' => $url->hash_name,
            ]);
            $url->delete();
        } catch (ModelNotFoundException $e) {
            Log::warning('['.__METHOD__.'] URL not found', [
                'id' => $id,
            ]);
        }

        return redirect()->route('admin_urls_list');
    }

    /**
     * Deletes a ShortURL Whitelisted URL by ID
     *
     * @param int $id The ShortURL Whitelist ID
     *
     * @return RedirectResponse
     */
    public function deleteWhitelistURL(int $id) : RedirectResponse
    {
        try {
            $url = ShortURLWhitelist::findOrFail($id);
            Log::info('Whitelist URL deleted', [
                'deleted_by' => Auth::id(),
                'url_id' => $url->id,
                'url' => $url->url,
            ]);
            $url->delete();
        } catch (ModelNotFoundException $e) {
            Log::warning('['.__METHOD__.'] Whitelist URL not found', [
                'id' => $id,
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

        try {
            ShortURLWhitelist::createWhitelistURL([
                'url' => parse_url($request->get('url'))['host'],
                'internal' => !$request->get('internal')[0],
            ]);
        } catch (HashNameAlreadyAssignedException | URLNotWhitelistedException $e) {
            return redirect()->route('admin_urls_whitelist_add_form')
                             ->withErrors($e->getMessage());
        }

        return redirect()->route('admin_urls_whitelist_list');
    }

    /**
     * Updates a ShortURL by ID
     *
     * @param Request $request The Update Request
     * @param int     $id      The ShortURL ID to update
     *
     * @return Redirect | RedirectResponse
     */
    public function updateURL(Request $request, int $id)
    {
        $data = [
            'url' => ShortURL::sanitizeURL($request->get('url')),
            'hash_name' => $request->get('hash_name'),
            'user_id' => $request->get('user_id'),
        ];

        $rules = [
            'url' => 'required|url|max:255',
            'hash_name' => 'required|alpha_dash|max:32',
            'user_id' => 'required|integer|exists:users,id',
        ];

        validate_array($data, $rules, $request);

        try {
            ShortURL::updateShortURL([
                'id' => $id,
                'url' => ShortURL::sanitizeURL($request->get('url')),
                'hash_name' => $request->get('hash_name'),
                'user_id' => $request->get('user_id'),
            ]);
        } catch (URLNotWhitelistedException | HashNameAlreadyAssignedException $e) {
            return back()->withErrors($e->getMessage());
        }

        return redirect()->route('admin_urls_list');
    }

    /**
     * Returns the View with all Users listed
     *
     * @return View
     */
    public function showUsersListView()
    {
        return view('admin.users.index')->with('users', User::withTrashed()->get());
    }

    /**
     * Returns the View to Edit a User by ID
     *
     * @param int $id The User ID
     *
     * @return View | Redirect
     */
    public function showEditUserView(int $id)
    {
        try {
            $user = User::withTrashed()->findOrFail($id);

            return view('admin.users.edit')->with('user', $user);
        } catch (ModelNotFoundException $e) {
            Log::warning('['.__METHOD__.'] User not found', [
                'id' => $id,
            ]);
        }

        return redirect()->route('admin_users_list');
    }

    /**
     * Deletes a User by ID
     *
     * @param int $id UserID
     *
     * @return RedirectResponse
     */
    public function deleteUser(int $id) : RedirectResponse
    {
        try {
            $user = User::findOrFail($id);
            Log::info('Account with ID '.$id.' deleted by Admin '.Auth::id());
            $user->delete();
        } catch (ModelNotFoundException $e) {
            Log::warning('['.__METHOD__.'] User not found', [
                'id' => $id,
            ]);
        }

        return redirect()->route('admin_users_list');
    }

    /**
     * Restores a User by ID
     *
     * @param int $id UserID
     *
     * @return RedirectResponse
     */
    public function restoreUser(int $id) : RedirectResponse
    {
        try {
            $user = User::withTrashed()->findOrFail($id);
            Log::info('Account with ID '.$id.' restored by Admin '.Auth::id());
            $user->restore();
        } catch (ModelNotFoundException $e) {
            Log::warning('['.__METHOD__.'] User not found', [
                'id' => $id,
            ]);
        }

        return redirect()->route('admin_users_list');
    }

    /**
     * Updates a User by ID
     *
     * @param Request $request Update Request
     * @param int     $id      UserID
     *
     * @return RedirectResponse
     */
    public function updateUser(Request $request, int $id) : RedirectResponse
    {
        $this->validate($request, [
            'name' => 'present',
            'requests_per_minute' => 'required|integer',
            'api_token' => 'required|max:60|min:60|alpha_num',
            'email' => 'required|min:3|email',
            'list' => 'nullable|alpha',
            'notes' => 'nullable',
            'password' => 'present',
        ]);

        $data = [];
        $data['id'] = $id;
        $data['name'] = $request->get('name');
        $data['requests_per_minute'] = $request->get('requests_per_minute');
        $data['api_token'] = $request->get('api_token');
        $data['email'] = $request->get('email');
        $data['notes'] = $request->get('notes');

        if (!is_null($request->get('password')) &&
            !empty($request->get('password'))
        ) {
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
                'id' => $id,
            ]);
        }

        return redirect()->route('admin_users_list');
    }

    /**
     * Returns the View to list all routes
     *
     * @return View
     */
    public function showRoutesView() : View
    {
        return view('admin.routes.index');
    }
}
