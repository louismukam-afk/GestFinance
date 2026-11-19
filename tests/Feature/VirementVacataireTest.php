<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\VirementVacataireController;
use App\Http\Controllers\Admin\BulletinVacataireController;
use App\Models\{banque, entite, BiometrieImport, HeureRealiseeEnseignant, personnel, VirementVacataire};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Schema};
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class VirementVacataireTest extends TestCase
{
    private $import;
    private $personnel;
    private $controller;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('personnels', function (Blueprint $t) { $t->id(); $t->string('nom'); $t->string('niveau_etude')->nullable(); $t->integer('id_banque')->nullable(); $t->timestamps(); });
        Schema::create('banques', function (Blueprint $t) { $t->id(); $t->string('nom_banque'); $t->timestamps(); });
        Schema::create('entites', function (Blueprint $t) { $t->id(); $t->string('nom_entite'); $t->timestamps(); });
        foreach (['cours_enseignants', 'seance_cours'] as $table) { Schema::create($table, fn(Blueprint $t) => $t->id()); }
        (require database_path('migrations/2026_05_09_000019_create_biometrie_decompte_tables.php'))->up();
        Schema::table('biometrie_imports', fn(Blueprint $t) => $t->string('type_import')->nullable());
        foreach (['2026_11_19_000002_create_taux_impots_table.php', '2026_11_19_000003_add_id_taux_impot_to_personnels_table.php', '2026_11_19_000004_create_virements_vacataires_tables.php'] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
        banque::create(['nom_banque' => 'CCA']); banque::create(['nom_banque' => 'CBC']);
        entite::create(['nom_entite' => 'Institut Superieur Hintel']); entite::create(['nom_entite' => 'Centre de Formation Hintel']);
        $this->personnel = personnel::create(['nom' => 'Enseignant test', 'niveau_etude' => '001 234', 'id_banque' => 1]);
        $this->import = BiometrieImport::create(['date_debut' => '2026-10-01', 'date_fin' => '2026-10-31', 'type_import' => 'cours']);
        HeureRealiseeEnseignant::create([
            'id_biometrie_import' => $this->import->id, 'id_personnel' => $this->personnel->id,
            'id_cours_enseignant' => 1, 'id_seance_cours' => 1, 'id_programme_specialite' => 1,
            'id_cycle' => 1, 'id_filiere' => 1, 'id_niveau' => 1, 'id_specialite' => 1,
            'id_annee_academique' => 1, 'id_entite' => 1, 'id_plage_horaire' => 1,
            'date_seance' => '2026-10-01', 'jour_semaine' => 4,
            'heure_debut_prevue' => '08:00:00', 'heure_fin_prevue' => '16:00:00',
            'duree_prevue' => 8, 'duree_realisee' => 6, 'montant_taux' => 3000, 'montant_total' => 18000,
        ]);
        $this->app['view']->share('errors', new \Illuminate\Support\ViewErrorBag());
        $this->controller = new VirementVacataireController();
    }

    private function data(array $override = []): array
    {
        $data = ['id_entite' => 1, 'id_banque' => 1, 'personnels' => [$this->personnel->id],
            'modele' => 'ish', 'agence' => 'Etoudi', 'compte_debit' => '001-002', 'motif' => 'Paie octobre 2026',
            'date_ordre' => '2026-11-19', 'lieu' => 'Yaounde', 'signataire' => 'Direction',
            'fonction_signataire' => 'La Directrice', 'confirmation' => '1'];
        $data = array_replace($data, $override);
        $data['empreinte'] = $this->controller->create(new Request($data), $this->import)->getData()['empreinte'];

        return $data;
    }

    public function test_validated_order_deducts_fees_and_pdf_uses_immutable_snapshot(): void
    {
        $data = $this->data(['transfert' => [1 => 1], 'frais' => [1 => 3500]]);
        $this->controller->store(Request::create('/', 'POST', $data), $this->import);
        $etat = VirementVacataire::firstOrFail();
        $ligne = $etat->lignes->first();
        $this->assertEquals(12000, $ligne->brut_fiscal);
        $this->assertEquals(660, $ligne->impot);
        $this->assertEquals(11340, $ligne->net_avant_frais);
        $this->assertEquals(7840, $ligne->net_a_payer);
        $this->assertSame('001 234', $ligne->compte);
        $this->personnel->update(['nom' => 'Nouveau nom', 'niveau_etude' => '999']);
        HeureRealiseeEnseignant::query()->update(['montant_total' => 1]);
        $this->assertSame('Enseignant test', $etat->fresh()->lignes->first()->nom);
        $this->assertStringContainsString('7 840,00', $this->controller->show($etat)->render());
        foreach (['ish', 'cef'] as $modele) {
            $etat->modele = $modele;
            $pdf = $this->controller->pdf($etat);
            $this->assertStringStartsWith('%PDF-', $pdf->getContent());
        }
    }

    public function test_filters_prevent_wrong_bank_or_entity_and_allow_explicit_other_bank(): void
    {
        $wrong = $this->controller->create(new Request(['id_entite' => 1, 'id_banque' => 2]), $this->import)->getData();
        $this->assertCount(0, $wrong['lignes']);
        $other = $this->controller->create(new Request(['id_entite' => 1, 'id_banque' => 2, 'inclure_autres' => 1]), $this->import)->getData();
        $this->assertCount(1, $other['lignes']);
        $request = new Request(['id_entite' => 1, 'id_banque' => 1]);
        $this->app->instance('request', $request);
        $this->assertStringContainsString('Sans compte confirme', $this->controller->create($request, $this->import)->render());
        foreach ([['id_entite' => 2], ['id_banque' => 2], ['personnels' => [999]], ['frais' => [1 => 12000], 'transfert' => [1 => 1]], ['comptes' => [1 => '']], ['confirmation' => '0']] as $override) {
            try {
                $this->controller->store(Request::create('/', 'POST', $this->data($override)), $this->import);
                $this->fail('Validation attendue');
            } catch (ValidationException $exception) { $this->assertNotEmpty($exception->errors()); }
        }
        $this->assertSame(0, VirementVacataire::count());
    }

    public function test_absent_account_requires_confirmation_and_duplicate_period_is_blocked(): void
    {
        $data = $this->data(['comptes' => [1 => ''], 'sans_compte' => [1 => 1], 'frais' => [1 => 3500]]);
        $this->controller->store(Request::create('/', 'POST', $data), $this->import);
        $ligne = VirementVacataire::first()->lignes->first();
        $this->assertTrue($ligne->sans_compte_confirme);
        $this->assertEquals(0, $ligne->frais_transfert);
        $this->assertEquals(11340, $ligne->net_a_payer);
        try {
            $this->controller->store(Request::create('/', 'POST', $data), $this->import);
            $this->fail('Doublon attendu');
        } catch (ValidationException $exception) { $this->assertArrayHasKey('personnels', $exception->errors()); }
        $this->assertSame(1, VirementVacataire::count());
    }

    public function test_stale_calculations_are_not_validated(): void
    {
        $data = $this->data();
        HeureRealiseeEnseignant::query()->update(['montant_total' => 20000]);
        $this->expectException(ValidationException::class);
        $this->controller->store(Request::create('/', 'POST', $data), $this->import);
    }

    public function test_custom_fee_for_other_bank_and_overlapping_import_protection(): void
    {
        $data = $this->data(['id_banque' => 2, 'inclure_autres' => 1, 'transfert' => [1 => 1], 'frais' => [1 => 1500.25]]);
        $this->controller->store(Request::create('/', 'POST', $data), $this->import);
        $this->assertEquals(9839.75, VirementVacataire::first()->lignes->first()->net_a_payer);
        $this->assertEquals(2, VirementVacataire::first()->id_banque);
        $autre = $this->import->replicate();
        $autre->save();
        $heure = HeureRealiseeEnseignant::first()->replicate();
        $heure->id_biometrie_import = $autre->id;
        $heure->save();
        $this->import = $autre;
        try {
            $this->controller->store(Request::create('/', 'POST', $this->data()), $autre);
            $this->fail('Une periode chevauchante doit etre bloquee.');
        } catch (ValidationException $exception) { $this->assertArrayHasKey('personnels', $exception->errors()); }
        $this->assertEquals(1, VirementVacataire::count());
    }

    public function test_guest_cannot_access_orders(): void
    {
        $this->get(route('virements_vacataires.index'))->assertRedirect(route('login'));
        $this->post(route('virements_vacataires.store', $this->import), [])->assertRedirect(route('login'));
        $this->get(route('virements_vacataires.historique'))->assertRedirect(route('login'));
        $this->get(route('bulletins_vacataires.show', $this->import))->assertRedirect(route('login'));
    }

    public function test_history_filters_saved_orders_and_reopens_pdf(): void
    {
        $this->controller->store(Request::create('/', 'POST', $this->data()), $this->import);
        $view = $this->controller->historique(new Request(['date_debut' => '2026-11-01', 'date_fin' => '2026-11-30', 'id_entite' => 1, 'id_banque' => 1, 'recherche' => 'Enseignant', 'id_import' => $this->import->id]));
        $this->assertEquals(1, $view->getData()['etats']->total());
        $this->assertStringContainsString('OV-1', $view->render());
        foreach ([['id_banque' => 2], ['id_entite' => 2], ['date_fin' => '2026-10-31'], ['recherche' => 'Introuvable'], ['id_import' => 999]] as $filtre) {
            $this->assertEquals(0, $this->controller->historique(new Request($filtre))->getData()['etats']->total());
        }
        $this->expectException(ValidationException::class);
        $this->controller->historique(new Request(['date_debut' => '2026-12-01', 'date_fin' => '2026-11-01']));
    }

    public function test_provisional_bulletins_per_import_include_hours_and_sanction(): void
    {
        $controller = new BulletinVacataireController();
        $view = $controller->show($this->import);
        $bulletin = $view->getData()['bulletins']->first();
        $this->assertSame('Provisoire', $bulletin['statut']);
        $this->assertEquals(11340, $bulletin['net_a_payer']);
        $detail = $bulletin['entites']->first()['details'][0];
        $this->assertEquals(8, $detail['heures_prevues']);
        $this->assertEquals(6, $detail['heures_realisees']);
        $this->assertEquals(6000, $detail['sanction_retenue']);
        $this->assertStringContainsString('Tous les bulletins PDF', $view->render());
        $pdf = $controller->pdf(new Request(['id_personnel' => $this->personnel->id]), $this->import);
        $this->assertStringStartsWith('%PDF-', $pdf->getContent());
        $this->assertStringContainsString('inline', $pdf->headers->get('Content-Disposition'));
    }

    public function test_validated_bulletins_preserve_fees_and_historical_hours(): void
    {
        $this->controller->store(Request::create('/', 'POST', $this->data(['transfert' => [1 => 1], 'frais' => [1 => 3500]])), $this->import);
        HeureRealiseeEnseignant::query()->update(['duree_realisee' => 1, 'montant_total' => 1]);
        $this->personnel->update(['nom' => 'Nouveau nom', 'niveau_etude' => '999']);
        $controller = new BulletinVacataireController();
        $b = $controller->show($this->import)->getData()['bulletins']->first();
        $this->assertSame('Valide', $b['statut']);
        $this->assertSame('Enseignant test', $b['nom']);
        $this->assertEquals(7840, $b['net_a_payer']);
        $this->assertEquals(3500, $b['frais_transfert']);
        $this->assertEquals(6, $b['entites']->first()['details'][0]['heures_realisees']);
        $this->assertSame('001 234', $b['entites']->first()['compte']);
        $this->assertStringStartsWith('%PDF-', $controller->pdf(new Request(), $this->import)->getContent());
    }

    public function test_legacy_archive_without_hours_still_produces_bulletin(): void
    {
        $this->controller->store(Request::create('/', 'POST', $this->data()), $this->import);
        $ligne = VirementVacataire::first()->lignes->first();
        $ligne->update(['details' => [['mois' => '2026-10', 'taux' => 5.5, 'brut_fiscal' => 12000, 'impot' => 660, 'net_avant_frais' => 11340]]]);
        HeureRealiseeEnseignant::query()->delete();
        $controller = new BulletinVacataireController();
        $b = $controller->show($this->import)->getData()['bulletins']->first();
        $this->assertSame('Valide', $b['statut']);
        $this->assertArrayNotHasKey('heures_realisees', $b['entites']->first()['details'][0]);
        $html = view('Admin.EmploiTemps.bulletins_vacataires.pdf', ['import' => $this->import, 'bulletins' => collect([$b])])->render();
        $this->assertStringContainsString('Detail des heures non archive', $html);
        $this->assertStringStartsWith('%PDF-', $controller->pdf(new Request(), $this->import)->getContent());
    }

    public function test_bulletin_cannot_target_another_teacher(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        (new BulletinVacataireController())->pdf(new Request(['id_personnel' => 999]), $this->import);
    }

    public function test_permanent_attendance_import_does_not_produce_course_bulletins(): void
    {
        $this->import->update(['type_import' => 'permanent']);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        (new BulletinVacataireController())->show($this->import);
    }

    private function ajouterEnseignant(string $nom, ?int $banque, ?string $compte): personnel
    {
        $p = personnel::create(['nom' => $nom, 'id_banque' => $banque, 'niveau_etude' => $compte]);
        $this->ajouterHeure($p->id, 1);

        return $p;
    }

    private function ajouterHeure(int $personnel, int $entite): void
    {
        $h = HeureRealiseeEnseignant::first()->replicate();
        $h->id_personnel = $personnel;
        $h->id_entite = $entite;
        $h->id_seance_cours = HeureRealiseeEnseignant::count() + 1;
        $h->save();
    }

    public function test_bank_groups_are_independent_disjoint_and_support_exclusive_lists(): void
    {
        $autre = $this->ajouterEnseignant('Autre banque', 2, '002-123');
        $sansBanque = $this->ajouterEnseignant('Sans banque', null, '003-123');
        $sansCompte = $this->ajouterEnseignant('Sans compte', 1, '');
        $autreSansCompte = $this->ajouterEnseignant('Autre sans compte', 2, null);
        $cas = [
            [[], [$this->personnel->id]],
            [['inclure_banque' => 0, 'inclure_autres' => 1], [$autre->id]],
            [['inclure_banque' => 0, 'inclure_sans_banque' => 1], [$sansBanque->id, $sansCompte->id, $autreSansCompte->id]],
            [['inclure_autres' => 1], [$this->personnel->id, $autre->id]],
            [['inclure_banque' => 0], []],
            [['inclure_autres' => 1, 'inclure_sans_banque' => 1], [$this->personnel->id, $autre->id, $sansBanque->id, $sansCompte->id, $autreSansCompte->id]],
        ];
        foreach ($cas as [$options, $attendus]) {
            $view = $this->controller->create(new Request($options + ['id_entite' => 1, 'id_banque' => 1]), $this->import);
            $this->assertEqualsCanonicalizing($attendus, $view->getData()['lignes']->pluck('id_personnel')->all());
        }
        $this->controller->store(Request::create('/', 'POST', $this->data([
            'inclure_banque' => 0, 'inclure_sans_banque' => 1, 'personnels' => [$sansBanque->id],
        ])), $this->import);
        $this->assertEquals($sansBanque->id, VirementVacataire::first()->lignes->first()->id_personnel);
    }

    public function test_server_rejects_teacher_from_an_unselected_bank_group(): void
    {
        $sans = $this->ajouterEnseignant('Non affecte', null, '001-456');
        $autre = $this->ajouterEnseignant('Autre banque', 2, '002-456');
        foreach ([
            ['inclure_autres' => 1, 'personnels' => [$sans->id]],
            ['inclure_banque' => 0, 'inclure_sans_banque' => 1, 'personnels' => [$autre->id]],
            ['inclure_banque' => 0, 'inclure_sans_banque' => 1],
        ] as $options) {
            try {
                $this->controller->store(Request::create('/', 'POST', $this->data($options)), $this->import);
                $this->fail('Le groupe non selectionne doit etre refuse.');
            } catch (ValidationException $exception) { $this->assertArrayHasKey('personnels', $exception->errors()); }
        }
        $this->assertEquals(0, VirementVacataire::count());
    }

    public function test_several_orders_exhaust_an_entity_without_repeating_teacher(): void
    {
        $deuxieme = $this->ajouterEnseignant('Deuxieme enseignant', 1, '002-456');
        $troisieme = $this->ajouterEnseignant('Troisieme enseignant', 1, '003-456');
        $this->ajouterHeure($this->personnel->id, 2);
        $filtre = new Request(['id_entite' => 1, 'id_banque' => 1]);
        $ancienFormulaire = $this->data();
        $this->controller->store(Request::create('/', 'POST', $this->data(['personnels' => [$deuxieme->id]])), $this->import);
        $vue = $this->controller->create($filtre, $this->import)->getData();
        $this->assertContains($this->personnel->id, $vue['lignes']->pluck('id_personnel')->all());
        $this->assertNotContains($deuxieme->id, $vue['lignes']->pluck('id_personnel')->all());
        $this->controller->store(Request::create('/', 'POST', $this->data()), $this->import);
        $vue = $this->controller->create($filtre, $this->import)->getData();
        $this->assertSame([$troisieme->id], $vue['lignes']->pluck('id_personnel')->all());
        $this->assertSame(['total' => 3, 'valides' => 2, 'restants' => 1], $vue['suivi']);
        try {
            $this->controller->store(Request::create('/', 'POST', $ancienFormulaire), $this->import);
            $this->fail('Une ancienne page ne doit pas permettre de payer deux fois.');
        } catch (ValidationException $exception) { $this->assertArrayHasKey('personnels', $exception->errors()); }
        $this->controller->store(Request::create('/', 'POST', $this->data(['personnels' => [$troisieme->id]])), $this->import);
        $vue = $this->controller->create($filtre, $this->import)->getData();
        $this->assertCount(0, $vue['lignes']);
        $this->assertSame(0, $vue['suivi']['restants']);
        $this->assertEquals(3, VirementVacataire::count());
        $autreEntite = $this->controller->create(new Request(['id_entite' => 2, 'id_banque' => 1]), $this->import)->getData();
        $this->assertSame([$this->personnel->id], $autreEntite['lignes']->pluck('id_personnel')->all());
        $this->controller->store(Request::create('/', 'POST', $this->data(['id_entite' => 2])), $this->import);
        $this->assertEquals(4, VirementVacataire::count());
    }
}
