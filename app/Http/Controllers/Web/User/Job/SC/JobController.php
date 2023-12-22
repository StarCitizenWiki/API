<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User\Job\SC;

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
    public function startItemImportJob(): RedirectResponse
    {
        $this->authorize('web.user.jobs.sc-import');

        Artisan::call('sc:import-items');

        return redirect()->route(self::DASHBOARD_ROUTE)->withMessages(
            [
                'success' => [
                    __('Import gestartet'),
                ],
            ]
        );
    }

    /**
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function startVehicleImportJob(): RedirectResponse
    {
        $this->authorize('web.user.jobs.sc-import');

        Artisan::call('sc:import-vehicles');

        return redirect()->route(self::DASHBOARD_ROUTE)->withMessages(
            [
                'success' => [
                    __('Import gestartet'),
                ],
            ]
        );
    }

    /**
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function startShopImportJob(): RedirectResponse
    {
        $this->authorize('web.user.jobs.sc-import');

        Artisan::call('sc:import-shops');

        return redirect()->route(self::DASHBOARD_ROUTE)->withMessages(
            [
                'success' => [
                    __('Import gestartet'),
                ],
            ]
        );
    }

    /**
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function startImageUploadJob(): RedirectResponse
    {
        $this->authorize('web.user.jobs.sc-import');

        Artisan::call('sc:upload-images');

        return redirect()->route(self::DASHBOARD_ROUTE)->withMessages(
            [
                'success' => [
                    __('Bild Upload gestartet'),
                ],
            ]
        );
    }

    /**
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function startTranslateJob(): RedirectResponse
    {
        $this->authorize('web.user.jobs.sc-import');

        Artisan::call('sc:translate');

        return redirect()->route(self::DASHBOARD_ROUTE)->withMessages(
            [
                'success' => [
                    __('Übersetzung gestartet'),
                ],
            ]
        );
    }
}
