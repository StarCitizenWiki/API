<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\Admin\Rsi\CommLink;

use App\Http\Controllers\Controller;
use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Http\Request;

class CommLinkController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $links = CommLink::orderByDesc('cig_id')->paginate(20);

        return view(
            'admin.rsi.comm_links.index',
            [
                'commLinks' => $links,
            ]
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Rsi\CommLink\CommLink $commLink
     *
     * @return \Illuminate\Http\Response
     */
    public function show(CommLink $commLink)
    {
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
     */
    public function edit(CommLink $commLink)
    {
        return view(
            'admin.rsi.comm_links.edit',
            [
                'content' => nl2br(base64_decode($commLink->english()->translation)),
                'images' => $commLink->images,
                'links' => $commLink->links,
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int                      $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }
}
