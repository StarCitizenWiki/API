<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User\Job\Wiki;

use App\Http\Controllers\Controller;
use App\Services\Gdoc\Parser\VehiclePriceParser;
use App\Traits\GetWikiCsrfTokenTrait;
use ErrorException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use StarCitizenWiki\MediaWikiApi\Facades\MediaWikiApi;

class GdocCsvController extends Controller
{
    use GetWikiCsrfTokenTrait;

    /**
     * The upload csv view
     *
     * @return View
     */
    public function view(): View
    {
        return view('user.jobs.wiki.upload_csv');
    }

    /**
     * Processes the upload and displays found data
     *
     * @param Request $request
     * @return View
     */
    public function upload(Request $request): View
    {
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
     */
    public function uploadWiki()
    {
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

        $path = sprintf('tmp%supload.csv', DIRECTORY_SEPARATOR);

        if (!Storage::exists($path)) {
            return $this->view()->with('errors', collect(['Invalid file']));
        }

        $parser = new VehiclePriceParser(Storage::path($path));
        $parser->parse();

        if ($parser->getBuyables()->isEmpty() || $parser->getRentables()->isEmpty()) {
            return $this->view()
                ->with('errors', collect(['Die Datei scheint fehlerhafte Daten zu beinhalten. Es wurden keine kaufbaren oder mietbaren Fahrzeuge gefunden']));
        }

        try {
            MediaWikiApi::edit('Raumschiffe/Preisdaten')
                ->text(sprintf($format, $parser->toSubObjects()))
                ->csrfToken($this->getCsrfToken('wiki_upload_image'))
                ->request();
        } catch (ErrorException | GuzzleException $e) {
            return $this->view()->with('errors', collect([$e->getMessage()]));
        }

        Storage::delete($path);

        return redirect()->route('web.user.dashboard', [
            'message' => 'Upload erfolgreich.'
        ]);
    }
}
