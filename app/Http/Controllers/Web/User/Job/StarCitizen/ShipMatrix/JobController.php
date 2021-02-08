<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User\Job\StarCitizen\ShipMatrix;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;

class JobController extends Controller
{
    private const DASHBOARD_ROUTE = 'web.user.dashboard';

    /**
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function startCommLinkWikiPageCreationJob(): RedirectResponse
    {
        $this->authorize('web.user.jobs.start_ship_matrix_download');

        Artisan::call('ship-matrix:download --import');

        return redirect()->route(self::DASHBOARD_ROUTE)->withMessages(
            [
                'success' => [
                    __('Download gestartet'),
                ],
            ]
        );
    }
}
