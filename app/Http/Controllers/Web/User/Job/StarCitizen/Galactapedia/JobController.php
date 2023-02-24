<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User\Job\StarCitizen\Galactapedia;

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
    public function startImportGalactapediaCategoriesJob(): RedirectResponse
    {
        $this->authorize('web.user.jobs.import_galactapedia_job');

        Artisan::call('galactapedia:import-categories');

        return redirect()->route(self::DASHBOARD_ROUTE)->withMessages(
            [
                'success' => [
                    __('Galactapedia Kategorieimport gestartet'),
                ],
            ]
        );
    }

    /**
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function startImportGalactapediaArticlesJob(): RedirectResponse
    {
        $this->authorize('web.user.jobs.import_galactapedia_job');

        Artisan::call('galactapedia:import-articles');

        return redirect()->route(self::DASHBOARD_ROUTE)->withMessages(
            [
                'success' => [
                    __('Galactapedia Artikelimport gestartet'),
                ],
            ]
        );
    }

    /**
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function startImportGalactapediaArticlePropertiesJob(): RedirectResponse
    {
        $this->authorize('web.user.jobs.import_galactapedia_job');

        Artisan::call('galactapedia:import-properties');

        return redirect()->route(self::DASHBOARD_ROUTE)->withMessages(
            [
                'success' => [
                    __('Galactapedia Import der Artikeleigenschaften gestartet'),
                ],
            ]
        );
    }

    /**
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function startCreateWikiPagesJob(): RedirectResponse
    {
        $this->authorize('web.user.jobs.import_galactapedia_job');

        Artisan::call('galactapedia:create-wiki-pages');
        Artisan::call('galactapedia:upload-wiki-images');

        return redirect()->route(self::DASHBOARD_ROUTE)->withMessages(
            [
                'success' => [
                    __('Galactapedia Wiki Seiten werden erstellt'),
                ],
            ]
        );
    }
}
