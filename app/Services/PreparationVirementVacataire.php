<?php

namespace App\Services;

use App\Models\TauxImpot;
use Brick\Math\BigInteger;
use Illuminate\Support\Collection;

class PreparationVirementVacataire
{
    public function lignes(Collection $heures, TauxImpot $defaut): Collection
    {
        $resultat = collect();
        foreach ($heures->groupBy(fn($h) => $h->id_personnel.'|'.$h->date_seance->format('Y-m')) as $items) {
            $fiscal = (new ConsolidationImpots())->lignes($items, $defaut)->first();
            $entites = $items->groupBy('id_entite')->sortKeys();
            $totauxEntites = $entites->map(fn($seances) => (new DecompteHeuresEnseignant())->totaux($seances)->first());
            $poids = $totauxEntites->map(fn($total) => (int) round($total['montant_total'] * 100))->all();
            // Conserve exactement la consolidation fiscale du mois, y compris son plafond de sanction.
            $bases = $this->repartir((int) round($fiscal['brut'] * 100), $poids);
            $impots = $this->repartir((int) round($fiscal['impot'] * 100), $bases);
            foreach ($entites as $idEntite => $seances) {
                $personnel = $seances->first()->personnel;
                $resultat->push([
                    'id_personnel' => $personnel->id, 'id_entite' => (int) $idEntite,
                    'nom' => $personnel->nom, 'compte' => trim((string) $personnel->niveau_etude),
                    'id_banque' => $personnel->id_banque, 'banque_personnel' => $personnel->banque?->nom_banque,
                    'mois' => $fiscal['mois'], 'taux' => $fiscal['taux'],
                    'brut_fiscal' => $bases[$idEntite] / 100, 'impot' => $impots[$idEntite] / 100,
                    'net_avant_frais' => ($bases[$idEntite] - $impots[$idEntite]) / 100,
                    'heures_prevues' => $totauxEntites[$idEntite]['heures_prevues'],
                    'heures_realisees' => $totauxEntites[$idEntite]['heures_realisees'],
                    'heures_manquantes' => $totauxEntites[$idEntite]['heures_non_comptabilisees'],
                    'montant_realise' => $totauxEntites[$idEntite]['montant_brut'],
                    'sanction_retenue' => round($totauxEntites[$idEntite]['montant_brut'] - $bases[$idEntite] / 100, 2),
                ]);
            }
        }

        return $resultat->groupBy(fn($ligne) => $ligne['id_entite'].'|'.$ligne['id_personnel'])
            ->map(fn($items) => array_merge($items->first(), [
                'brut_fiscal' => round($items->sum('brut_fiscal'), 2),
                'impot' => round($items->sum('impot'), 2),
                'net_avant_frais' => round($items->sum('net_avant_frais'), 2),
                'details' => $items->map(fn($i) => collect($i)->only(['mois', 'taux', 'brut_fiscal', 'impot', 'net_avant_frais',
                    'heures_prevues', 'heures_realisees', 'heures_manquantes', 'montant_realise', 'sanction_retenue'])->all())->values()->all(),
            ]))->sortBy('nom')->values();
    }

    private function repartir(int $total, array $poids): array
    {
        $somme = array_sum($poids);
        $parts = array_fill_keys(array_keys($poids), 0);
        if ($total === 0 || $somme === 0) {
            return $parts;
        }
        $restes = [];
        foreach ($poids as $id => $poidsEntite) {
            [$quotient, $reste] = BigInteger::of($total)->multipliedBy($poidsEntite)->quotientAndRemainder($somme);
            $parts[$id] = $quotient->toInt();
            $restes[$id] = $reste->toInt();
        }
        arsort($restes, SORT_NUMERIC);
        $reste = $total - array_sum($parts);
        foreach (array_keys($restes) as $id) {
            if ($reste-- <= 0) { break; }
            $parts[$id]++;
        }

        return $parts;
    }
}
