<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\annee_academique;
use App\Models\DisciplinePersonnel;
use App\Models\entite;
use App\Models\personnel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class DisciplinePersonnelController extends Controller
{
    public function index(Request $request)
    {
        $query = DisciplinePersonnel::with(['personnel', 'cours.programme.matiere', 'seance.plage', 'annee_academique', 'entite'])
            ->when($request->id_personnel, fn($q) => $q->where('id_personnel', $request->id_personnel))
            ->when($request->search_personnel, function ($q) use ($request) {
                $term = '%' . $request->search_personnel . '%';
                $q->whereHas('personnel', fn($personnel) => $personnel->where('nom', 'like', $term));
            })
            ->when($request->id_annee_academique, fn($q) => $q->where('id_annee_academique', $request->id_annee_academique))
            ->when($request->id_entite, fn($q) => $q->where('id_entite', $request->id_entite))
            ->when($request->type_discipline, fn($q) => $q->where('type_discipline', $request->type_discipline))
            ->when($request->statut, fn($q) => $q->where('statut', $request->statut))
            ->when($request->date_debut, fn($q) => $q->whereDate('date_discipline', '>=', $request->date_debut))
            ->when($request->date_fin, fn($q) => $q->whereDate('date_discipline', '<=', $request->date_fin))
            ->orderByDesc('date_discipline');

        return view('Admin.EmploiTemps.discipline_personnels.index', [
            'title' => 'Discipline du personnel',
            'disciplines' => $query->get()
                ->unique(fn($discipline) => implode('|', [
                    $discipline->id_personnel,
                    optional($discipline->date_discipline)->format('Y-m-d'),
                    $discipline->type_discipline,
                    $discipline->id_biometrie_import,
                    $discipline->id_seance_cours ?: 'permanent',
                ]))
                ->values(),
            'personnels' => personnel::orderBy('nom')->get(),
            'annees' => annee_academique::orderBy('nom', 'desc')->get(),
            'entites' => entite::orderBy('nom_entite')->get(),
        ]);
    }

    public function justify(Request $request, DisciplinePersonnel $discipline)
    {
        $data = $request->validate([
            'motif_justification' => 'required|string|max:2000',
            'date_justification' => 'required|date',
            'preuves.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ]);

        $paths = $discipline->preuves ?: [];
        if ($request->hasFile('preuves')) {
            File::ensureDirectoryExists(public_path('uploads/disciplines'));
            foreach ($request->file('preuves') as $file) {
                $name = time() . '_' . uniqid() . '_' . preg_replace('/[^A-Za-z0-9_.-]/', '_', $file->getClientOriginalName());
                $file->move(public_path('uploads/disciplines'), $name);
                $paths[] = 'uploads/disciplines/' . $name;
            }
        }

        $discipline->update([
            'statut' => 'justifie',
            'motif_justification' => $data['motif_justification'],
            'date_justification' => $data['date_justification'],
            'preuves' => $paths,
        ]);

        return back()->with('success', 'Justification enregistree.');
    }

    public function update(Request $request, DisciplinePersonnel $discipline)
    {
        $data = $request->validate([
            'motif' => 'nullable|string|max:2000',
            'statut' => 'required|in:non_justifie,justifie,annule',
        ]);

        $discipline->update($data);

        return back()->with('success', 'Discipline mise a jour.');
    }
}
