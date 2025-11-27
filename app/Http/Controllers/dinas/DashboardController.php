<?php

namespace App\Http\Controllers\Dinas;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dinas.dashboard');
    }
}
