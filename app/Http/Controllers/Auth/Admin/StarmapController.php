<?php declare(strict_types = 1);

namespace App\Http\Controllers\Auth\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\DownloadStarmapData;
use App\Models\CelestialObject;
use App\Models\Starsystem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Class AdminStarmapController
 * @package App\Http\Controllers\Auth\Admin
 */
class StarmapController extends Controller
{
    /**
     * @return View
     */
    public function showStarmapSystemsView(): View
    {

        return view('admin.starmap.systems.index')->with(
            'systems',
            Starsystem::orderBy('code')->get()
        );
    }

    /**
     * @return View
     */
    public function showStarmapCelestialObjectView() : View
    {
        Log::debug('Starmap Celestial Objects View requested', [
            'method' => __METHOD__,
        ]);

        return view('admin.starmap.celestialobjects.index')->with(
            'celestialobjects',
            CelestialObject::orderBy('code')->get()
        );
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function updateStarmapSystem(Request $request): RedirectResponse
    {
        $this->validate(
            $request,
            [
                'id'      => 'required|exists:starsystems|int',
                'code'    => 'required|regex:/[A-Z0-9\'-]/',
                'exclude' => 'nullable',
            ]
        );

        $system = Starsystem::findOrFail($request->id);
        Log::notice(
            'Starmap System updated',
            [
                'updated_by'  => Auth::id(),
                'code_old'    => $system->code,
                'code_new'    => $request->code,
                'exclude_old' => $system->exclude,
                'exclude_new' => $request->exclude === "1",
            ]
        );
        $system->code = $request->code;
        $system->exclude = $request->exclude === "1";
        $system->save();

        return redirect()->route('admin_starmap_systems_list');
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function deleteStarmapSystem(Request $request): RedirectResponse
    {
        $this->validate(
            $request,
            [
                'id' => 'required|exists:starsystems|int',
            ]
        );

        $system = Starsystem::findOrFail($request->id);
        Log::notice(
            'Starmap System deleted',
            [
                'deleted_by' => Auth::id(),
                'system_id'  => $system->id,
                'code'       => $system->code,
                'exclude'    => $system->exclude,
            ]
        );
        $system->delete();

        return redirect()->route('admin_starmap_systems_list');
    }

    /**
     * @return RedirectResponse
     */
    public function downloadStarmap(): RedirectResponse
    {
        $this->dispatch(new DownloadStarmapData());

        return redirect()->back()->with(
            'success',
            ['Starmap Download Queued']
        );
    }
}
