<?php

namespace Tests\Unit;

use App\Models\CoursEnseignant;
use App\Models\DisciplinePersonnel;
use App\Models\HeureRealiseeEnseignant;
use App\Models\personnel;
use App\Models\ProgrammeSpecialite;
use App\Models\specialite;
use App\Services\RapportImportBiometrie;
use Tests\TestCase;

class RapportImportBiometrieTest extends TestCase
{
    private function heure(float $prevu, float $realise): HeureRealiseeEnseignant
    {
        $heure = new HeureRealiseeEnseignant([
            'id_personnel' => 1, 'id_specialite' => 2, 'id_seance_cours' => 3, 'date_seance' => '2026-10-15',
            'heure_debut_prevue' => '08:00:00', 'heure_fin_prevue' => '10:00:00',
            'duree_prevue' => $prevu, 'duree_realisee' => $realise, 'montant_taux' => 3000, 'montant_total' => $realise * 3000,
        ]);
        $heure->setRelation('personnel', new personnel(['nom' => 'Enseignant test']));
        $programme = new ProgrammeSpecialite();
        $programme->setRelation('specialite', new specialite(['nom_specialite' => 'Informatique']));
        $programme->setRelation('matiere', null);
        $cours = new CoursEnseignant();
        $cours->setRelation('programme', $programme);
        $heure->setRelation('cours', $cours);

        return $heure;
    }

    public function test_financial_report_reconciles_budget_cuts_sanctions_and_net(): void
    {
        $service = new RapportImportBiometrie();
        foreach ([[8, 6, 12000], [4, 1, 0]] as [$prevu, $realise, $net]) {
            $ligne = $service->finances(collect([$this->heure($prevu, $realise)]))->first();
            $this->assertEquals($net, $ligne['montant_total']);
            $this->assertEquals($ligne['montant_prevu'], $ligne['coupe_heures'] + $ligne['sanction_retenue'] + $ligne['montant_total']);
            $this->assertEquals($ligne['montant_sanction'], $ligne['sanction_retenue'] + $ligne['sanction_non_imputable']);
        }
    }

    public function test_discipline_report_groups_by_specialty_and_teacher_and_preserves_justification(): void
    {
        $discipline = new DisciplinePersonnel([
            'id_personnel' => 1, 'id_seance_cours' => 3, 'date_discipline' => '2026-10-15', 'statut' => 'justifie',
        ]);
        $groupes = (new RapportImportBiometrie())->discipline(collect([$this->heure(2, 0)]), collect([$discipline]));
        $this->assertSame('Informatique', $groupes[0]['specialite']);
        $enseignant = $groupes[0]['enseignants'][0];
        $this->assertSame('Enseignant test', $enseignant['enseignant']);
        $this->assertSame('2026-10', $enseignant['mois']);
        $evenement = $enseignant['evenements'][0];
        $this->assertSame('15/10/2026', $evenement['date']);
        $this->assertSame('justifie', $evenement['statuts']);
        $this->assertEquals(2, $evenement['manquantes']);
    }
}
