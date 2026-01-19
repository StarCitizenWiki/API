<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class FailedJobController extends Controller
{
    public function index(): View
    {
        $jobs = DB::table('failed_jobs')
            ->orderBy('failed_at', 'desc')
            ->paginate(25);

        return view('admin.jobs.index', compact('jobs'));
    }

    public function destroy(int $id): RedirectResponse
    {
        DB::table('failed_jobs')->where('id', $id)->delete();

        return redirect()->route('admin.jobs.index')
            ->with('success', 'Failed job deleted successfully.');
    }

    public function truncate(): RedirectResponse
    {
        DB::table('failed_jobs')->truncate();

        return redirect()->route('admin.jobs.index')
            ->with('success', 'All failed jobs have been deleted.');
    }
}
