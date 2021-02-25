<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User\Job\StarCitizen\Vehicle;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;

class JobController extends Controller
{
    private const DASHBOARD_ROUTE = 'web.user.dashboard';

    /**
     * JobController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
    }

    /**
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function startDownloadShipMatrixJob(): RedirectResponse
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

    /**
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function startMsrpImportJob(): RedirectResponse
    {
        $this->authorize('web.user.jobs.start_msrp_import');

        Artisan::call('vehicles:import-msrp');

        return redirect()->route(self::DASHBOARD_ROUTE)->withMessages(
            [
                'success' => [
                    __('Import gestartet'),
                ],
            ]
        );
    }
}
