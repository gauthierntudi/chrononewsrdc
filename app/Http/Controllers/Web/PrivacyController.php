<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class PrivacyController extends Controller
{
    public function index(): View
    {
        return view('pages.privacy.index');
    }
}
