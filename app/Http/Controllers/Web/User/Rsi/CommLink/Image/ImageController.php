<?php declare(strict_types=1);

namespace App\Http\Controllers\Web\User\Rsi\CommLink\Image;

use App\Http\Controllers\Controller;
use App\Models\Rsi\CommLink\Image\Image;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;

/**
 * Class ImageController
 */
class ImageController extends Controller
{
    /**
     * ImageController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
    }

    /**
     * All downloaded Images, excluding those that could not be found
     *
     * @return Factory|View
     *
     * @throws AuthorizationException
     */
    public function index()
    {
        $this->authorize('web.user.rsi.comm-links.view');

        return view(
            'user.rsi.comm_links.images.index',
            [
                'images' => Image::query()
                    ->where('dir', 'NOT LIKE', 'NOT_FOUND')
                    ->orderByDesc('id')
                    ->groupBy('src')
                    ->paginate(20),
            ]
        );
    }
}
