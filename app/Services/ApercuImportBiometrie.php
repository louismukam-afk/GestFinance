<?php

namespace App\Services;

use App\Models\{BiometrieImport, CoursEnseignant, HeureRealiseeEnseignant};
use Carbon\Carbon;

class ApercuImportBiometrie
{
    public function analyser(BiometrieImport $import): array
    {
        $debut = $import->date_debut->copy()->startOfDay();
        $fin = $import->date_fin->copy()->endOfDay();
        $pointages = $import->pointages()->whereNotNull('id_personnel')->get()->groupBy(fn($p) => $p->id_personnel.'|'.$p->date_heure_pointage->toDateString());
        $personnels = $import->pointages()->whereNotNull('id_personnel')->distinct()->pluck('id_personnel');
        $cours = CoursEnseignant::with(['personnel', 'programme.matiere', 'programme.specialite', 'seances.plage'])
            ->where('statut', 'actif')->whereIn('id_personnel', $personnels)
            ->whereDate('date_debut', '<=', $fin->toDateString())->whereDate('date_fin', '>=', $debut->toDateString())->get();
        $anciennes = HeureRealiseeEnseignant::with(['cours.programme', 'import'])
            ->where('id_biometrie_import', '!=', $import->id)
            ->where(fn($q) => $q->whereHas('import', fn($s) => $s->whereNull('type_import')->orWhereIn('type_import', ['cours', 'manuel']))
                ->orWhereExists(fn($s) => $s->selectRaw('1')->from('lignes_virement_vacataires as lv')
                    ->whereColumn('lv.id_biometrie_import', 'heures_realisees_enseignants.id_biometrie_import')
                    ->whereColumn('lv.id_personnel', 'heures_realisees_enseignants.id_personnel')
                    ->whereColumn('lv.id_entite', 'heures_realisees_enseignants.id_entite')))
            ->whereIn('id_personnel', $personnels)->whereDate('date_seance', '>=', $debut->toDateString())
            ->whereDate('date_seance', '<=', $fin->toDateString())->get()
            ->groupBy(fn($h) => $h->id_personnel.'|'.$h->date_seance->toDateString());
        $lignes = []; $vus = [];
        foreach ($cours as $enseignement) {
            $start = $debut->copy()->max(Carbon::parse($enseignement->date_debut)->startOfDay());
            $end = $fin->copy()->min(Carbon::parse($enseignement->date_fin)->endOfDay());
            foreach ($enseignement->seances as $seance) {
                if (!$seance->plage || $seance->plage->type_plage !== 'cours') { continue; }
                for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                    if ($date->dayOfWeekIso !== (int) $seance->jour_semaine) { continue; }
                    $programme = $enseignement->programme;
                    $sujet = $programme?->type_matiere === 'transversale' ? 'transversale-'.($programme->id_matiere ?? $enseignement->id_programme_specialite) : 'programme-'.$enseignement->id_programme_specialite;
                    $cle = implode('|', [$enseignement->id_personnel, $date->toDateString(), $seance->id_plage_horaire, $sujet]);
                    if (isset($vus[$cle])) { continue; }
                    $vus[$cle] = true;
                    $precedentes = $anciennes->get($enseignement->id_personnel.'|'.$date->toDateString(), collect());
                    $meme = function ($h) use ($seance, $sujet) {
                        $programme = $h->cours?->programme;
                        $ancienSujet = $programme?->type_matiere === 'transversale' ? 'transversale-'.($programme->id_matiere ?? $h->id_programme_specialite) : 'programme-'.$h->id_programme_specialite;
                        return $h->id_seance_cours == $seance->id || ($h->id_plage_horaire == $seance->id_plage_horaire && $ancienSujet === $sujet);
                    };
                    $debutPlage = Carbon::parse($seance->plage->heure_debut)->format('H:i:s');
                    $finPlage = Carbon::parse($seance->plage->heure_fin)->format('H:i:s');
                    $conflits = $precedentes->filter(fn($h) => !$meme($h) && Carbon::parse($h->heure_debut_prevue)->format('H:i:s') < $finPlage && Carbon::parse($h->heure_fin_prevue)->format('H:i:s') > $debutPlage);
                    $etat = $conflits->isNotEmpty() ? 'conflit' : ($precedentes->contains($meme) ? 'deja_consolidee' : 'a_traiter');
                    $raisons = $conflits->map(fn($h) => 'Import #'.$h->id_biometrie_import.' : '.substr($h->heure_debut_prevue, 0, 5).'-'.substr($h->heure_fin_prevue, 0, 5))->implode('; ');
                    $lignes[] = ['id_personnel' => $enseignement->id_personnel, 'date_iso' => $date->toDateString(),
                        'enseignant' => $enseignement->personnel?->nom ?? 'Enseignant #'.$enseignement->id_personnel,
                        'date' => $date->format('d/m/Y'), 'plage' => substr($debutPlage, 0, 5).' - '.substr($finPlage, 0, 5),
                        'matiere' => $programme?->matiere?->nom_matiere ?? '-', 'specialite' => $programme?->specialite?->nom_specialite ?? '-',
                        'pointages_jour' => $pointages->get($enseignement->id_personnel.'|'.$date->toDateString(), collect())->count(),
                        'etat' => $etat, 'raison' => $raisons];
                }
            }
        }
        return ['lignes' => $lignes, 'pointages' => $import->pointages()->count(),
            'non_associes' => $import->pointages()->whereNull('id_personnel')->count()];
    }
}
