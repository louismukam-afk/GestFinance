<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BiometrieImport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RequeteVacataireController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['id_import_parent' => 'nullable|integer|min:1']);
        $parents = BiometrieImport::pourEnseignants()->where(fn ($q) => $q->whereNull('type_import')->orWhere('type_import', '!=', 'requete'))
            ->orderByDesc('id')->get();
        $requetes = BiometrieImport::where('type_import', 'requete')
            ->when($request->filled('id_import_parent'), fn ($q) => $q->where('id_import_parent', $request->id_import_parent))
            ->with(['heures_realisees' => fn ($q) => $q->whereNotNull('type_requete')->with(['personnel', 'cours.programme.matiere', 'seance.plage'])])
            ->orderByDesc('id')->get();
        $ordres = \App\Models\VirementVacataire::whereIn('id_biometrie_import', $requetes->pluck('id'))->with('lignes')->get()->groupBy('id_biometrie_import');

        return view('Admin.EmploiTemps.biometrie.requetes', compact('parents', 'requetes', 'ordres'));
    }

    public function update(Request $request, BiometrieImport $requete)
    {
        $data = $request->validate(['motif_requete' => 'required|string|max:3000']);
        DB::transaction(function () use ($requete, $data) {
            $dossier = BiometrieImport::whereKey($requete->id)->lockForUpdate()->firstOrFail();
            abort_unless($dossier->estRequete(), 404);
            if (\App\Models\VirementVacataire::where('id_biometrie_import', $dossier->id)->exists()) {
                throw ValidationException::withMessages(['motif_requete' => 'Ce dossier possede un ordre valide ; son motif ne peut plus etre modifie.']);
            }
            $dossier->update($data);
        });
        return redirect()->route('biometrie_heures.requetes.index', ['id_import_parent' => $requete->id_import_parent])
            ->with('success', 'Motif de la requete modifie.');
    }

    public function store(Request $request)
    {
        $data = $request->validate(['id_import_parent' => 'required|integer|exists:biometrie_imports,id',
            'mois_requete' => 'required|date_format:Y-m', 'motif_requete' => 'required|string|max:3000']);
        $parent = BiometrieImport::pourEnseignants()->findOrFail($data['id_import_parent']);
        abort_if($parent->estRequete(), 422, 'Choisissez l import initial et non un dossier de requete.');
        $debut = Carbon::createFromFormat('!Y-m', $data['mois_requete']);
        $fin = $debut->copy()->endOfMonth()->startOfDay();
        if ($fin->lt($parent->date_debut) || $debut->gt($parent->date_fin)) {
            throw ValidationException::withMessages(['mois_requete' => 'Ce mois ne fait pas partie de la periode de l import initial.']);
        }
        $requete = BiometrieImport::create([
            'type_import' => 'requete', 'statut' => 'manuel', 'id_import_parent' => $parent->id,
            'mois_requete' => $debut, 'motif_requete' => $data['motif_requete'],
            'libelle' => 'Salaires vacataires apres requete '.$debut->format('m/Y').' - import #'.$parent->id,
            'date_debut' => $debut->max($parent->date_debut)->toDateString(),
            'date_fin' => $fin->min($parent->date_fin)->toDateString(), 'id_user' => $request->user()?->id ?? 0,
        ]);
        return redirect()->route('biometrie_heures.manual.create', ['id_biometrie_import' => $requete->id])
            ->with('success', 'Dossier de requete cree. Saisissez les heures corrigees des enseignants concernes.');
    }
}
