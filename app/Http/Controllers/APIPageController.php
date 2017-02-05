<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class APIPageController extends Controller
{
    public function getIndex()
    {
        $bootstrapModules = [
            'enableCSS' =>  true,
            'enableJS' =>  false
        ];
        return view('api.index', compact('bootstrapModules'));
    }
}
