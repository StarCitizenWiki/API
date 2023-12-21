<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User\Rsi\CommLink\Image;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rsi\CommLink\Image\NewImageTagRequest;
use App\Http\Requests\Rsi\CommLink\Image\ImageUploadRequest;
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

        return view(
            'user.rsi.comm_links.tags.index',
            [
                'tags' => Tag::query()->paginate(250),
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
