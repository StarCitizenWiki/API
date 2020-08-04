<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\User\Changelog;

use App\Http\Controllers\Controller;
use App\Models\System\ModelChangelog;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;

/**
 * Model Changelogs
 */
class ChangelogController extends Controller
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
        $this->authorize('web.user.changelogs.view');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $changelogs = ModelChangelog::query()->orderByDesc('id')->paginate(25);

        return view(
            'user.changelog.index',
            [
                'changelogs' => $changelogs,
            ]
        );
    }
}
