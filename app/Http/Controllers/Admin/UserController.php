<?php declare(strict_types = 1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Class AdminUserController
 *
 * @package App\Http\Controllers\Admin
 */
class UserController extends Controller
{
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
     * Returns the ShortUrl List View
     *
     * @param int $id UserID
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function showUrlListView(int $id): View
    {
        app('Log')::info(make_name_readable(__FUNCTION__), ['id' => $id]);

        return view('admin.shorturls.index')->with(
            'urls',
            User::find($id)->shortUrls()->simplePaginate(100)
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
        app('Log')::info(make_name_readable(__FUNCTION__));
        try {
            $user = User::with(
                [
                    'shortUrls'   => function ($query) {
                        $query->orderBy('created_at')->take(5);
                    },
                    'apiRequests' => function ($query) {
                        $query->orderBy('created_at')->take(5);
                    },
                ]
            )->withTrashed()->findOrFail($id);

            return view('admin.user.edit')->with(
                'user',
                $user
            );
        } catch (ModelNotFoundException $e) {
            app('Log')::warning("User with ID: {$id} not found");
        }

        return redirect()->route('admin_user_list');
    }

    /**
     * Returns the View with all Users listed
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function showRequestView(int $id)
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
        $this->validate(
            $request,
            [
                'id' => 'required|exists:users|int',
            ]
        );

        try {
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
            return redirect()->route('admin_user_list')->withErrors(__('admin/users/edit.not_found'));
        }

        return redirect()->route('admin_user_list');
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
        $this->validate(
            $request,
            [
                'id' => 'required|exists:users|int',
            ]
        );

        try {
            $user = User::withTrashed()->findOrFail($request->id);
            app('Log')::notice("Restored Account with ID: {$request->id}");
            $user->restore();
        } catch (ModelNotFoundException $e) {
            return redirect()->route('admin_user_list')->withErrors(__('admin/users/edit.not_found'));
        }

        return redirect()->route('admin_user_list');
    }

    /**
     * Updates a User by ID
     *
     * @param \Illuminate\Http\Request $request Update Request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateUser(Request $request, int $id): RedirectResponse
    {
        $this->validate(
            $request,
            [
                'name'                => 'present',
                'requests_per_minute' => 'required|integer',
                'api_token'           => 'required|max:60|min:60|alpha_num',
                'email'               => 'required|min:3|email',
                'list'                => 'nullable|alpha',
                'notes'               => 'nullable|string',
                'password'            => 'present',
            ]
        );

        $data = [];
        $data['id'] = $id;
        $data['name'] = $request->get('name');
        $data['requests_per_minute'] = $request->get('requests_per_minute');
        $data['api_token'] = $request->get('api_token');
        $data['email'] = $request->get('email');
        $data['notes'] = $request->get('notes');

        if (!is_null($request->get('password')) && !empty($request->get('password'))) {
            $data['password'] = bcrypt($request->get('password'));
        }

        $data['whitelisted'] = false;
        $data['blacklisted'] = false;

        if ($request->has('list')) {
            if ('blacklisted' === $request->list) {
                $data['whitelisted'] = false;
                $data['blacklisted'] = true;
            }

            if ('whitelisted' === $request->list) {
                $data['whitelisted'] = true;
                $data['blacklisted'] = false;
            }
        }

        User::updateUser($data);

        return redirect()->route('admin_user_list');
    }
}
