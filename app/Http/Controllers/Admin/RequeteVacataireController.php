<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BiometrieImport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RequeteVacataireController extends Controller
{
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
        abort_unless($parent->heures_realisees()->whereBetween('date_seance', [
            $debut->toDateString(), $fin->toDateString(),
        ])->exists(), 422, 'Aucune heure consolidee pour ce mois dans l import initial.');
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
