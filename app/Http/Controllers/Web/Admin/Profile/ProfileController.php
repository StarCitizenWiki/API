<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\Admin\Profile;

use App\Models\Account\Admin\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * Class ProfileController
 */
class ProfileController extends Controller
{
    /**
     * AdminController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:admin');
    }

    /**
     * @param \App\Models\Account\Admin\Admin $admin
     */
    public function show(Admin $admin)
    {

    }
}
