<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\annee_academique;
use App\Models\entite;
use App\Models\personnel;
use App\Models\PersonnelEntite;
use Illuminate\Http\Request;

class PersonnelEntiteController extends Controller
{
    public function index()
    {
        return view('Admin.EmploiTemps.personnel_entites', [
            'title' => 'Affectation personnel - entite',
            'affectations' => PersonnelEntite::with(['personnel', 'entite', 'annee_academique'])->latest()->get(),
            'personnels' => personnel::orderBy('nom')->get(),
            'entites' => entite::orderBy('nom_entite')->get(),
            'annees' => annee_academique::orderBy('nom', 'desc')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_personnel' => 'required|integer|exists:personnels,id',
            'id_entite' => 'required|integer|exists:entites,id',
            'id_annee_academique' => 'nullable|integer|exists:annee_academiques,id',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'statut' => 'required|in:actif,inactif',
        ]);
        $data['id_user'] = auth()->id() ?? 0;

        PersonnelEntite::create($data);

        return back()->with('success', 'Affectation enregistree.');
    }

    public function update(Request $request, PersonnelEntite $personnel_entite)
    {
        $data = $request->validate([
            'id_personnel' => 'required|integer|exists:personnels,id',
            'id_entite' => 'required|integer|exists:entites,id',
            'id_annee_academique' => 'nullable|integer|exists:annee_academiques,id',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'statut' => 'required|in:actif,inactif',
        ]);

        $personnel_entite->update($data);

        return back()->with('success', 'Affectation modifiee.');
    }

    public function destroy(PersonnelEntite $personnel_entite)
    {
        $personnel_entite->delete();

        return back()->with('success', 'Affectation supprimee.');
    }
}
