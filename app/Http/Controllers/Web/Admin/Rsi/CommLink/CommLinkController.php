<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\Admin\Rsi\CommLink;

use App\Http\Controllers\Controller;
use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Http\Request;

/**
 * Comm Link Controller
 */
class CommLinkController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('web.admin.rsi.comm_links.view');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $links = CommLink::orderByDesc('cig_id')->paginate(20);

        return view(
            'admin.rsi.comm_links.index',
            [
                'commLinks' => $links,
            ]
        );
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Rsi\CommLink\CommLink $commLink
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(CommLink $commLink)
    {
        $this->authorize('web.admin.rsi.comm_links.view');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view(
            'admin.rsi.comm_links.show',
            [
                'commLink' => $commLink,
            ]
        );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Rsi\CommLink\CommLink $commLink
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(CommLink $commLink)
    {
        $this->authorize('web.admin.rsi.comm_links.update');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view(
            'admin.rsi.comm_links.edit',
            [
                'commLink' => $commLink,
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request         $request
     * @param \App\Models\Rsi\CommLink\CommLink $commLink
     *
     * @return void
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, CommLink $commLink)
    {
        $this->authorize('web.admin.rsi.comm_links.update');
        app('Log')::debug(make_name_readable(__FUNCTION__));
        //
    }
}
