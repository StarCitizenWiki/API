<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Models\Account\User\User;
use App\Models\System\ModelChangelog;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use StarCitizenWiki\DeepLy\Integrations\Laravel\DeepLyFacade;

/**
 * Class DashboardController
 */
class DashboardController extends Controller
{
    private const DEEPL_STATS_CACHE_KEY = 'deepl_stats';
    private const DASHBOARD_ROUTE = 'web.user.dashboard';

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
                'changelogs' => ModelChangelog::query()->orderByDesc('id')->take(5),
            ]
        );
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function startCommLinkTranslationJob()
    {
        $this->authorize('web.user.jobs.start_translation');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        Artisan::call('translate:comm-links');

        return redirect()->route(self::DASHBOARD_ROUTE)->withMessages(
            [
                'success' => [
                    __('Überstzung gestartet'),
                ],
            ]
        );
    }

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
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
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
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
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
            function ($id) {
                return trim($id);
            }
        )->filter(
            function ($id) {
                return is_numeric($id);
            }
        )->map(
            function ($id) {
                return (int) $id;
            }
        )->filter(
            function (int $id) {
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
