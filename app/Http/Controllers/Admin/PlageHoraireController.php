<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlageHoraire;
use Illuminate\Http\Request;

class PlageHoraireController extends Controller
{
    public function index()
    {
        return view('Admin.EmploiTemps.plages_horaires', [
            'title' => 'Plages horaires',
            'plages' => PlageHoraire::orderBy('ordre')->orderBy('heure_debut')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'libelle' => 'required|string|max:255',
            'heure_debut' => 'required|date_format:H:i',
            'heure_fin' => 'required|date_format:H:i|after:heure_debut',
            'duree_payable' => 'nullable|numeric|min:0',
            'type_plage' => 'required|in:cours,pause',
            'type_personnel' => 'required|in:tous,permanent,vacataire',
            'periode_journee' => 'required|in:jour,soir',
            'format_plage' => 'required|string|max:30',
            'ordre' => 'nullable|integer|min:0',
            'statut' => 'required|in:actif,inactif',
        ]);

        $data['id_user'] = auth()->id() ?? 0;
        PlageHoraire::create($data);

        return back()->with('success', 'Plage horaire creee.');
    }

    public function update(Request $request, PlageHoraire $plage_horaire)
    {
        $data = $request->validate([
            'libelle' => 'required|string|max:255',
            'heure_debut' => 'required|date_format:H:i',
            'heure_fin' => 'required|date_format:H:i|after:heure_debut',
            'duree_payable' => 'nullable|numeric|min:0',
            'type_plage' => 'required|in:cours,pause',
            'type_personnel' => 'required|in:tous,permanent,vacataire',
            'periode_journee' => 'required|in:jour,soir',
            'format_plage' => 'required|string|max:30',
            'ordre' => 'nullable|integer|min:0',
            'statut' => 'required|in:actif,inactif',
        ]);

        $plage_horaire->update($data);

        return back()->with('success', 'Plage horaire modifiee.');
    }

    public function destroy(PlageHoraire $plage_horaire)
    {
        $plage_horaire->delete();

        return back()->with('success', 'Plage horaire supprimee.');
    }
}
