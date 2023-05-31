<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User\Transcript;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transcript\TranscriptUpdateRequest;
use App\Models\Transcript\Transcript;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class TranscriptController extends Controller
{
    private const TRANSCRIPT_UPDATE_PERMISSION = 'web.user.transcripts.update';

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
        $this->authorize('web.user.transcripts.index');

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

        $validated = $request->validated();
        $transcript->title = trim($validated['title']);
        $transcript->playlist_name = trim($validated['playlist_name']);
        $transcript->save();

        $message = __('crud.updated', ['type' => __('Transkript')]);

        return redirect()->route('web.user.transcripts.edit', $transcript->getRouteKey())->withMessages(
            [
                'success' => [
                    $message,
                ],
            ]
        );
    }
}
