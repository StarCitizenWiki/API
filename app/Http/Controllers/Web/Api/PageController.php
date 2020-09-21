<?php declare(strict_types=1);

namespace App\Http\Controllers\Web\Api;

use App\Http\Controllers\Controller;
use App\Models\Api\Notification;
use Illuminate\Contracts\View\View;

/**
 * Class APIPageController
 */
class PageController extends Controller
{
    /**
     * Returns the API Index View
     *
     * @return View
     */
    public function index(): View
    {
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $notifications = Notification::published()
            ->onFrontPage()
            ->notExpired()
            ->orderBy('order')
            ->orderByDesc('created_at')
            ->orderBy('expired_at')
            ->take(3)
            ->get();

        return view(
            'api.pages.index',
            [
                'notifications' => $notifications,
            ]
        );
    }

    /**
     * Returns the API FAQ View
     *
     * @return View
     */
    public function showFaqView(): View
    {
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view('api.pages.faq');
    }

    /**
     * @return View
     */
    public function showStatusView(): View
    {
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $notifications = Notification::published()
            ->notExpired()
            ->onStatusPage()
            ->orderByDesc('published_at')
            ->simplePaginate(4);


        return view(
            'api.pages.status',
            [
                'notifications' => $notifications,
            ]
        );
    }
}
