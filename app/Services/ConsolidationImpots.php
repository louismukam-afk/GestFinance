<?php

namespace App\Services;

use App\Models\TauxImpot;
use Illuminate\Support\Collection;

class ConsolidationImpots
{
    public function lignes(Collection $heures, TauxImpot $defaut): Collection
    {
        return $heures->groupBy(fn($heure) => $heure->id_personnel.'|'.$heure->date_seance->format('Y-m'))
            ->map(function ($items) use ($defaut) {
                $personnel = $items->first()->personnel;
                $taux = $personnel->taux_impot ?: $defaut;
                $total = (new DecompteHeuresEnseignant())->totaux($items)->first();
                // La base fiscale est le net apres sanctions, exprime en centimes.
                $baseCentimes = (int) round($total['montant_total'] * 100);
                $pourcentageCentiemes = (int) round((float) $taux->taux * 100);
                $impotCentimes = intdiv($baseCentimes * $pourcentageCentiemes + 5000, 10000);

                return [
                    'id_personnel' => $personnel->id,
                    'nom' => $personnel->nom,
                    'mois' => $total['mois'],
                    'id_taux_impot' => $personnel->id_taux_impot,
                    'taux_nom' => $taux->nom,
                    'taux' => $taux->taux,
                    'brut' => $baseCentimes / 100,
                    'impot' => $impotCentimes / 100,
                    'net' => ($baseCentimes - $impotCentimes) / 100,
                ];
            })->values();
    }
}
