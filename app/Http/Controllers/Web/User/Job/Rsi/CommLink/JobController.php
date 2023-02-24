<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User\Job\Rsi\CommLink;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

/**
 * Comm Link Api Jobs
 */
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
    public function startCommLinkTranslationJob(): RedirectResponse
    {
        $this->authorize('web.user.jobs.start_translation');

        Artisan::call('comm-links:translate', ['modifiedTime' => -1]);

        return redirect()->route(self::DASHBOARD_ROUTE)->withMessages(
            [
                'success' => [
                    __('Übersetzung gestartet'),
                ],
            ]
        );
    }

    /**
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function startCommLinkImageDownloadJob(): RedirectResponse
    {
        $this->authorize('web.user.jobs.start_image_download');

        Artisan::call('comm-links:download-images');

        return redirect()->route(self::DASHBOARD_ROUTE)->withMessages(
            [
                'success' => [
                    __('Download gestartet'),
                ],
            ]
        );
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function startCommLinkDownloadJob(Request $request): RedirectResponse
    {
        $this->authorize('web.user.jobs.start_download');

        $data = $request->validate(
            [
                'ids' => 'required|string|min:5',
            ]
        );

        $ids = collect(explode(',', $data['ids']))->map(
            static function ($id) {
                return trim($id);
            }
        )->filter(
            static function ($id) {
                return is_numeric($id);
            }
        )->map(
            static function ($id) {
                return (int)$id;
            }
        )->filter(
            static function (int $id) {
                return $id >= 12663;
            }
        );

        Artisan::call(
            'comm-links:download',
            [
                'id' => $ids->toArray(),
                '--import' => true,
            ]
        );

        return redirect()->route(self::DASHBOARD_ROUTE)->withMessages(
            [
                'success' => [
                    __('Comm-Link Download gestartet'),
                ],
            ]
        );
    }
}
