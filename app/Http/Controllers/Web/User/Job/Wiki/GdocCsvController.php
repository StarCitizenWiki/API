<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User\Job\Wiki;

use App\Http\Controllers\Controller;
use App\Services\Gdoc\Parser\VehiclePriceParser;
use App\Traits\GetWikiCsrfTokenTrait;
use ErrorException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use StarCitizenWiki\MediaWikiApi\Facades\MediaWikiApi;

class GdocCsvController extends Controller
{
    use GetWikiCsrfTokenTrait;

    /**
     * GdocCsvController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth');
    }

    /**
     * The upload csv view
     *
     * @return View
     * @throws AuthorizationException
     */
    public function view(): View
    {
        $this->authorize('web.user.jobs.upload_csv');

        return view('user.jobs.wiki.upload_csv');
    }

    /**
     * Processes the upload and displays found data
     *
     * @param Request $request
     * @return View
     * @throws AuthorizationException
     */
    public function upload(Request $request): View
    {
        $this->authorize('web.user.jobs.upload_csv');

        $request->validate([
            'file' => 'required|mimetypes:text/plain|max:2048'
        ]);

        $file = $request->file('file');

        if (!in_array($file->getMimeType(), ['text/plain', 'application/vnd.ms-excel', 'application/csv'], true)) {
            return $this->view()->with('errors', collect(['Invalid file']));
        }

        $path = $file->storeAs('tmp', 'upload.csv');

        if ($path === false) {
            return $this->view()->with('errors', collect(['Could not upload file']));
        }

        $parser = new VehiclePriceParser(Storage::path($path));
        $parser->parse();

        return view('user.jobs.wiki.result', [
            'buyables' => $parser->getBuyables(),
            'rentables' => $parser->getRentables(),
        ]);
    }

    /**
     * Writes the parsed data to the designated wiki page
     *
     * @return View|RedirectResponse
     * @throws AuthorizationException
     */
    public function uploadWiki()
    {
        $this->authorize('web.user.jobs.upload_csv');

        // phpcs:disable
        $format = <<<FORMAT
<noinclude>
{{Alert|color=info|title=Information|content=Diese Seite enthält Daten über Kauf- und Mietpreise von Fahrzeugen in Star Citizen.<br>Diese Daten werden automatisch durch die Star Citizen Wiki API verwaltet.}}<!--
START: Semantic MediaWiki SubObjects -->
%s
<!--
END: Semantic MediaWiki SubObjects -->
[[Kategorie:Instandhaltung]]
</noinclude>
FORMAT;
        // phpcs:enable

        $path = sprintf('tmp%supload.csv', DIRECTORY_SEPARATOR);

        if (!Storage::exists($path)) {
            return $this->view()->with('errors', collect(['Invalid file']));
        }

        $parser = new VehiclePriceParser(Storage::path($path));
        $parser->parse();

        if ($parser->getBuyables()->isEmpty() || $parser->getRentables()->isEmpty()) {
            return $this->view()
                ->with('errors', collect([
                    // phpcs:disable
                    'Die Datei scheint fehlerhafte Daten zu beinhalten. Es wurden keine kaufbaren oder mietbaren Fahrzeuge gefunden'
                    // phpcs:enable
                ]));
        }

        try {
            $token = $this->getCsrfTokenForUser(true);
            $response = MediaWikiApi::edit('Spieldaten/Fahrzeuge')
                ->withAuthentication()
                ->text(sprintf($format, $parser->toSubObjects()))
                ->csrfToken($token)
                ->summary('Updating Vehicle Prices')
                ->request();
        } catch (ErrorException | GuzzleException $e) {
            return $this->view()->with('errors', collect([$e->getMessage()]));
        }

        if ($response->hasErrors()) {
            return $this->view()->with('errors', collect($response->getErrors()));
        }

        Storage::delete($path);

        return redirect()->route('web.user.dashboard')->withMessages(
            [
                'success' => [
                    __('Import erfolgreich. Daten auf Seite "Spieldaten/Fahrzeuge" veröffentlicht.'),
                ],
            ]
        );
    }
}