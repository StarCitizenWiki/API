<?php declare(strict_types = 1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Traits\ProfilesMethodsTrait;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;

/**
 * Class APIPageController
 *
 * @package App\Http\Api
 */
class PageController extends Controller
{
    use ProfilesMethodsTrait;

    /**
     * Returns the API Index View
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function showApiView(): View
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return view('api.pages.index');
    }

    /**
     * Returns the API FAQ View
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function showFaqView(): View
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return view('api.pages.faq');
    }

    /**
     * @return \Illuminate\Contracts\View\View
     */
    public function showStatusView(): View
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        Cache::put(
            get_cache_key_for_current_request(),
            Notification::query()
                ->where('output_status', true)
                ->orderByDesc('published_at')
                ->orderBy('expired_at')
                ->simplePaginate(4),
            CACHE_TIME
        );

        return view('api.pages.status')->with('notifications', Cache::get(get_cache_key_for_current_request()));
    }
}
