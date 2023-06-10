<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User\Rsi\CommLink\Image;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rsi\CommLink\Image\ImageUploadRequest;
use App\Models\Rsi\CommLink\Image\Image;
use App\Models\Rsi\CommLink\Image\ImageMetadata;
use App\Services\UploadWikiImage;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
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
     * @param Request $request
     *
     * @return Factory|View
     *
     * @throws AuthorizationException
     */
    public function index(Request $request)
    {
        $this->authorize('web.user.rsi.comm-links.view');

        $query = Image::query()
            ->where('dir', 'NOT LIKE', 'NOT_FOUND');

        if ($request->get('mime', null) !== null) {
            $query->whereHas(
                'metadata',
                function (Builder $query) use ($request) {
                    return $query->where('mime', '=', $request->get('mime'));
                }
            );
        }

        return view(
            'user.rsi.comm_links.images.index',
            [
                'images' => $query
                    ->orderByDesc('id')
                    ->groupBy('src')
                    ->paginate(50),
                'mimes' => ImageMetadata::query()->groupBy('mime')->get('mime'),
            ]
        );
    }

    /**
     * @param ImageUploadRequest $request
     *
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws AuthorizationException
     */
    public function upload(ImageUploadRequest $request): string
    {
        $this->authorize('web.user.rsi.comm-links.view');

        $params = $request->validated();

        $uploader = new UploadWikiImage();

        return $uploader->uploadCommLinkImage($params);
    }
}
