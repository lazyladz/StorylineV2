<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        return view('welcome'); // Your landing page
    }

    public function browse()
    {
        return view('browse'); // We'll create this later
    }
}