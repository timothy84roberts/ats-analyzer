<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class AtsAnalysisController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:use-ats-lab');
    }

    public function index(): View
    {
        return view('ats.index');
    }
}
