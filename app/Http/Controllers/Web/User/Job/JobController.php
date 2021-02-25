<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User\Job;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class JobController extends Controller
{
    /**
     * JobController constructor.
     *
     * @throws AuthorizationException
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
    }

    public function viewFailed(): View
    {
        $this->authorize('web.user.jobs.view_failed');

        return view('user.jobs.failed_index')
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
                        ->get(),
                ]
            );
    }
}
