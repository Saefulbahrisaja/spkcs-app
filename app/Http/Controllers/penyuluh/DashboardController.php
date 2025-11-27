<?php

namespace App\Http\Controllers\Penyuluh;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('penyuluh.dashboard');
    }
}
