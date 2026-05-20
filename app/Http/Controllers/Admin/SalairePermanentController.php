<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\annee_academique;
use App\Models\entite;
use App\Models\personnel;
use App\Models\SalairePermanent;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SalairePermanentController extends Controller
{
    public function index()
    {
        return view('Admin.EmploiTemps.salaires_permanents', [
            'title' => 'Salaires permanents',
            'salaires' => SalairePermanent::with(['personnel', 'annee_academique', 'entite'])->latest()->get(),
            'personnels' => personnel::where('type_personnel', 'permanent')->orderBy('nom')->get(),
            'annees' => annee_academique::orderBy('nom', 'desc')->get(),
            'entites' => entite::orderBy('nom_entite')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_personnel' => 'required|integer|exists:personnels,id',
            'id_annee_academique' => 'nullable|integer|exists:annee_academiques,id',
            'id_entite' => 'nullable|integer|exists:entites,id',
            'montant_mensuel' => 'required|numeric|min:0',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'statut' => 'required|in:actif,inactif',
            'observations' => 'nullable|string|max:1000',
        ]);

        $data['id_user'] = auth()->id() ?? 0;
        SalairePermanent::create($data);

        return back()->with('success', 'Salaire permanent enregistre.');
    }

    public function update(Request $request, SalairePermanent $salaire_permanent)
    {
        $data = $request->validate([
            'id_personnel' => 'required|integer|exists:personnels,id',
            'id_annee_academique' => 'nullable|integer|exists:annee_academiques,id',
            'id_entite' => 'nullable|integer|exists:entites,id',
            'montant_mensuel' => 'required|numeric|min:0',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'statut' => 'required|in:actif,inactif',
            'observations' => 'nullable|string|max:1000',
        ]);

        $salaire_permanent->update($data);

        return back()->with('success', 'Salaire permanent modifie.');
    }

    public function destroy(SalairePermanent $salaire_permanent)
    {
        $salaire_permanent->delete();

        return back()->with('success', 'Salaire permanent supprime.');
    }

    public static function salaireJournalier(?SalairePermanent $salaire, Carbon $date): float
    {
        if (!$salaire) {
            return 0;
        }

        return round(((float) $salaire->montant_mensuel) / $date->daysInMonth, 2);
    }
}
