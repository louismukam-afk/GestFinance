<?php

namespace Tests\Feature;

use App\Models\HeureRealiseeEnseignant;
use App\Models\personnel;
use App\Services\DecompteHeuresEnseignant;
use Tests\TestCase;

class BiometriePdfTest extends TestCase
{
    public function test_detail_and_recap_exports_are_separate_and_recap_has_grand_totals(): void
    {
        $heure = new HeureRealiseeEnseignant([
            'id_personnel' => 1, 'date_seance' => '2026-10-01', 'duree_prevue' => 8,
            'duree_realisee' => 6, 'montant_taux' => 3000, 'montant_total' => 18000,
            'heure_debut_prevue' => '08:00:00', 'heure_fin_prevue' => '16:00:00', 'statut' => 'realise',
        ]);
        $heure->setRelation('personnel', new personnel(['nom' => 'Enseignant test']));
        $heure->setRelation('cours', null);
        $heures = collect([$heure]);
        $data = ['title' => 'Verification', 'heures' => $heures, 'totaux' => (new DecompteHeuresEnseignant())->totaux($heures)];

        $detail = view('Admin.EmploiTemps.biometrie.pdf', $data + ['tableau' => 'detail'])->render();
        $this->assertStringContainsString('Observation', $detail);
        $this->assertStringNotContainsString('Net a payer', $detail);

        $recap = view('Admin.EmploiTemps.biometrie.pdf', $data + ['tableau' => 'totaux'])->render();
        $this->assertStringNotContainsString('Observation', $recap);
        $this->assertStringContainsString('Net a payer', $recap);
        $this->assertStringContainsString('TOTAL GENERAL', $recap);
        $this->assertStringContainsString('12 000', $recap);
        $this->assertStringContainsString('6 000', $recap);

        $complete = view('Admin.EmploiTemps.biometrie.pdf', $data)->render();
        $this->assertStringContainsString('Observation', $complete);
        $this->assertStringContainsString('Net a payer', $complete);
    }
}
