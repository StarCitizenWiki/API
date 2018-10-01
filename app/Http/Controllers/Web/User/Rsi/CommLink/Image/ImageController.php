<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\User\Rsi\CommLink\Image;

use App\Http\Controllers\Controller;
use App\Models\Rsi\CommLink\Image\Image;

class ImageController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
    }

    public function index()
    {
        $this->authorize('web.user.rsi.comm-links.images.view');

        $images = Image::query()->paginate(20);

        return view(
            'user.rsi.comm_links.images.index',
            [
                'images' => $images,
            ]
        );
    }
}
