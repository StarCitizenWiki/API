<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User\Rsi\CommLink\Image;

use App\Http\Controllers\Api\V2\Rsi\CommLink\CommLinkSearchController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Rsi\CommLink\Image\AddImageTagsRequest;
use App\Http\Requests\Rsi\CommLink\Image\ImageUploadRequest;
use App\Models\Rsi\CommLink\Image\Image;
use App\Models\Rsi\CommLink\Image\ImageMetadata;
use App\Models\Rsi\CommLink\Image\Tag;
use App\Services\UploadWikiImage;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use JsonException;

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
        $this->middleware('auth')->only('upload');
    }

    /**
     * All downloaded Images, excluding those that could not be found
     *
     * @param Request $request
     *
     * @return Factory|View
     */
    public function index(Request $request)
    {
        $query = Image::query()
            ->where('dir', 'NOT LIKE', 'NOT_FOUND')
            ->whereNull('base_image_id');

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
                    ->where('local', 1)
                    ->orderByDesc('id')
                    ->groupBy('src')
                    ->paginate(50),
                'mimes' => ImageMetadata::query()->groupBy('mime')->get('mime'),
            ]
        );
    }

    /**
     * @param Request $request
     *
     * @return Factory|View
     */
    public function indexByTag(Request $request)
    {
        $tag = Tag::query()->where('name', $request->tag)->orWhere('name_en', $request->tag)->first();

        return view(
            'user.rsi.comm_links.images.index',
            [
                'images' => optional($tag)->images ?? [],
            ]
        );
    }

    /**
     * @param Image $image
     *
     * @return Factory|View
     */
    public function show(Image $image)
    {
        return view(
            'user.rsi.comm_links.images.show',
            [
                'image' => $image
            ]
        );
    }

    /**
     * @param ImageUploadRequest $request
     *
     * @return string
     *
     * @throws GuzzleException
     * @throws AuthorizationException|JsonException
     */
    public function upload(ImageUploadRequest $request): string
    {
        $this->authorize('web.user.rsi.comm-links.view');

        $params = $request->validated();

        $uploader = new UploadWikiImage();

        return $uploader->uploadCommLinkImage($params);
    }

    /**
     * Retrieve similar images based on a hash
     *
     * @param Request $request
     *
     * @return View
     */
    public function similarImages(Request $request): View
    {
        $controller = new CommLinkSearchController($request);

        return view(
            'user.rsi.comm_links.images.index',
            [
                'images' => $controller->similarSearch($request),
            ]
        );
    }

    /**
     * View for editing the tags of an image
     *
     * @param Image $image
     *
     * @return View
     */
    public function editTags(Image $image): View
    {
        return view(
            'user.rsi.comm_links.tags.edit',
            [
                'tags' => Tag::query()->orderByDesc('name')->get(),
                'image' => $image,
                'image_tags' => $image->tags->map(fn(Tag $tag) => $tag->name),
            ]
        );
    }

    /**
     * Save tags to an image
     *
     * @param Image $image
     * @param AddImageTagsRequest $request
     *
     * @return RedirectResponse
     */
    public function saveTags(Image $image, AddImageTagsRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $ids = collect($data['tags'])->map(function (string $datum) {
            if (str_starts_with($datum, 'id:')) {
                return (int) str_replace('id:', '', $datum);
            }

            return Tag::query()->firstOrCreate(['name' => $datum])->id;
        });

        $image->tags()->sync($ids);

        return redirect()->back();
    }
}
