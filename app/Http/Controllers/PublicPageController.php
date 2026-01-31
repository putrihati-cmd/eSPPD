<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PublicPageController extends Controller
{
    public function about(): View
    {
        return view('public.about');
    }

    public function guide(): View
    {
        return view('public.guide');
    }
}
