<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User\Rsi\CommLink\Image;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rsi\CommLink\Image\NewImageTagRequest;
use App\Http\Requests\Rsi\CommLink\Image\ImageUploadRequest;
use App\Http\Requests\Rsi\CommLink\Image\TagUpdateRequest;
use App\Models\Rsi\CommLink\Image\Image;
use App\Models\Rsi\CommLink\Image\ImageMetadata;
use App\Models\Rsi\CommLink\Image\Tag;
use App\Services\UploadWikiImage;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Class ImageController
 */
class TagController extends Controller
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
     * @param Request $request
     *
     * @return Factory|View
     *
     * @throws AuthorizationException
     */
    public function index(Request $request)
    {
        $this->authorize('web.user.rsi.comm-links.view');

        return view(
            'user.rsi.comm_links.tags.index',
            [
                'tags' => Tag::query()->orderByDesc('images_count')->paginate(250),
            ]
        );
    }

    /**
     * @param Tag $tag
     *
     * @return Factory|View
     *
     * @throws AuthorizationException
     */
    public function edit(Tag $tag)
    {
        $this->authorize('web.user.rsi.comm-links.view');

        return view(
            'user.rsi.comm_links.tags.edit-tag',
            [
                'tag' => $tag,
            ]
        );
    }

    /**
     * @param Tag $tag
     * @param TagUpdateRequest $request
     *
     * @return Factory|View
     *
     * @throws AuthorizationException
     */
    public function update(Tag $tag, TagUpdateRequest $request): Factory|View
    {
        $this->authorize('web.user.rsi.comm-links.view');
        $data = $request->validated();

        $tag->update([
            #'name' => $data['name'],
            'name_en' => $data['name_en'],
        ]);

        return redirect()->route('web.user.rsi.comm-links.image-tags.index')->withMessages(
            [
                'success' => [
                    __('Tag aktualisiert'),
                ],
            ]
        );
    }

    /**
     * Creates a new image tag
     *
     * @param NewImageTagRequest $request
     * @return mixed
     */
    public function post(NewImageTagRequest $request)
    {
        Tag::query()->create([
            'name' => $request->name,
        ]);

        return redirect()->route('web.user.rsi.comm-links.image-tags.index')->withMessages(
            [
                'success' => [
                    __('Tag erstellt'),
                ],
            ]
        );
    }
}
