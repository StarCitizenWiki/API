<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User\Job\Wiki\CommLink;

use App\Http\Controllers\Controller;
use App\Jobs\Wiki\CommLink\UpdateCommLinkProofReadStatus;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;

/**
 * Comm Link Wiki Jobs
 */
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
        $this->authorize('web.user.jobs.start_wiki_page_creation');

        Artisan::call('comm-links:create-wiki-pages');

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
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function startCommLinkProofReadStatusUpdateJob(): RedirectResponse
    {
        $this->authorize('web.user.jobs.start_proofread_update');

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
