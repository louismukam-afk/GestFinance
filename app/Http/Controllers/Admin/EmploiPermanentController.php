<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\annee_academique;
use App\Models\EmploiPermanent;
use App\Models\entite;
use App\Models\personnel;
use App\Models\PlageHoraire;
use Barryvdh\DomPDF\Facade\Pdf;
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

    public function index(Request $request)
    {
        $search = trim((string) $request->get('search_personnel', ''));
        $selectedPersonnelId = $request->get('id_personnel');
        $emploisQuery = EmploiPermanent::with(['personnel', 'plage', 'annee_academique', 'entite'])
            ->when($selectedPersonnelId, fn($q) => $q->where('id_personnel', $selectedPersonnelId))
            ->when($search !== '', function ($q) use ($search) {
                $q->whereHas('personnel', fn($personnel) => $personnel->where('nom', 'like', '%' . $search . '%'));
            })
            ->orderBy('id_personnel')
            ->orderBy('jour_semaine')
            ->orderByDesc('date_debut');

        return view('Admin.EmploiTemps.emplois_permanents', [
            'title' => 'Emplois du temps permanents',
            'emplois' => $emploisQuery->get(),
            'personnels' => personnel::where('type_personnel', 'permanent')->orderBy('nom')->get(),
            'plages' => $this->plagesDisponibles(),
            'annees' => annee_academique::orderBy('nom', 'desc')->get(),
            'entites' => entite::orderBy('nom_entite')->get(),
            'jours' => $this->jours,
            'selectedPersonnel' => $selectedPersonnelId ? personnel::find($selectedPersonnelId) : null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_personnel' => 'nullable|integer|exists:personnels,id',
            'id_personnels' => 'nullable|array',
            'id_personnels.*' => 'integer|exists:personnels,id',
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

        $personnelIds = collect($data['id_personnels'] ?? [])
            ->push($data['id_personnel'] ?? null)
            ->filter()
            ->unique()
            ->values();

        if ($personnelIds->isEmpty()) {
            return back()
                ->withErrors(['id_personnel' => 'Selectionne au moins un personnel permanent.'])
                ->withInput();
        }

        foreach ($personnelIds as $personnelId) {
            foreach ($data['jours'] as $jour) {
                EmploiPermanent::updateOrCreate(
                    [
                        'id_personnel' => $personnelId,
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
        }

        return back()->with('success', 'Emploi permanent enregistre pour ' . $personnelIds->count() . ' personnel(s).');
    }

    public function editPersonnel(personnel $personnel)
    {
        $emplois = EmploiPermanent::with(['plage', 'annee_academique', 'entite'])
            ->where('id_personnel', $personnel->id)
            ->orderBy('jour_semaine')
            ->orderByDesc('date_debut')
            ->get();

        return view('Admin.EmploiTemps.emplois_permanents_edit', [
            'title' => 'Modifier emploi permanent',
            'personnel' => $personnel,
            'emplois' => $emplois,
            'plages' => $this->plagesDisponibles(),
            'annees' => annee_academique::orderBy('nom', 'desc')->get(),
            'entites' => entite::orderBy('nom_entite')->get(),
            'jours' => $this->jours,
        ]);
    }

    public function pdfPersonnel(personnel $personnel)
    {
        $emplois = EmploiPermanent::with(['plage', 'annee_academique', 'entite'])
            ->where('id_personnel', $personnel->id)
            ->orderBy('jour_semaine')
            ->orderByDesc('date_debut')
            ->get();

        $pdf = Pdf::loadView('Admin.EmploiTemps.emplois_permanents_pdf', [
            'title' => 'Emploi du temps permanent',
            'personnel' => $personnel,
            'emplois' => $emplois,
            'jours' => $this->jours,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('emploi_permanent_' . $personnel->id . '.pdf');
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

    private function plagesDisponibles()
    {
        return PlageHoraire::where('statut', 'actif')
            ->where('type_plage', 'cours')
            ->whereIn('type_personnel', ['tous', 'permanent'])
            ->orderBy('ordre')
            ->orderBy('heure_debut')
            ->get();
    }
}
