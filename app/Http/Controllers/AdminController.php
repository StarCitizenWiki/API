<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function users()
    {
        return view('admin.users')->with('users', User::all());
    }

    public function routes()
    {
        return view('admin.routes');
    }
}
