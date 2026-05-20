<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Salle;
use Illuminate\Http\Request;

class SalleController extends Controller
{
    public function index()
    {
        return view('Admin.EmploiTemps.salles', [
            'title' => 'Salles',
            'salles' => Salle::orderBy('nom_salle')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom_salle' => 'required|string|max:255',
            'code_salle' => 'nullable|string|max:100',
            'capacite' => 'nullable|integer|min:0',
            'statut' => 'required|in:actif,inactif',
        ]);

        $data['id_user'] = auth()->id() ?? 0;
        Salle::create($data);

        return back()->with('success', 'Salle creee.');
    }

    public function update(Request $request, Salle $salle)
    {
        $data = $request->validate([
            'nom_salle' => 'required|string|max:255',
            'code_salle' => 'nullable|string|max:100',
            'capacite' => 'nullable|integer|min:0',
            'statut' => 'required|in:actif,inactif',
        ]);

        $salle->update($data);

        return back()->with('success', 'Salle modifiee.');
    }

    public function destroy(Salle $salle)
    {
        $salle->delete();

        return back()->with('success', 'Salle supprimee.');
    }
}
