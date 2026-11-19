<?php

namespace Tests\Unit;

use App\Models\{personnel, HeureRealiseeEnseignant, TauxImpot};
use App\Services\{ConsolidationImpots, MontantEnLettres, PreparationVirementVacataire};
use Tests\TestCase;

class PreparationVirementVacataireTest extends TestCase
{
    private function heure(int $entite, float $prevu, float $realise, float $taux = 3000, string $date = '2026-10-01'): HeureRealiseeEnseignant
    {
        $p = new personnel(['nom' => 'Test', 'niveau_etude' => '001 234', 'id_banque' => 1]);
        $p->id = 1;
        $p->setRelation('taux_impot', null)->setRelation('banque', null);
        $h = new HeureRealiseeEnseignant(['id_personnel' => 1, 'id_entite' => $entite,
            'date_seance' => $date, 'duree_prevue' => $prevu, 'duree_realisee' => $realise,
            'montant_taux' => $taux, 'montant_total' => $realise * $taux]);
        $h->setRelation('personnel', $p);

        return $h;
    }

    public function test_entity_allocation_conserves_global_tax_and_sanctions_in_centimes(): void
    {
        $heures = collect([$this->heure(1, 8, 6), $this->heure(2, 8, 2)]);
        $taux = new TauxImpot(['nom' => 'Defaut', 'taux' => 5.5]);
        $lignes = (new PreparationVirementVacataire())->lignes($heures, $taux);
        $global = (new ConsolidationImpots())->lignes($heures, $taux)->first();
        $this->assertCount(2, $lignes);
        $this->assertEquals($global['brut'], $lignes->sum('brut_fiscal'));
        $this->assertEquals($global['impot'], $lignes->sum('impot'));
        $this->assertEquals($global['net'], $lignes->sum('net_avant_frais'));
        $this->assertEquals(0, $lignes[1]['net_avant_frais']);
        $this->assertSame('001 234', $lignes[0]['compte']);
    }

    public function test_months_aggregate_without_offsetting_negative_month_or_losing_rounding(): void
    {
        $heures = collect([$this->heure(1, 1, 1, .10), $this->heure(2, 1, 1, .10),
            $this->heure(1, 8, 1, 3000, '2026-11-01')]);
        $lignes = (new PreparationVirementVacataire())->lignes($heures, new TauxImpot(['taux' => 5.5]));
        $this->assertEquals(.20, $lignes->sum('brut_fiscal'));
        $this->assertEquals(.01, $lignes->sum('impot'));
        $this->assertEquals(.19, $lignes->sum('net_avant_frais'));
        $this->assertCount(2, $lignes[0]['details']);
    }

    public function test_french_amount_without_intl(): void
    {
        $service = new MontantEnLettres();
        $this->assertSame('huit cent mille quatre cent trente francs CFA', $service->francs(800430));
        $this->assertSame('quatre-vingt mille francs CFA', $service->francs(80000));
        $this->assertSame('soixante et onze francs CFA et cinquante centimes', $service->francs(71.5));
    }

    public function test_large_amounts_do_not_overflow_during_allocation(): void
    {
        $heures = collect([$this->heure(1, 10, 10, 5000000), $this->heure(2, 10, 10, 5000000)]);
        $lignes = (new PreparationVirementVacataire())->lignes($heures, new TauxImpot(['taux' => 5.5]));
        $this->assertEquals(100000000, $lignes->sum('brut_fiscal'));
        $this->assertEquals(5500000, $lignes->sum('impot'));
        $this->assertEquals(94500000, $lignes->sum('net_avant_frais'));
    }
}
