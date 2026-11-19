<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class RapportImportBiometrie
{
    public function finances(Collection $heures): Collection
    {
        return $heures->groupBy(fn($heure) => $heure->id_personnel.'|'.$heure->date_seance->format('Y-m'))
            ->map(function ($items) {
                $total = (new DecompteHeuresEnseignant())->totaux($items)->first();
                $prevu = round($items->sum(fn($heure) => $heure->duree_prevue * $heure->montant_taux), 2);
                $retenue = min($total['montant_sanction'], $total['montant_brut']);

                return array_merge($total, [
                    'montant_prevu' => $prevu,
                    'coupe_heures' => max(round($prevu - $total['montant_brut'], 2), 0),
                    'sanction_retenue' => $retenue,
                    'sanction_non_imputable' => max(round($total['montant_sanction'] - $retenue, 2), 0),
                    'impact_total' => round($prevu - $total['montant_total'], 2),
                ]);
            })->values();
    }

    public function discipline(Collection $heures, Collection $disciplines): Collection
    {
        $disciplines = $disciplines->groupBy(fn($discipline) => implode('|', [
            $discipline->id_personnel, $discipline->id_seance_cours, $discipline->date_discipline->format('Y-m-d'),
        ]));

        return $heures->groupBy('id_specialite')->map(function ($items) use ($disciplines) {
            return [
                'specialite' => $items->first()->cours?->programme?->specialite?->nom_specialite ?? 'Specialite non renseignee',
                'enseignants' => $items->groupBy(fn($heure) => $heure->id_personnel.'|'.$heure->date_seance->format('Y-m'))
                    ->map(function ($seances) use ($disciplines) {
                        $total = (new DecompteHeuresEnseignant())->totaux($seances)->first();
                        $evenements = collect();
                        foreach ($seances->sortBy('date_seance') as $heure) {
                            $manquantes = max(round($heure->duree_prevue - $heure->duree_realisee, 2), 0);
                            $retard = $heure->heure_debut_reelle
                                ? max(Carbon::parse($heure->heure_debut_prevue)->diffInMinutes(Carbon::parse($heure->heure_debut_reelle), false), 0) : 0;
                            if ($manquantes <= 0 && $retard <= 0) {
                                continue;
                            }
                            $constats = $disciplines->get(implode('|', [$heure->id_personnel, $heure->id_seance_cours, $heure->date_seance->format('Y-m-d')]), collect());
                            $nature = $heure->duree_realisee <= 0 ? 'Absence ou presence non etablie' : 'Presence partielle';
                            if ($retard > 0) {
                                $nature .= ' / retard '.$retard.' min';
                            }
                            $evenements->push([
                                'date' => $heure->date_seance->format('d/m/Y'),
                                'matiere' => $heure->cours?->programme?->matiere?->nom_matiere ?? '-',
                                'plage' => substr($heure->heure_debut_prevue, 0, 5).' - '.substr($heure->heure_fin_prevue, 0, 5),
                                'nature' => $nature,
                                'manquantes' => $manquantes,
                                'statuts' => $constats->pluck('statut')->unique()->implode(', ') ?: 'A verifier',
                                'observation' => $heure->observation,
                            ]);
                        }

                        return array_merge($total, ['evenements' => $evenements]);
                    })->values(),
            ];
        })->values();
    }
}
