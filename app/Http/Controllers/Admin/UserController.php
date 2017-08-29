<?php declare(strict_types = 1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ProfilesMethodsTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class AdminUserController
 *
 * @package App\Http\Controllers\Admin
 */
class UserController extends Controller
{
    use ProfilesMethodsTrait;

    /**
     * UserController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Returns the View with all Users listed
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function showUserListView()
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return view('admin.user.index')->with(
            'users',
            User::withTrashed()->orderBy('deleted_at')->simplePaginate(100)
        );
    }

    /**
     * Returns the View to Edit a User by ID
     *
     * @param int $id The User ID
     *
     * @return \Illuminate\Contracts\View\View | \Illuminate\Routing\Redirector
     */
    public function showEditUserView(int $id)
    {
        $this->startProfiling(__FUNCTION__);

        app('Log')::info(make_name_readable(__FUNCTION__));
        try {
            $this->addTrace("Getting User with ID: {$id}", __FUNCTION__, __LINE__);
            $user = User::with(['shortUrls', 'apiRequests'])->withTrashed()->findOrFail($id);
            $this->stopProfiling(__FUNCTION__);

            return view('admin.user.edit')->with(
                'user',
                $user
            );
        } catch (ModelNotFoundException $e) {
            app('Log')::warning("User with ID: {$id} not found");
        }

        $this->stopProfiling(__FUNCTION__);

        return redirect()->route('admin_users_list');
    }

    /**
     * Returns the View with all Users listed
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function showRequestsView(int $id)
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return view('admin.user.requests')->with(
            'requests',
            User::find($id)->apiRequests()->getResults()
        );
    }

    /**
     * Deletes a User by ID
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteUser(Request $request): RedirectResponse
    {
        $this->startProfiling(__FUNCTION__);

        $this->validate(
            $request,
            [
                'id' => 'required|exists:users|int',
            ]
        );

        try {
            $this->addTrace("Getting User with ID: {$request->id}", __FUNCTION__, __LINE__);
            $user = User::findOrFail($request->id);
            app('Log')::notice(
                'Account deleted',
                [
                    'account_id' => $request->get('id'),
                    'deleted_by' => Auth::id(),
                ]
            );
            $user->delete();
        } catch (ModelNotFoundException $e) {
            $this->addTrace('User not found', __FUNCTION__, __LINE__);
            $this->stopProfiling(__FUNCTION__);

            return redirect()->route('admin_users_list')->withErrors(__('admin/users/edit.not_found'));
        }

        $this->stopProfiling(__FUNCTION__);

        return redirect()->route('admin_users_list');
    }

    /**
     * Restores a User by ID
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restoreUser(Request $request): RedirectResponse
    {
        $this->startProfiling(__FUNCTION__);

        $this->validate(
            $request,
            [
                'id' => 'required|exists:users|int',
            ]
        );

        try {
            $this->addTrace("Getting User with ID: {$request->id}", __FUNCTION__, __LINE__);
            $user = User::withTrashed()->findOrFail($request->id);
            app('Log')::notice("Restored Account with ID: {$request->id}");
            $user->restore();
        } catch (ModelNotFoundException $e) {
            $this->addTrace('User not found', __FUNCTION__, __LINE__);
            $this->stopProfiling(__FUNCTION__);

            return redirect()->route('admin_users_list')->withErrors(__('admin/users/edit.not_found'));
        }

        $this->stopProfiling(__FUNCTION__);

        return redirect()->route('admin_users_list');
    }

    /**
     * Updates a User by ID
     *
     * @param \Illuminate\Http\Request $request Update Request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateUser(Request $request): RedirectResponse
    {
        $this->startProfiling(__FUNCTION__);

        $this->validate(
            $request,
            [
                'id'                  => 'required|exists:users|int',
                'name'                => 'present',
                'requests_per_minute' => 'required|integer',
                'api_token'           => 'required|max:60|min:60|alpha_num',
                'email'               => 'required|min:3|email',
                'list'                => 'nullable|alpha',
                'notes'               => 'nullable',
                'password'            => 'present',
            ]
        );

        $data = [];
        $data['id'] = $request->id;
        $data['name'] = $request->get('name');
        $data['requests_per_minute'] = $request->get('requests_per_minute');
        $data['api_token'] = $request->get('api_token');
        $data['email'] = $request->get('email');
        $data['notes'] = $request->get('notes');

        if (!is_null($request->get('password')) && !empty($request->get('password'))) {
            $this->addTrace('Password changed', __FUNCTION__, __LINE__);
            $data['password'] = $request->get('password');
        }

        $data['whitelisted'] = false;
        $data['blacklisted'] = false;

        if ($request->has('list')) {
            $this->addTrace('Black/Whitelist-Flag set', __FUNCTION__, __LINE__);
            if ('blacklisted' === $request->list) {
                $this->addTrace('User is now blacklisted', __FUNCTION__, __LINE__);
                $data['whitelisted'] = false;
                $data['blacklisted'] = true;
            }

            if ('whitelisted' === $request->list) {
                $this->addTrace('User is now whitelisted', __FUNCTION__, __LINE__);
                $data['whitelisted'] = true;
                $data['blacklisted'] = false;
            }
        }

        $this->addTrace('Start Update', __FUNCTION__, __LINE__);
        User::updateUser($data);

        $this->stopProfiling(__FUNCTION__);

        return redirect()->route('admin_users_list');
    }
}
