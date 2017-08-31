<?php declare(strict_types = 1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
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
        $this->middleware('auth:admin');
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
     * @param \App\Models\User $user
     *
     * @return \Illuminate\View\View
     */
    public function showUrlListView(User $user): View
    {
        app('Log')::info(make_name_readable(__FUNCTION__), ['id' => $user->id]);

        return view('admin.shorturls.index')->with(
            'urls',
            $user->shortUrls()->simplePaginate(100)
        );
    }

    /**
     * Returns the View to Edit a User by ID
     *
     * @param \App\Models\User $user
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Routing\Redirector
     */
    public function showEditUserView(User $user)
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        $user->load(
            [
                'shortUrls'   => function ($query) {
                    $query->orderBy('created_at')->take(5);
                },
                'apiRequests' => function ($query) {
                    $query->orderBy('created_at')->take(5);
                },
            ]
        );

        return view('admin.user.edit')->with(
            'user',
            $user
        );
    }

    /**
     * Returns the View with all Users listed
     *
     * @param \App\Models\User $user
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function showRequestView(User $user)
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return view('admin.user.requests')->with(
            'requests',
            $user->apiRequests()->getResults()
        );
    }

    /**
     * Deletes a User by ID
     *
     * @param \App\Models\User $user
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteUser(User $user): RedirectResponse
    {
        app('Log')::notice("Account {$user->name} ({$user->id}) deleted by ".Auth::id());

        $user->delete();

        return redirect()->route('admin_user_list');
    }

    /**
     * Restores a User by ID
     *
     * @param \App\Models\User $user
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restoreUser(User $user): RedirectResponse
    {
        app('Log')::notice("Restored Account with ID: {$user->id}");

        $user->restore();

        return redirect()->route('admin_user_list');
    }

    /**
     * Updates a User by ID
     *
     * @param \Illuminate\Http\Request $request Update Request
     * @param \App\Models\User         $user
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $data = $this->validate(
            $request,
            [
                'name'                => 'present|string',
                'requests_per_minute' => 'required|integer',
                'api_token'           => 'required|alpha_num|max:60|min:60',
                'email'               => 'required|email|min:3',
                'state'               => 'required|int|between:0,2',
                'notes'               => 'nullable|string',
                'password'            => 'nullable|string|min:3',
            ]
        );

        if (!is_null($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('admin_user_list');
    }
}
