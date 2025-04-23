<?php

namespace Tocaan\Dukan\Http\Controllers;

use Illuminate\Routing\Controller;

class DukanController extends Controller
{
    public function index()
    {
        return view('dukan::welcome');
    }
}
