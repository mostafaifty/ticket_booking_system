<?php

namespace App\Http\Controllers;

use App\Models\Station;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the home landing page with quick search.
     */
    public function index(): View
    {
        $stations = Station::orderBy('name')->get();

        return view('home', compact('stations'));
    }
}
