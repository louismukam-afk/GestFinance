<?php

namespace Tests\Unit;

use App\Exports\HeuresRealiseesExport;
use App\Models\HeureRealiseeEnseignant;
use App\Models\personnel;
use App\Services\DecompteHeuresEnseignant;
use Tests\TestCase;

class DecompteHeuresEnseignantTest extends TestCase
{
    private function heure(string $date, float $prevu, float $realise, float $taux): HeureRealiseeEnseignant
    {
        $heure = new HeureRealiseeEnseignant([
            'id_personnel' => 1, 'date_seance' => $date, 'duree_prevue' => $prevu,
            'duree_realisee' => $realise, 'montant_taux' => $taux, 'montant_total' => $realise * $taux,
        ]);
        $heure->setRelation('personnel', new personnel(['nom' => 'Enseignant test']));

        return $heure;
    }

    public function test_eight_planned_hours_and_six_worked_hours_pay_four_hours(): void
    {
        $heures = collect([
            $this->heure('2026-10-01', 2, 2, 3000), $this->heure('2026-10-08', 2, 2, 3000),
            $this->heure('2026-10-15', 2, 2, 3000), $this->heure('2026-10-22', 2, 0, 3000),
        ]);
        $total = (new DecompteHeuresEnseignant())->totaux($heures)->first();

        $this->assertEquals(8, $total['heures_prevues']);
        $this->assertEquals(6, $total['heures_realisees']);
        $this->assertEquals(4, $total['heures_payables']);
        $this->assertEquals(18000, $total['montant_brut']);
        $this->assertEquals(6000, $total['montant_sanction']);
        $this->assertEquals(12000, $total['montant_total']);
        $sheets = (new HeuresRealiseesExport($heures))->sheets();
        $this->assertCount(2, $sheets);
        $this->assertSame(array_values($total), $sheets[1]->collection()->first());
    }

    public function test_sanctions_use_each_missing_slot_rate_and_do_not_carry_to_another_month(): void
    {
        $heures = collect([
            $this->heure('2026-10-01', 2, 1, 3000), $this->heure('2026-10-08', 2, 0, 5000),
            $this->heure('2026-11-01', 2, 2, 4000),
        ]);
        $totaux = (new DecompteHeuresEnseignant())->totaux($heures);

        $this->assertCount(2, $totaux);
        $this->assertEquals(13000, $totaux[0]['montant_sanction']);
        $this->assertEquals(0, $totaux[0]['montant_total']);
        $this->assertEquals(8000, $totaux[1]['montant_total']);
        $this->assertEquals(2, $totaux[1]['heures_payables']);
    }
}
