<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Job;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class JobController extends Controller
{
    /**
     * JobController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
    }

    /**
     * View failed jobs
     *
     *
     * @throws AuthorizationException
     */
    public function viewFailed(): View
    {
        $this->authorize('web.jobs.view_failed');

        return view('web.jobs.failed_index')
            ->with(
                [
                    'failed' => DB::table('failed_jobs')
                        ->select([
                            'id',
                            'connection',
                            'queue',
                            'payload',
                            'exception',
                            'failed_at',
                        ])
                        ->orderByDesc('id')
                        ->get(),
                ]
            );
    }

    /**
     * Truncate the failed job table
     *
     *
     * @throws AuthorizationException
     */
    public function truncate(): RedirectResponse
    {
        $this->authorize('web.jobs.truncate');

        DB::table('failed_jobs')->truncate();

        return redirect()->route('web.dashboard')->withMessages(
            [
                'success' => [
                    __('Jobs gelöscht'),
                ],
            ]
        );
    }
}
