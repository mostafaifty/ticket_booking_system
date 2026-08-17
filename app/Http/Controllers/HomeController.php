<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the home landing page.
     */
    public function index(): View
    {
        return view('home');
    }
}
