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
            Log::info('Account with ID '.$request->id.' deleted by Admin '.Auth::id());
            $user->delete();
        } catch (ModelNotFoundException $e) {
            Log::warning('['.__METHOD__.'] User not found', [
                'id' => $request->id,
            ]);
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
            Log::info('Account with ID '.$request->id.' restored by Admin '.Auth::id());
            $user->restore();
        } catch (ModelNotFoundException $e) {
            Log::warning('['.__METHOD__.'] User not found', [
                'id' => $request->id,
            ]);
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

        try {
            User::updateUser($data);
        } catch (ModelNotFoundException $e) {
            Log::warning('['.__METHOD__.'] User not found', [
                'id' => $request->id,
            ]);
        }

        return redirect()->route('admin_users_list');
    }
}
