<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class APIPageController extends Controller
{
    /**
     * @return View
     */
    public function index()
    {
        return view('api.index');
    }
}
