<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Models\Account\User\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use StarCitizenWiki\DeepLy\Integrations\Laravel\DeepLyFacade;

/**
 * Class DashboardController
 */
class DashboardController extends Controller
{
    const DEEPL_STATS_CACHE_KEY = 'deepl_stats';

    /**
     * AdminController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
    }

    /**
     * Returns the Dashboard View
     *
     * @return \Illuminate\Contracts\View\View
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(): View
    {
        $this->authorize('web.user.dashboard.view');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view(
            'user.dashboard',
            [
                'users' => $this->getUserStats(),
                'deepl' => $this->getDeeplStats(),
                'jobs' => $this->getQueueStats(),
            ]
        );
    }

    /**
     * User Stats
     * New Registrations and Logins
     *
     * @return array
     */
    private function getUserStats()
    {
        $today = Carbon::today()->toDateString();

        return [
            'overall' => User::all()->count(),
            'last' => User::query()->take(5)->orderBy('created_at', 'desc')->get(),
            'registrations' => [
                'counts' => [
                    'last_hour' => User::query()->whereDate('created_at', '>', Carbon::now()->subHour())->count(),
                    'today' => User::query()->whereDate('created_at', '=', $today)->get()->count(),
                    'overall' => User::all()->count(),
                ],
            ],
            'logins' => [
                'counts' => [
                    'last_hour' => User::query()->whereDate('last_login', '>', Carbon::now()->subHour())->count(),
                    'today' => User::query()->whereDate('last_login', '=', $today)->get()->count(),
                    'overall' => User::all()->count(),
                ],
            ],
        ];
    }

    /**
     * Deepl Usage Stats
     *
     * @return array
     */
    private function getDeeplStats()
    {
        if (Cache::has(self::DEEPL_STATS_CACHE_KEY)) {
            return Cache::get(self::DEEPL_STATS_CACHE_KEY);
        }

        $deeplUsage = DeepLyFacade::getUsage()->getResponse();
        $width = ($deeplUsage['character_count'] / $deeplUsage['character_limit']) * 100;

        $style = 'bg-success';
        if ($width >= 85) {
            $style = 'bg-danger';
        } elseif ($width >= 75) {
            $style = 'bg-warning';
        } elseif ($width >= 50) {
            $style = 'bg-info';
        }

        $stats = [
            'usage' => [
                'limit' => $deeplUsage['character_limit'],
                'count' => $deeplUsage['character_count'],
            ],
            'bar' => [
                'width' => $width,
                'style' => $style,
            ],
        ];

        Cache::put(self::DEEPL_STATS_CACHE_KEY, $stats, 1);

        return $stats;
    }

    /**
     * Simple Queue Stat Counts
     *
     * @return array
     */
    private function getQueueStats()
    {
        $jobs = DB::table('jobs')->get();
        $jobsFailed = DB::table('failed_jobs')->count();

        $active = $jobs->filter(
            function (\stdClass $job) {
                return null !== $job->reserved_at;
            }
        );

        return [
            'all' => number_format($jobs->count(), 0, ',', '.'),
            'active' => number_format($active->count(), 0, ',', '.'),
            'failed' => number_format($jobsFailed, 0, ',', '.'),
        ];
    }
}
