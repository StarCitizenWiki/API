<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User\StarCitizen\Starmap\Starsystem;

use App\Http\Controllers\Controller;
use App\Models\Api\StarCitizen\Starmap\Starsystem\Starsystem;
use Illuminate\Contracts\View\View;

/**
 * Class AdminStarmapController
 */
class StarsystemController extends Controller
{
    public function index(): View
    {
        $this->authorize('web.user.starcitizen.starmap.view');

        return view(
            'user.starcitizen.starmap.starsystems.index',
            [
                'systems' => Starsystem::query()->orderBy('code')->get(),
            ]
        );
    }

    public function show(Starsystem $starsystem): View
    {
        $this->authorize('web.user.starcitizen.starmap.view');

        return view(
            'user.starcitizen.starmap.starsystems.show',
            [
                'system' => $starsystem,
            ]
        );
    }
}
