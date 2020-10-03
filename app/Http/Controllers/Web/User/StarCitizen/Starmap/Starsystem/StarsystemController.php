<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User\StarCitizen\Starmap\Starsystem;

use App\Http\Controllers\Controller;
use App\Models\Api\StarCitizen\Starmap\Starsystem\Starsystem;
use Illuminate\View\View;

/**
 * Class AdminStarmapController
 */
class StarsystemController extends Controller
{
    /**
     * @return View
     */
    public function index(): View
    {
        return view(
            'user.starmap.systems.list',
            [
                'systems' => Starsystem::query()->orderBy('code')->get(),
            ]
        );
    }
}
