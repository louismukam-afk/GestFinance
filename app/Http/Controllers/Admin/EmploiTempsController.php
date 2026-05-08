<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class EmploiTempsController extends Controller
{
    public function index()
    {
        return view('Admin.EmploiTemps.index', [
            'title' => 'Emploi du temps',
        ]);
    }
}
