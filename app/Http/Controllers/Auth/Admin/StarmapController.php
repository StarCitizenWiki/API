<?php

namespace App\Http\Controllers\Auth\Admin;

use App\Models\Starsystem;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
        return view('admin.starmap.systems.index')->with('systems', Starsystem::orderBy('code')->get());
    }

    /**
     * @param String $code Starmap System Code
     *
     * @return View
     */
    public function showEditStarmapSystemsView(String $code) : View
    {
        return view('admin.starmap.systems.edit')->with('system', Starsystem::where('code', $code)->first());
    }

    /**
     * @return View
     */
    public function showAddStarmapSystemsView() : View
    {
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
            'code' => 'required|regex:/[A-Z\']/',
        ]);

        $system = Starsystem::findOrFail($request->id);
        Log::info('Starmap System updated', [
            'updated_by' => Auth::id(),
            'code_old' => $system->code,
            'code_new' => $request->code,
        ]);
        $system->code = $request->code;
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
        Log::info('Starmap System deleted', [
            'deleted_by' => Auth::id(),
            'system_id' => $system->id,
            'code' => $system->code,
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
            'code' => 'required|regex:/[A-Z\']/',
        ]);

        Starsystem::create([
            'code' => $request->code,
        ]);
        Log::info('Starmap System added', [
            'added_by' => Auth::id(),
            'system_code' => $request->code,
        ]);

        return redirect()->route('admin_starmap_systems_list');
    }
}
