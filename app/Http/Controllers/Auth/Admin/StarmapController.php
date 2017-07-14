<?php

namespace App\Http\Controllers\Auth\Admin;

use App\Facades\Log;
use App\Http\Controllers\Controller;
use App\Jobs\DownloadStarmapData;
use App\Models\Starsystem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
    public function showStarmapSystemsView() : View
    {
        Log::info(get_human_readable_name_from_view_function(__FUNCTION__), Auth::user()->getBasicInfoForLog());

        return view('admin.starmap.systems.index')->with(
            'systems',
            Starsystem::orderBy('code')->get()
        );
    }

    /**
     * @param String $code Starmap System Code
     *
     * @return View
     */
    public function showEditStarmapSystemsView(String $code) : View
    {
        Log::debug('Edit Starmap System View requested');

        $content = '';
        if (Storage::disk('starmap')->exists(Starsystem::makeFilenameFromCode($code))) {
            $content = Storage::disk('starmap')->get(Starsystem::makeFilenameFromCode($code));
        }

        return view('admin.starmap.systems.edit')->with('system', Starsystem::where('code', '=', $code)->first())
                                                       ->with('content', $content);
    }

    /**
     * @return View
     */
    public function showAddStarmapSystemsView() : View
    {
        Log::debug('Add Starmap System View requested');

        return view('admin.starmap.systems.add');
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function updateStarmapSystem(Request $request) : RedirectResponse
    {
        $this->validate($request, [
            'id' => 'required|exists:starsystems|int',
            'code' => 'required|regex:/[A-Z0-9\'-]/',
            'exclude' => 'nullable',
        ]);

        $system = Starsystem::findOrFail($request->id);
        Log::notice('Starmap System updated', [
            'updated_by' => Auth::id(),
            'code_old' => $system->code,
            'code_new' => $request->code,
            'exclude_old' => $system->exclude,
            'exclude_new' => $request->exclude === "1",
        ]);
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
    public function deleteStarmapSystem(Request $request) : RedirectResponse
    {
        $this->validate($request, [
            'id' => 'required|exists:starsystems|int',
        ]);

        $system = Starsystem::findOrFail($request->id);
        Log::notice('Starmap System deleted', [
            'deleted_by' => Auth::id(),
            'system_id' => $system->id,
            'code' => $system->code,
            'exclude' => $system->exclude,
        ]);
        $system->delete();

        return redirect()->route('admin_starmap_systems_list');
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function addStarmapSystem(Request $request) : RedirectResponse
    {
        $this->validate($request, [
            'code' => 'required|regex:/[A-Z0-9\'-]/',
            'exclude' => 'nullable',
        ]);

        $system = new Starsystem();
        $system->code = $request->code;
        $system->exclude = $request->exclude === "1";
        $system->save();

        Log::notice('Starmap System added', [
            'added_by' => Auth::id(),
            'system_code' => $request->code,
            'exclude' => $request->exclude === "1",
        ]);

        return redirect()->route('admin_starmap_systems_list');
    }

    /**
     * @return RedirectResponse
     */
    public function downloadStarmap() : RedirectResponse
    {
        self::startExecutionTimer();

        $this->dispatch(new DownloadStarmapData());

        self::endExecutionTimer();

        return redirect()->back()->with(
            'success',
            ['Starmap Download Queued']
        );
    }
}
