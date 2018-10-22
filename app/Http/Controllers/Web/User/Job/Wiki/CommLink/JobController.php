<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\User\Job\Wiki\CommLink;

use App\Http\Controllers\Controller;
use App\Jobs\Wiki\CommLink\UpdateCommLinkProofReadStatus;
use Illuminate\Support\Facades\Artisan;

/**
 * Comm Link Wiki Jobs
 */
class JobController extends Controller
{
    private const DASHBOARD_ROUTE = 'web.user.dashboard';

    /**
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function startCommLinkWikiPageCreationJob()
    {
        $this->authorize('web.user.jobs.start_wiki_page_creation');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        Artisan::call('wiki:create-comm-link-pages');

        return redirect()->route(self::DASHBOARD_ROUTE)->withMessages(
            [
                'success' => [
                    __('Seitenerstellung gestartet'),
                ],
            ]
        );
    }

    /**
     * Update Comm-Link Proofread Status
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function startCommLinkProofReadStatusUpdateJob()
    {
        $this->authorize('web.user.jobs.start_proofread_update');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $this->dispatch(new UpdateCommLinkProofReadStatus());

        return redirect()->route(self::DASHBOARD_ROUTE)->withMessages(
            [
                'success' => [
                    __('Update gestartet'),
                ],
            ]
        );
    }
}
