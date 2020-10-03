<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User\Account;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

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
     * @return Factory|View
     *
     * @throws AuthorizationException
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
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function update(Request $request)
    {
        $this->authorize('web.user.account.update');

        Auth::user()->settings()->updateOrCreate(
            [
                'user_id' => Auth::id(),
            ],
            [
                'receive_comm_link_notifications' => $request->has('receive_comm_link_notifications'),
                'receive_api_notifications' => $request->has('api_notifications'),
            ]
        );

        return redirect()->route('web.user.account.index')->withMessages(
            [
                'success' => [
                    __('crud.updated', ['type' => __('Einstellungen')]),
                ],
            ]
        );
    }
}
