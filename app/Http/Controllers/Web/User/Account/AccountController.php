<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\User\Account;

use App\Http\Controllers\Controller;
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
    public function show()
    {
        $this->authorize('web.user.account.view');

        return view(
            'user.profile.index',
            [
                'user' => Auth::user(),
            ]
        );
    }
}
