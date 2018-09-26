<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\User\License;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class LicenseController
 */
class LicenseController extends Controller
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
     * View to Accept Editor Licence
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('web.user.license.view');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        if (Auth::user()->settings->editorLicenseAccepted()) {
            return redirect()->route('web.user.account.index');
        }

        return view('user.license.show');
    }

    /**
     * Update Admin to accept Editor Licence
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function accept(Request $request)
    {
        $this->authorize('web.user.license.update');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        /** @var \App\Models\Account\User\User $user */
        $user = Auth::user();
        $user->settings()->updateOrCreate(
            [
                'user_id' => $user->id,
            ],
            [
                'editor_license_accepted' => true,
            ]
        );

        return redirect()->route('web.user.account.index');
    }
}
