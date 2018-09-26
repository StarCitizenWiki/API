<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\User\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class ProfileController
 */
class AccountController extends Controller
{
    /**
     * AdminController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('web.user.account.view');

        return view(
            'user.account.index',
            [
                'user' => Auth::user(),
            ]
        );
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request)
    {
        $this->authorize('web.user.account.update');

        Auth::user()->settings()->updateOrCreate(
            [
                'user_id' => Auth::id(),
            ],
            [
                'editor_receive_emails' => $request->has('editor_receive_emails'),
                'receive_api_notifications' => $request->has('api_notifications'),
            ]
        );

        return redirect()->back()->withMessages(
            [
                'success' => [
                    __('crud.updated', ['type' => __('Einstellungen')]),
                ],
            ]
        );
    }
}
