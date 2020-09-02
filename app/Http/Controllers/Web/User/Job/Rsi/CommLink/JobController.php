<?php declare(strict_types = 1);

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
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function startCommLinkTranslationJob()
    {
        $this->authorize('web.user.jobs.start_translation');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        Artisan::call('translate:comm-links');

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
    public function startCommLinkImageDownloadJob()
    {
        $this->authorize('web.user.jobs.start_image_download');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        Artisan::call('download:comm-link-images');

        return redirect()->route(self::DASHBOARD_ROUTE)->withMessages(
            [
                'success' => [
                    __('Download gestartet'),
                ],
            ]
        );
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function startCommLinkDownloadJob(Request $request)
    {
        $this->authorize('web.user.jobs.start_download');
        app('Log')::debug(make_name_readable(__FUNCTION__));

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
                return (int) $id;
            }
        )->filter(
            static function (int $id) {
                return $id >= 12663;
            }
        );

        Artisan::call(
            'download:comm-link',
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
