<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User\Transcript;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transcript\TranscriptStoreRequest;
use App\Http\Requests\Transcript\TranscriptUpdateRequest;
use App\Models\Rsi\Video\VideoFormat;
use App\Models\Transcript\Transcript;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class TranscriptController extends Controller
{
    private const TRANSCRIPT_UPDATE_PERMISSION = 'web.user.transcripts.update';
    private const LOCALE_EN = 'en_EN';
    private const LOCALE_DE = 'de_DE';
    private const TITLE = 'title';
    private const YOUTUBE_URL = 'youtube_url';
    private const PUBLISHED_AT = 'published_at';
    private const WIKI_ID = 'wiki_id';

    /**
     * CommLinkController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return View
     *
     * @throws AuthorizationException
     */
    public function index(): View
    {
        $this->authorize('web.user.transcripts.view');

        $transcripts = Transcript::query()->orderBy('id')->paginate(500);

        return view(
            'user.transcripts.index',
            [
                'transcripts' => $transcripts,
            ]
        );
    }

    /**
     * Display the specified resource.
     *
     * @param Transcript $transcript
     *
     * @return Factory|View
     *
     * @throws AuthorizationException
     */
    public function show(Transcript $transcript)
    {
        $this->authorize('web.user.transcripts.view');

        return view(
            'user.transcripts.show',
            [
                'transcript' => $transcript,
                'prev' => $transcript->getPrevAttribute(),
                'next' => $transcript->getNextAttribute(),
            ]
        );
    }

    /**
     * Creation Mask.
     *
     * @return View
     *
     * @throws AuthorizationException
     */
    public function create(): View
    {
        $this->authorize('web.user.transcripts.create');

        return view(
            'user.transcripts.create',
            [
                'formats' => VideoFormat::query()->orderBy('name')->get(),
            ]
        );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Transcript $transcript
     *
     * @return View
     *
     * @throws AuthorizationException
     */
    public function edit(Transcript $transcript): View
    {
        $this->authorize(self::TRANSCRIPT_UPDATE_PERMISSION);

        return view(
            'user.transcripts.edit',
            [
                'transcript' => $transcript,
                'formats' => VideoFormat::query()->orderBy('name')->get(),
                'prev' => $transcript->getPrevAttribute(),
                'next' => $transcript->getNextAttribute(),
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param TranscriptUpdateRequest $request
     * @param Transcript              $transcript
     *
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function update(TranscriptUpdateRequest $request, Transcript $transcript): RedirectResponse
    {
        $this->authorize(self::TRANSCRIPT_UPDATE_PERMISSION);

        $transcript->update(
            $this->getUpdateDataArray($request->validated())
        );

        $message = __('crud.updated', ['type' => __('Transkript')]);

        return redirect()->route('web.user.transcripts.edit', $transcript->getRouteKey())->withMessages(
            [
                'success' => [
                    $message,
                ],
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param TranscriptStoreRequest $request
     *
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function store(TranscriptStoreRequest $request): RedirectResponse
    {
        $this->authorize('web.user.transcripts.create');

        $data = $request->validated();

        $transcript = new Transcript(
            [
                'source_title' => $data['source_title'],
                'source_url' => $data['source_url'],
                'source_published_at' => $data['source_published_at'],
            ] + $this->getUpdateDataArray($data)
        );
        $transcript->save();

        if (isset($data[self::LOCALE_EN])) {
            $transcript->translations()->create(
                [
                    'locale_code' => self::LOCALE_EN,
                    'translation' => $data[self::LOCALE_EN],
                ]
            );
        }

        if (isset($data[self::LOCALE_DE])) {
            $transcript->translations()->create(
                [
                    'locale_code' => self::LOCALE_DE,
                    'translation' => $data[self::LOCALE_DE],
                ]
            );
        }

        $message = __('crud.created', ['type' => __('Transkript')]);

        return redirect()->route('web.user.transcripts.index', $transcript->getRouteKey())->withMessages(
            [
                'success' => [
                    $message,
                ],
            ]
        );
    }

    /**
     * Array data used in store / update
     *
     * @param array $data
     * @return array
     */
    private function getUpdateDataArray(array $data): array
    {
        return [
            self::TITLE => $data[self::TITLE],
            self::YOUTUBE_URL => $data[self::YOUTUBE_URL],
            self::PUBLISHED_AT => $data[self::PUBLISHED_AT],
            self::WIKI_ID => $data[self::WIKI_ID],
            'format_id' => $data['format'],
        ];
    }
}
