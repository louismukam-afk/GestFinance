<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TauxHoraire;
use Illuminate\Http\Request;

class TauxHoraireController extends Controller
{
    public function index()
    {
        return view('Admin.EmploiTemps.taux_horaires', [
            'title' => 'Taux horaires',
            'taux' => TauxHoraire::orderByDesc('par_defaut')->orderBy('libelle')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'libelle' => 'required|string|max:255',
            'montant' => 'required|numeric|min:0',
            'par_defaut' => 'nullable|boolean',
            'statut' => 'required|in:actif,inactif',
        ]);

        $data['par_defaut'] = $request->boolean('par_defaut');
        $data['id_user'] = auth()->id() ?? 0;

        if ($data['par_defaut']) {
            TauxHoraire::query()->update(['par_defaut' => false]);
        }

        TauxHoraire::create($data);

        return back()->with('success', 'Taux horaire cree.');
    }

    public function update(Request $request, TauxHoraire $taux_horaire)
    {
        $data = $request->validate([
            'libelle' => 'required|string|max:255',
            'montant' => 'required|numeric|min:0',
            'par_defaut' => 'nullable|boolean',
            'statut' => 'required|in:actif,inactif',
        ]);

        $data['par_defaut'] = $request->boolean('par_defaut');

        if ($data['par_defaut']) {
            TauxHoraire::where('id', '<>', $taux_horaire->id)->update(['par_defaut' => false]);
        }

        $taux_horaire->update($data);

        return back()->with('success', 'Taux horaire modifie.');
    }

    public function destroy(TauxHoraire $taux_horaire)
    {
        $taux_horaire->delete();

        return back()->with('success', 'Taux horaire supprime.');
    }
}
