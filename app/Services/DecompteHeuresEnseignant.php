<?php

namespace App\Services;

use Illuminate\Support\Collection;

class DecompteHeuresEnseignant
{
    public function totaux(Collection $heures): Collection
    {
        // Une sanction ne peut pas absorber la remuneration d'un autre mois.
        return $heures->groupBy(fn($heure) => $heure->id_personnel.'|'.$heure->date_seance->format('Y-m'))
            ->map(function ($items) {
                $prevues = round($items->sum('duree_prevue'), 2);
                $realisees = round($items->sum('duree_realisee'), 2);
                $manquantes = round($items->sum(fn($heure) => max($heure->duree_prevue - $heure->duree_realisee, 0)), 2);
                $brut = round($items->sum('montant_total'), 2);
                $manquantesSanctionnees = round($items->sum(fn($heure) => $heure->sanction_annulee ? 0 : max($heure->duree_prevue - $heure->duree_realisee, 0)), 2);
                $sanction = round($items->sum(fn($heure) => $heure->sanction_annulee ? 0 : max($heure->duree_prevue - $heure->duree_realisee, 0) * $heure->montant_taux), 2);

                return [
                    'enseignant' => $items->first()->personnel->nom ?? '-',
                    'mois' => $items->first()->date_seance->format('Y-m'),
                    'heures_prevues' => $prevues,
                    'heures_realisees' => $realisees,
                    'heures_non_comptabilisees' => $manquantes,
                    'heures_payables' => max(round($realisees - $manquantesSanctionnees, 2), 0),
                    'montant_brut' => $brut,
                    'montant_sanction' => $sanction,
                    'montant_total' => max(round($brut - $sanction, 2), 0),
                ];
            })->values();
    }
}
