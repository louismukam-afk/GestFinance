<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GroupeMatiere;
use App\Models\GroupeMatiereLigne;
use App\Models\ProgrammeSpecialite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GroupeMatiereController extends Controller
{
    public function create(ProgrammeSpecialite $programme)
    {
        $programme->load(['specialite', 'matiere', 'cycle', 'filiere', 'niveau', 'annee_academique', 'entite']);

        $groupedProgrammeIds = GroupeMatiereLigne::whereHas('groupe', function ($query) use ($programme) {
                $query->where('id_specialite', $programme->id_specialite);
            })
            ->pluck('id_programme_specialite')
            ->all();

        $moduleMatiereIds = GroupeMatiere::where('id_specialite', $programme->id_specialite)
            ->pluck('id_matiere_parent')
            ->all();

        $programmes = ProgrammeSpecialite::with('matiere')
            ->where('id_specialite', $programme->id_specialite)
            ->where('id_cycle', $programme->id_cycle)
            ->where('id_filiere', $programme->id_filiere)
            ->where('id_niveau', $programme->id_niveau)
            ->where('id_annee_academique', $programme->id_annee_academique)
            ->where('id_entite', $programme->id_entite)
            ->where('id', '<>', $programme->id)
            ->whereNotIn('id', $groupedProgrammeIds)
            ->whereNotIn('id_matiere', $moduleMatiereIds)
            ->orderBy('semestre')
            ->orderBy('id')
            ->get();

        $groupes = GroupeMatiere::with('lignes.programme.matiere')
            ->where('id_specialite', $programme->id_specialite)
            ->where('id_matiere_parent', $programme->id_matiere)
            ->latest()
            ->get();

        return view('Admin.GroupesMatieres.create', [
            'title' => 'Groupe matiere',
            'programme' => $programme,
            'programmes' => $programmes,
            'groupes' => $groupes,
        ]);
    }

    public function store(Request $request, ProgrammeSpecialite $programme)
    {
        $data = $request->validate([
            'description' => 'nullable|string',
            'programmes' => 'required|array|min:1',
            'programmes.*' => 'integer|exists:programme_specialites,id',
        ]);

        DB::transaction(function () use ($data, $programme) {
            $programme->loadMissing('matiere');

            $groupe = GroupeMatiere::firstOrCreate(
                [
                    'id_specialite' => $programme->id_specialite,
                    'id_matiere_parent' => $programme->id_matiere,
                ],
                [
                    'libelle_groupe' => $programme->matiere->nom_matiere ?? 'Module',
                    'description' => $data['description'] ?? null,
                    'id_user' => Auth::id() ?? 0,
                ]
            );

            if (($data['description'] ?? null) && !$groupe->description) {
                $groupe->update(['description' => $data['description']]);
            }

            foreach ($data['programmes'] as $programmeId) {
                GroupeMatiereLigne::firstOrCreate(
                    [
                        'id_groupe_matiere' => $groupe->id,
                        'id_programme_specialite' => $programmeId,
                    ],
                    [
                        'id_user' => Auth::id() ?? 0,
                    ]
                );
            }
        });

        return back()->with('success', 'Groupe matiere cree avec succes.');
    }

    public function destroyLine(GroupeMatiereLigne $ligne)
    {
        $ligne->delete();

        return back()->with('success', 'Matiere retiree du groupe.');
    }

    public function destroy(GroupeMatiere $groupe)
    {
        DB::transaction(function () use ($groupe) {
            $groupe->lignes()->delete();
            $groupe->delete();
        });

        return back()->with('success', 'Groupe matiere supprime.');
    }
}
