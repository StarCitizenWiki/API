<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User\Changelog;

use App\Http\Controllers\Controller;
use App\Models\System\ModelChangelog;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
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
     * @param Request $request
     * @return Factory|View
     *
     * @throws AuthorizationException
     */
    public function index(Request $request)
    {
        $this->authorize('web.user.changelogs.view');

        $query = ModelChangelog::query()
            ->with('changelog')
            ->orderByDesc('id');

        if ($request->get('model') !== null) {
            $query->where(
                'changelog_type',
                $request->get('model'),
            );
        }

        if ($request->get('type') !== null) {
            $query->where(
                'type',
                $request->get('type'),
            );
        }

        return view(
            'user.changelog.index',
            [
                'changelogs' => $query->paginate($request->get('limit', 25)),
                'models' => ModelChangelog::query()->select('changelog_type')->distinct()->get(),
                'types' => ModelChangelog::query()->select('type')->distinct()->get(),
            ]
        );
    }
}
