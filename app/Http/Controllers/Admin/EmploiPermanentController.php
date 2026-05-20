<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\annee_academique;
use App\Models\EmploiPermanent;
use App\Models\entite;
use App\Models\personnel;
use App\Models\PlageHoraire;
use Illuminate\Http\Request;

class EmploiPermanentController extends Controller
{
    private array $jours = [
        1 => 'Lundi',
        2 => 'Mardi',
        3 => 'Mercredi',
        4 => 'Jeudi',
        5 => 'Vendredi',
        6 => 'Samedi',
        7 => 'Dimanche',
    ];

    public function index()
    {
        return view('Admin.EmploiTemps.emplois_permanents', [
            'title' => 'Emplois du temps permanents',
            'emplois' => EmploiPermanent::with(['personnel', 'plage', 'annee_academique', 'entite'])
                ->orderByDesc('date_debut')
                ->orderBy('jour_semaine')
                ->get(),
            'personnels' => personnel::where('type_personnel', 'permanent')->orderBy('nom')->get(),
            'plages' => PlageHoraire::where('statut', 'actif')
                ->where('type_plage', 'cours')
                ->whereIn('type_personnel', ['tous', 'permanent'])
                ->orderBy('ordre')
                ->orderBy('heure_debut')
                ->get(),
            'annees' => annee_academique::orderBy('nom', 'desc')->get(),
            'entites' => entite::orderBy('nom_entite')->get(),
            'jours' => $this->jours,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_personnel' => 'required|integer|exists:personnels,id',
            'id_plage_horaire' => 'required|integer|exists:plage_horaires,id',
            'id_annee_academique' => 'nullable|integer|exists:annee_academiques,id',
            'id_entite' => 'nullable|integer|exists:entites,id',
            'jours' => 'required|array|min:1',
            'jours.*' => 'integer|min:1|max:7',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'statut' => 'required|in:actif,inactif',
            'observations' => 'nullable|string|max:1000',
        ]);

        foreach ($data['jours'] as $jour) {
            EmploiPermanent::updateOrCreate(
                [
                    'id_personnel' => $data['id_personnel'],
                    'id_plage_horaire' => $data['id_plage_horaire'],
                    'jour_semaine' => $jour,
                    'date_debut' => $data['date_debut'],
                ],
                [
                    'id_annee_academique' => $data['id_annee_academique'] ?? null,
                    'id_entite' => $data['id_entite'] ?? null,
                    'date_fin' => $data['date_fin'] ?? null,
                    'statut' => $data['statut'],
                    'observations' => $data['observations'] ?? null,
                    'id_user' => auth()->id() ?? 0,
                ]
            );
        }

        return back()->with('success', 'Emploi permanent enregistre.');
    }

    public function update(Request $request, EmploiPermanent $emploi_permanent)
    {
        $data = $request->validate([
            'id_personnel' => 'required|integer|exists:personnels,id',
            'id_plage_horaire' => 'required|integer|exists:plage_horaires,id',
            'id_annee_academique' => 'nullable|integer|exists:annee_academiques,id',
            'id_entite' => 'nullable|integer|exists:entites,id',
            'jour_semaine' => 'required|integer|min:1|max:7',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'statut' => 'required|in:actif,inactif',
            'observations' => 'nullable|string|max:1000',
        ]);

        $emploi_permanent->update($data);

        return back()->with('success', 'Emploi permanent modifie.');
    }

    public function destroy(EmploiPermanent $emploi_permanent)
    {
        $emploi_permanent->delete();

        return back()->with('success', 'Emploi permanent supprime.');
    }
}
