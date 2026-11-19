<?php

namespace Tests\Unit;

use App\Models\HeureRealiseeEnseignant;
use App\Models\personnel;
use App\Models\TauxImpot;
use App\Services\ConsolidationImpots;
use Tests\TestCase;

class ConsolidationImpotsTest extends TestCase
{
    private function heure(float $prevu, float $realise, float $tauxHoraire, ?TauxImpot $impot = null): HeureRealiseeEnseignant
    {
        $personnel = new personnel(['nom' => 'Enseignant test']);
        $personnel->id = 1;
        $personnel->setRelation('taux_impot', $impot);
        $heure = new HeureRealiseeEnseignant([
            'id_personnel' => 1, 'date_seance' => '2026-10-01', 'duree_prevue' => $prevu,
            'duree_realisee' => $realise, 'montant_taux' => $tauxHoraire, 'montant_total' => $realise * $tauxHoraire,
        ]);
        $heure->setRelation('personnel', $personnel);

        return $heure;
    }

    public function test_tax_is_calculated_after_sanctions_and_totals_reconcile(): void
    {
        $lignes = (new ConsolidationImpots())->lignes(
            collect([$this->heure(8, 6, 3000)]), new TauxImpot(['nom' => 'Defaut', 'taux' => '5.50'])
        );
        $ligne = $lignes->first();
        $this->assertEquals(12000, $ligne['brut']);
        $this->assertEquals(660, $ligne['impot']);
        $this->assertEquals(11340, $ligne['net']);
        $this->assertEquals($lignes->sum('brut'), $lignes->sum('impot') + $lignes->sum('net'));
    }

    public function test_personnel_override_zero_rate_and_negative_consolidations_are_handled(): void
    {
        $service = new ConsolidationImpots();
        $defaut = new TauxImpot(['nom' => 'Defaut', 'taux' => '5.50']);
        $exonere = new TauxImpot(['nom' => 'Exonere', 'taux' => 0]);
        $ligne = $service->lignes(collect([$this->heure(8, 6, 3000, $exonere)]), $defaut)->first();
        $this->assertEquals(0, $ligne['impot']);
        $this->assertEquals(12000, $ligne['net']);
        $ligne = $service->lignes(collect([$this->heure(8, 2, 3000)]), $defaut)->first();
        $this->assertEquals(0, $ligne['brut']);
        $this->assertEquals(0, $ligne['impot']);
        $this->assertEquals(0, $ligne['net']);
    }

    public function test_tax_is_rounded_per_month_in_centimes(): void
    {
        $octobre = $this->heure(1, 1, 0.10);
        $novembre = $this->heure(1, 1, 0.10);
        $novembre->date_seance = '2026-11-01';
        $lignes = (new ConsolidationImpots())->lignes(collect([$octobre, $novembre]), new TauxImpot(['nom' => 'Defaut', 'taux' => '5.50']));
        $this->assertCount(2, $lignes);
        $this->assertEquals(0.01, $lignes[0]['impot']);
        $this->assertEquals(0.09, $lignes[0]['net']);
        $this->assertEquals(0.02, $lignes->sum('impot'));
    }
}
