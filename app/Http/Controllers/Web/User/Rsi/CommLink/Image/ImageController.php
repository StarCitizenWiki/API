<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\User\Rsi\CommLink\Image;

use App\Http\Controllers\Controller;
use App\Models\Rsi\CommLink\Image\Image;

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
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('web.user.rsi.comm-links.images.view');

        $images = Image::query()->where('dir', 'NOT LIKE', 'NOT_FOUND')->paginate(20);

        return view(
            'user.rsi.comm_links.images.index',
            [
                'images' => $images,
            ]
        );
    }
}
