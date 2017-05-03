<?php

namespace App\Http\Controllers\Auth\Admin;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

/**
 * Class AdminUserController
 * @package App\Http\Controllers\Auth\Admin
 */
class UserController extends Controller
{
    /**
     * Returns the View with all Users listed
     *
     * @return View
     */
    public function showUsersListView()
    {
        $this->logger::debug('Users List View requested');

        return view('admin.users.index')->with(
            'users',
            User::withTrashed()->get()
        );
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

            $this->logger::debug('Edit User View for User requested');

            return view('admin.users.edit')->with(
                'user',
                $user
            );
        } catch (ModelNotFoundException $e) {
            $this->logger::warning('User not found', [
                'id' => $id,
            ]);
        }

        return redirect()->route('admin_users_list');
    }

    /**
     * Returns the View with all Users listed
     *
     * @param int $id
     *
     * @return View
     */
    public function showRequestsView(int $id)
    {
        $this->logger::debug('Users Requests View requested');

        return view('admin.users.requests')->with(
            'requests',
            User::find($id)->apiRequests()->getResults()
        );
    }

    /**
     * Deletes a User by ID
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function deleteUser(Request $request) : RedirectResponse
    {
        $this->validate($request, [
            'id' => 'required|exists:users|int',
        ]);

        try {
            $user = User::findOrFail($request->id);
            $this->logger::notice('Account deleted', [
                'account_id' => $request->get('id'),
                'deleted_by' => Auth::id(),
            ]);
            $user->delete();
        } catch (ModelNotFoundException $e) {
            return redirect()->route('admin_users_list')
                             ->withErrors(__('admin/users/edit.not_found'));
        }

        return redirect()->route('admin_users_list');
    }

    /**
     * Restores a User by ID
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function restoreUser(Request $request) : RedirectResponse
    {
        $this->validate($request, [
            'id' => 'required|exists:users|int',
        ]);

        try {
            $user = User::withTrashed()->findOrFail($request->id);
            $this->logger::notice('Account restored', [
                'account_id'  => $request->get('id'),
                'restored_by' => Auth::id(),
            ]);
            $user->restore();
        } catch (ModelNotFoundException $e) {
            return redirect()->route('admin_users_list')
                             ->withErrors(__('admin/users/edit.not_found'));
        }

        return redirect()->route('admin_users_list');
    }

    /**
     * Updates a User by ID
     *
     * @param Request $request Update Request
     *
     * @return RedirectResponse
     */
    public function updateUser(Request $request) : RedirectResponse
    {
        $this->validate($request, [
            'id' => 'required|exists:users|int',
            'name' => 'present',
            'requests_per_minute' => 'required|integer',
            'api_token' => 'required|max:60|min:60|alpha_num',
            'email' => 'required|min:3|email',
            'list' => 'nullable|alpha',
            'notes' => 'nullable',
            'password' => 'present',
        ]);

        $data = [];
        $data['id'] = $request->id;
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

        User::updateUser($data);

        return redirect()->route('admin_users_list');
    }
}
