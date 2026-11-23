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
        (require database_path('migrations/2026_11_20_000001_add_requetes_vacataires.php'))->up();
        (require database_path('migrations/2026_11_20_000002_add_type_requete_to_heures.php'))->up();
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

    public function test_order_introduction_uses_archived_entity_bank_and_debit_account(): void
    {
        $this->controller->store(Request::create('/', 'POST', $this->data([
            'id_banque' => 2, 'inclure_autres' => 1, 'compte_debit' => 'COMPTE-987', 'motif' => 'Paiement des cours',
        ])), $this->import);
        $etat = VirementVacataire::firstOrFail()->load('lignes');
        $html = view('Admin.EmploiTemps.virements.pdf', [
            'etat' => $etat, 'total' => $etat->lignes->sum('net_a_payer'), 'totalLettres' => 'test',
        ])->render();
        foreach ([$this->controller->show($etat)->render(), $html] as $document) {
            $this->assertStringContainsString('Par la présente, Institut Superieur Hintel autorise CBC', $document);
            $this->assertStringContainsString('<strong>COMPTE-987</strong>', $document);
        }
        $this->assertLessThan(strpos($html, 'Paiement des cours'), strpos($html, 'Par la présente'));
        $this->assertLessThan(strpos($html, '<table class="liste">'), strpos($html, 'Paiement des cours'));
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
        $ligne = VirementVacataire::first()->lignes->first();
        $this->assertEquals(9840, $ligne->net_a_payer);
        $this->assertEquals(0.25, $ligne->details[0]['ajustement_arrondi_virement']);
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

    public function test_pdf_rounds_each_archived_amount_up_and_sums_the_rounded_lines(): void
    {
        $this->controller->store(Request::create('/', 'POST', $this->data()), $this->import);
        $etat = VirementVacataire::firstOrFail();
        $ligne = $etat->lignes()->firstOrFail();
        $ligne->update(['net_a_payer' => 1000.01]);
        $seconde = $ligne->replicate();
        $seconde->id_personnel = 999;
        $seconde->net_a_payer = 2000.01;
        $seconde->save();
        $response = $this->controller->pdf($etat);
        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $etat->load('lignes');
        $total = $etat->lignes->sum(fn ($item) => ceil((float) $item->net_a_payer));
        $html = view('Admin.EmploiTemps.virements.pdf', ['etat' => $etat, 'total' => $total,
            'totalLettres' => (new \App\Services\MontantEnLettres())->francs($total), 'arrondiArchive' => true])->render();
        $this->assertStringContainsString('1 001</td>', $html);
        $this->assertStringContainsString('2 001</td>', $html);
        $this->assertStringContainsString('3 002</th>', $html);
        $this->assertStringContainsString('valeurs archivees restent inchangees', $html);
        $this->assertEquals(1000.01, $ligne->fresh()->net_a_payer);
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

    private function preparerCoursManuel(): void
    {
        Schema::table('personnels', fn(Blueprint $t) => $t->string('type_personnel')->default('vacataire'));
        Schema::create('programme_specialites', fn(Blueprint $t) => $t->id());
        Schema::create('taux_horaires', function (Blueprint $t) {
            $t->id(); $t->string('libelle'); $t->decimal('montant', 12, 2); $t->string('statut'); $t->boolean('par_defaut'); $t->timestamps();
        });
        Schema::create('plage_horaires', function (Blueprint $t) {
            $t->id(); $t->string('libelle'); $t->string('type_plage'); $t->time('heure_debut'); $t->time('heure_fin');
        });
        Schema::table('cours_enseignants', function (Blueprint $t) {
            foreach (['id_personnel','id_programme_specialite','id_taux_horaire','id_salle','id_cycle','id_filiere','id_niveau','id_specialite','id_annee_academique','id_entite'] as $c) { $t->integer($c)->nullable(); }
            $t->string('statut'); $t->date('date_debut'); $t->date('date_fin'); $t->timestamps();
        });
        Schema::table('seance_cours', function (Blueprint $t) { $t->integer('id_cours_enseignant'); $t->integer('id_plage_horaire'); $t->integer('jour_semaine'); $t->timestamps(); });
        DB::table('taux_horaires')->insert(['id'=>1,'libelle'=>'Cours','montant'=>3000,'statut'=>'actif','par_defaut'=>1]);
        DB::table('plage_horaires')->insert(['id'=>1,'libelle'=>'Cours','type_plage'=>'cours','heure_debut'=>'08:00','heure_fin'=>'16:00']);
        DB::table('cours_enseignants')->insert(['id'=>1,'id_personnel'=>$this->personnel->id,'id_programme_specialite'=>1,'id_taux_horaire'=>1,
            'id_cycle'=>1,'id_filiere'=>1,'id_niveau'=>1,'id_specialite'=>1,'id_annee_academique'=>1,'id_entite'=>1,
            'statut'=>'actif','date_debut'=>'2026-10-01','date_fin'=>'2026-10-31']);
        DB::table('seance_cours')->insert(['id'=>1,'id_cours_enseignant'=>1,'id_plage_horaire'=>1,'jour_semaine'=>4]);
    }

    private function nouvelleRequete(): BiometrieImport
    {
        (new \App\Http\Controllers\Admin\RequeteVacataireController())->store(new Request([
            'id_import_parent' => $this->import->id, 'mois_requete' => '2026-10', 'motif_requete' => 'Presence justifiee et heures rectifiees',
        ]));
        return BiometrieImport::where('type_import', 'requete')->latest('id')->firstOrFail();
    }

    private function saisirHeures(BiometrieImport $import, string $fin, string $decision = 'conserver'): void
    {
        (new \App\Http\Controllers\Admin\BiometrieHeureController())->manualStore(new Request([
            'id_biometrie_import'=>$import->id,'id_cours_enseignant'=>1,'id_seance_cours'=>1,'date_seance'=>'2026-10-01',
            'heure_debut_reelle'=>'08:00','heure_fin_reelle'=>$fin,'observation'=>'Correction apres examen de la requete','decision_sanction'=>$decision,
            'type_requete' => $decision === 'lever' ? 'absence_justifiee' : 'presence_manuelle',
        ]));
    }

    public function test_manual_corrections_require_request_and_unpaid_teacher_can_reach_payment(): void
    {
        $this->preparerCoursManuel();
        $controller = new \App\Http\Controllers\Admin\BiometrieHeureController();
        $view = $controller->manualCreate(new Request(['id_biometrie_import'=>$this->import->id]));
        $this->assertContains($this->import->id, $view->getData()['imports']->pluck('id')->all());
        $this->assertStringContainsString('Reutiliser un import / dossier', $view->render());
        try { $this->saisirHeures($this->import, '15:00'); $this->fail('Une correction doit passer par un dossier'); }
        catch (ValidationException $e) { $this->assertArrayHasKey('import', $e->errors()); }
        $parent = $this->import;
        $this->import = $this->nouvelleRequete();
        $this->saisirHeures($this->import, '15:00');
        $this->assertEquals(7, $this->import->heures_realisees()->first()->duree_realisee);
        $this->assertEquals(6, $parent->heures_realisees()->first()->duree_realisee);
        $this->assertEquals(17010, $controller->impotsImport($this->import)->getData()['lignes']->first()['net']);
        $this->controller->store(Request::create('/', 'POST', $this->data()), $this->import);
        $this->assertEquals(17010, VirementVacataire::first()->lignes->first()->net_a_payer);
        $this->assertStringStartsWith('%PDF-', (new BulletinVacataireController())->pdf(new Request(), $this->import)->getContent());
    }

    public function test_after_request_pays_only_delta_preserves_initial_rate_and_freezes_original(): void
    {
        $this->preparerCoursManuel();
        $this->controller->store(Request::create('/', 'POST', $this->data(['transfert'=>[1=>1], 'frais'=>[1=>3500]])), $this->import);
        $initial = VirementVacataire::first();
        \App\Models\TauxImpot::query()->update(['taux'=>20]);
        $requete = $this->nouvelleRequete();
        $this->saisirHeures($requete, '16:00');
        $form = (new \App\Http\Controllers\Admin\BiometrieHeureController())->manualCreate(new Request(['id_biometrie_import'=>$requete->id]));
        $this->assertStringContainsString('Nature de la requete', $form->render());
        $this->assertEquals(6, $this->import->heures_realisees()->first()->duree_realisee);
        $lignes = (new \App\Services\PaieImportVacataire())->lignes($requete);
        $this->assertEquals(12000, $lignes->first()['brut_fiscal']);
        $this->assertEquals(660, $lignes->first()['impot']);
        $this->assertEquals(11340, $lignes->first()['net_avant_frais']);
        $parent = $this->import;
        $this->import = $requete;
        $this->controller->store(Request::create('/', 'POST', $this->data()), $requete);
        $correctif = VirementVacataire::latest('id')->first();
        $this->assertSame('requete', $correctif->nature);
        $this->assertEquals($parent->id, $correctif->id_import_parent);
        $this->assertEquals(11340, $correctif->lignes->first()->net_a_payer);
        $this->assertEquals(7840, $initial->fresh()->lignes->first()->net_a_payer);
        $this->assertStringStartsWith('%PDF-', $this->controller->pdf($correctif)->getContent());
        $this->assertStringStartsWith('%PDF-', (new BulletinVacataireController())->pdf(new Request(), $requete)->getContent());
        $history = $this->controller->historique(new Request(['nature'=>'requete','id_import'=>$parent->id,'id_entite'=>1,'id_banque'=>1,'mois_requete'=>'2026-10']))->getData();
        $this->assertEquals(1, $history['etats']->total());
        $this->assertEquals(11340, $history['totaux']->net);
        try { $this->saisirHeures($parent, '16:00'); $this->fail('La paie initiale validee doit rester figee'); }
        catch (ValidationException $e) { $this->assertArrayHasKey('import', $e->errors()); }
    }

    public function test_successive_requests_cannot_repay_the_same_supplement(): void
    {
        $this->preparerCoursManuel();
        $this->controller->store(Request::create('/', 'POST', $this->data()), $this->import);
        $parent = $this->import;
        $premiere = $this->nouvelleRequete();
        $this->saisirHeures($premiere, '15:00');
        $this->import = $premiere;
        $this->controller->store(Request::create('/', 'POST', $this->data()), $premiere);
        $this->assertEquals(5670, VirementVacataire::latest('id')->first()->lignes->first()->net_a_payer);
        $this->import = $parent;
        $seconde = $this->nouvelleRequete();
        $this->saisirHeures($seconde, '15:00');
        $this->assertEquals(0, (new \App\Services\PaieImportVacataire())->lignes($seconde)->first()['net_avant_frais']);
        $this->import = $seconde;
        try { $this->controller->store(Request::create('/', 'POST', $this->data()), $seconde); $this->fail('Complement nul'); }
        catch (ValidationException $e) { $this->assertArrayHasKey('personnels', $e->errors()); }
        $this->saisirHeures($seconde, '16:00');
        $this->assertEquals(5670, (new \App\Services\PaieImportVacataire())->lignes($seconde)->first()['net_avant_frais']);
        $this->controller->store(Request::create('/', 'POST', $this->data()), $seconde);
        $this->assertEquals(22680, \App\Models\LigneVirementVacataire::sum('net_avant_frais'));
    }

    public function test_request_rejects_wrong_month_and_employee_without_initial_order(): void
    {
        $this->preparerCoursManuel();
        $this->controller->store(Request::create('/', 'POST', $this->data()), $this->import);
        try {
            (new \App\Http\Controllers\Admin\RequeteVacataireController())->store(new Request(['id_import_parent'=>$this->import->id,'mois_requete'=>'2026-11','motif_requete'=>'Test']));
            $this->fail('Mois hors periode');
        } catch (ValidationException $e) { $this->assertArrayHasKey('mois_requete', $e->errors()); }
        $requete = $this->nouvelleRequete();
        $hors = personnel::create(['nom'=>'Sans ordre initial','type_personnel'=>'vacataire']);
        DB::table('cours_enseignants')->where('id',1)->update(['id_personnel'=>$hors->id]);
        try { $this->saisirHeures($requete, '16:00'); $this->fail('Pas de droit initial'); }
        catch (ValidationException $e) { $this->assertArrayHasKey('id_cours_enseignant', $e->errors()); }
        $this->assertEquals(0, $requete->heures_realisees()->count());
    }

    public function test_waiving_sanction_refunds_only_sanction_without_inventing_worked_hours(): void
    {
        $this->preparerCoursManuel();
        $this->controller->store(Request::create('/', 'POST', $this->data()), $this->import);
        $requete = $this->nouvelleRequete();
        $this->saisirHeures($requete, '14:00', 'lever');
        $heure = $requete->heures_realisees()->first();
        $this->assertEquals(6, $heure->duree_realisee);
        $this->assertTrue($heure->sanction_annulee);
        $ligne = (new \App\Services\PaieImportVacataire())->lignes($requete)->first();
        $this->assertEquals(6000, $ligne['brut_fiscal']);
        $this->assertEquals(330, $ligne['impot']);
        $this->assertEquals(5670, $ligne['net_avant_frais']);
        $this->import = $requete;
        $this->controller->store(Request::create('/', 'POST', $this->data()), $requete);
        $this->assertEquals(5670, VirementVacataire::latest('id')->first()->lignes->first()->net_a_payer);
    }

    public function test_zero_initial_entitlement_can_be_reviewed_without_initial_order_and_cannot_be_paid_twice(): void
    {
        $this->preparerCoursManuel();
        $this->import->heures_realisees()->update(['duree_realisee' => 4, 'montant_total' => 12000]);
        $parent = $this->import;
        $requete = $this->nouvelleRequete();
        $this->saisirHeures($requete, '12:00', 'lever');
        $this->assertEquals(4, $requete->heures_realisees()->first()->duree_realisee);
        $this->assertEquals(4, $parent->heures_realisees()->first()->duree_realisee);
        $this->assertFalse($parent->heures_realisees()->first()->sanction_annulee);
        $ligne = (new \App\Services\PaieImportVacataire())->lignes($requete)->first();
        $this->assertEquals(12000, $ligne['brut_fiscal']);
        $this->assertEquals(660, $ligne['impot']);
        $this->assertEquals(11340, $ligne['net_avant_frais']);
        $this->import = $requete;
        $this->controller->store(Request::create('/', 'POST', $this->data()), $requete);
        $this->assertSame('requete', VirementVacataire::first()->nature);
        $this->assertStringStartsWith('%PDF-', (new BulletinVacataireController())->pdf(new Request(), $requete)->getContent());
        try { $this->saisirHeures($parent, '16:00'); $this->fail('La source du paiement correctif doit rester protegee'); }
        catch (ValidationException $e) { $this->assertArrayHasKey('import', $e->errors()); }
        \App\Models\TauxImpot::query()->update(['taux' => 20]);
        $this->import = $parent;
        $seconde = $this->nouvelleRequete();
        $this->saisirHeures($seconde, '12:00', 'lever');
        $this->assertEquals(0, (new \App\Services\PaieImportVacataire())->lignes($seconde)->first()['net_avant_frais']);
        $this->import = $seconde;
        try { $this->controller->store(Request::create('/', 'POST', $this->data()), $seconde); $this->fail('Complement deja couvert'); }
        catch (ValidationException $e) { $this->assertArrayHasKey('personnels', $e->errors()); }
        $this->assertSame(1, VirementVacataire::count());
    }

    public function test_unpaid_positive_entitlement_can_be_reviewed_and_paid_only_once(): void
    {
        $this->preparerCoursManuel();
        $requete = $this->nouvelleRequete();
        $this->saisirHeures($requete, '14:00', 'lever');
        $parent = $this->import;
        $this->import = $requete;
        $this->controller->store(Request::create('/', 'POST', $this->data()), $requete);
        $this->assertEquals(17010, VirementVacataire::first()->lignes->first()->net_a_payer);
        $this->import = $parent;
        try { $this->controller->store(Request::create('/', 'POST', $this->data()), $parent); $this->fail('Le paiement initial ne peut pas doubler la requete'); }
        catch (ValidationException $e) { $this->assertArrayHasKey('personnels', $e->errors()); }
    }

    public function test_zero_worked_hours_can_be_kept_when_waiving_a_sanction(): void
    {
        $this->preparerCoursManuel();
        $this->import->heures_realisees()->update(['duree_realisee' => 0, 'montant_total' => 0]);
        $requete = $this->nouvelleRequete();
        $this->saisirHeures($requete, '08:00', 'lever');
        $heure = $requete->heures_realisees()->first();
        $this->assertEquals(0, $heure->duree_realisee);
        $this->assertTrue($heure->sanction_annulee);
        $this->assertEquals(0, (new \App\Services\PaieImportVacataire())->lignes($requete)->first()['net_avant_frais']);
    }

    public function test_manual_form_context_matches_posted_import_after_validation_error(): void
    {
        $this->preparerCoursManuel();
        $requete = $this->nouvelleRequete();
        session()->flashInput(['id_biometrie_import' => $this->import->id, 'decision_sanction' => 'lever']);
        $form = (new \App\Http\Controllers\Admin\BiometrieHeureController())->manualCreate(new Request(['id_biometrie_import' => $requete->id]));
        $this->assertEquals($this->import->id, $form->getData()['selectedImport']->id);
        $this->assertStringNotContainsString('id="decisionSanction"', $form->render());
    }

    public function test_three_claim_scenarios_restore_penalty_and_only_pay_attested_hours(): void
    {
        $this->preparerCoursManuel();
        $this->import->heures_realisees()->update([
            'duree_prevue' => 2, 'duree_realisee' => 0, 'montant_total' => 0,
            'heure_fin_prevue' => '10:00:00', 'heure_debut_reelle' => null, 'heure_fin_reelle' => null,
        ]);
        $travaillees = $this->import->heures_realisees()->first()->replicate();
        $travaillees->fill(['id_seance_cours' => 2, 'duree_prevue' => 6, 'duree_realisee' => 6,
            'montant_total' => 18000, 'heure_debut_prevue' => '10:00:00', 'heure_fin_prevue' => '16:00:00']);
        $travaillees->save();
        $this->controller->store(Request::create('/', 'POST', $this->data()), $this->import);
        $requete = $this->nouvelleRequete();
        $controller = new \App\Http\Controllers\Admin\BiometrieHeureController();
        $base = ['id_biometrie_import' => $requete->id, 'id_cours_enseignant' => 1, 'id_seance_cours' => 1,
            'date_seance' => '2026-10-01', 'observation' => 'Releve administratif verifie par la direction'];
        // Une justification doit ignorer meme des horaires de travail envoyes par erreur.
        $controller->manualStore(new Request($base + ['type_requete' => 'absence_justifiee', 'heure_debut_reelle' => '08:00', 'heure_fin_reelle' => '10:00']));
        $heure = $requete->heures_realisees()->where('id_seance_cours', 1)->first();
        $this->assertEquals(0, $heure->duree_realisee);
        $this->assertNull($heure->heure_debut_reelle);
        $this->assertEquals(6000, $heure->sanction_avant_requete);
        $this->assertEquals(6000, (new \App\Services\PaieImportVacataire())->lignes($requete)->first()['brut_fiscal']);
        foreach (['presence_manuelle', 'pointage_incomplet'] as $type) {
            $controller->manualStore(new Request($base + ['type_requete' => $type, 'heure_debut_reelle' => '08:00', 'heure_fin_reelle' => '10:00']));
            $heure = $heure->fresh();
            $this->assertEquals(2, $heure->duree_realisee);
            $this->assertSame($type, $heure->type_requete);
            $this->assertEquals(0, $heure->duree_avant_requete);
            $this->assertEquals(6000, $heure->montant_total);
            $ligne = (new \App\Services\PaieImportVacataire())->lignes($requete)->first();
            $this->assertEquals(12000, $ligne['brut_fiscal']);
            $this->assertEquals(11340, $ligne['net_avant_frais']);
        }
        $this->assertEquals(0, $this->import->heures_realisees()->where('id_seance_cours', 1)->first()->duree_realisee);
    }

    public function test_justified_absence_reimburses_only_the_unpaid_sanction_once(): void
    {
        $this->preparerCoursManuel();
        $this->import->heures_realisees()->update([
            'duree_prevue' => 2, 'duree_realisee' => 0, 'montant_total' => 0,
            'heure_fin_prevue' => '10:00:00', 'heure_debut_reelle' => null, 'heure_fin_reelle' => null,
        ]);
        $autresHeures = $this->import->heures_realisees()->first()->replicate();
        $autresHeures->fill(['id_seance_cours' => 2, 'duree_prevue' => 6, 'duree_realisee' => 6,
            'montant_total' => 18000, 'heure_debut_prevue' => '10:00:00', 'heure_fin_prevue' => '16:00:00']);
        $autresHeures->save();

        $parent = $this->import;
        $this->controller->store(Request::create('/', 'POST', $this->data()), $parent);
        $ordreInitial = VirementVacataire::firstOrFail();
        $this->assertEquals(12000, $ordreInitial->lignes->first()->brut_fiscal);

        $requete = $this->nouvelleRequete();
        $this->saisirHeures($requete, '10:00', 'lever');
        $absence = $requete->heures_realisees()->where('id_seance_cours', 1)->firstOrFail();
        $this->assertEquals(0, $absence->duree_realisee);
        $this->assertEquals(0, $absence->montant_total);
        $this->assertEquals(6000, $absence->sanction_avant_requete);
        $this->assertTrue($absence->sanction_annulee);
        $ligne = (new \App\Services\PaieImportVacataire())->lignes($requete)->first();
        $this->assertEquals(6000, $ligne['brut_fiscal']);
        $this->assertEquals(330, $ligne['impot']);
        $this->assertEquals(5670, $ligne['net_avant_frais']);

        $this->import = $requete;
        $this->controller->store(Request::create('/', 'POST', $this->data()), $requete);
        $this->assertEquals(5670, VirementVacataire::latest('id')->firstOrFail()->lignes->first()->net_a_payer);
        $this->assertEquals(12000, $ordreInitial->fresh()->lignes->first()->brut_fiscal);

        $this->import = $parent;
        $seconde = $this->nouvelleRequete();
        $this->saisirHeures($seconde, '10:00', 'lever');
        $this->assertEquals(0, (new \App\Services\PaieImportVacataire())->lignes($seconde)->first()['net_avant_frais']);
        $this->import = $seconde;
        try {
            $this->controller->store(Request::create('/', 'POST', $this->data()), $seconde);
            $this->fail('La meme sanction ne doit pas etre restituee deux fois.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('personnels', $e->errors());
        }
        $this->assertSame(2, VirementVacataire::count());
    }

    public function test_request_tracking_filters_imports_and_edits_correction_and_motif(): void
    {
        $this->preparerCoursManuel();
        $parent = $this->import;
        $requete = $this->nouvelleRequete();
        $this->saisirHeures($requete, '14:00', 'lever');
        $correction = $requete->heures_realisees()->whereNotNull('type_requete')->firstOrFail();
        $controller = new \App\Http\Controllers\Admin\RequeteVacataireController();
        $request = new Request(['id_import_parent' => $parent->id]);
        $this->app->instance('request', $request);
        $data = $controller->index($request)->getData();
        $this->assertCount(1, $data['requetes']);
        $this->assertSame($requete->id, $data['requetes']->first()->id);
        $this->assertStringContainsString('Modifier', $controller->index($request)->render());
        $controller->update(new Request(['motif_requete' => 'Certificat medical verifie']), $requete);
        $this->assertSame('Certificat medical verifie', $requete->fresh()->motif_requete);
        $view = (new \App\Http\Controllers\Admin\BiometrieHeureController())->manualCreate(new Request([
            'id_biometrie_import' => $requete->id, 'modifier_correction' => $correction->id,
        ]));
        $this->assertSame($correction->id, $view->getData()['correction']->id);
        $this->assertStringContainsString('id_heure_correction', $view->render());
        (new \App\Http\Controllers\Admin\BiometrieHeureController())->manualStore(new Request([
            'id_biometrie_import' => $requete->id, 'id_heure_correction' => $correction->id,
            'id_cours_enseignant' => 1, 'id_seance_cours' => 1, 'date_seance' => '2026-10-01',
            'type_requete' => 'absence_justifiee', 'observation' => 'Certificat medical confirme',
        ]));
        $this->assertStringContainsString('Certificat medical confirme', $correction->fresh()->observation);
        $this->import = $requete;
        $this->controller->store(Request::create('/', 'POST', $this->data()), $requete);
        try { $controller->update(new Request(['motif_requete' => 'Changement interdit']), $requete); $this->fail('Dossier deja valide'); }
        catch (ValidationException $e) { $this->assertArrayHasKey('motif_requete', $e->errors()); }
        try { $this->saisirHeures($requete, '14:00', 'lever'); $this->fail('Correction deja payee'); }
        catch (ValidationException $e) { $this->assertArrayHasKey('import', $e->errors()); }
    }

    public function test_claim_reference_shows_source_hours_and_single_biometric_punch_without_copying(): void
    {
        $this->preparerCoursManuel();
        $requete = $this->nouvelleRequete();
        \App\Models\BiometriePointage::create(['id_biometrie_import' => $this->import->id,
            'id_personnel' => $this->personnel->id, 'date_heure_pointage' => '2026-10-01 08:02:00']);
        $request = new Request(['id_cours_enseignant' => 1, 'id_seance_cours' => 1, 'date_seance' => '2026-10-01']);
        $json = (new \App\Http\Controllers\Admin\BiometrieHeureController())->referenceRequete($request, $requete)->getData(true);
        $this->assertEquals(6, $json['duree_realisee']);
        $this->assertEquals(6000, $json['sanction']);
        $this->assertSame(['08:02'], $json['pointages']);
        $this->assertSame(0, $requete->heures_realisees()->count());
    }

    public function test_claim_requires_attested_hours_and_justification_for_presence(): void
    {
        $this->preparerCoursManuel();
        $requete = $this->nouvelleRequete();
        $base = ['id_biometrie_import' => $requete->id, 'id_cours_enseignant' => 1, 'id_seance_cours' => 1,
            'date_seance' => '2026-10-01', 'type_requete' => 'pointage_incomplet'];
        try {
            (new \App\Http\Controllers\Admin\BiometrieHeureController())->manualStore(new Request($base));
            $this->fail('Un pointage incomplet doit etre appuye par des horaires attestes et une justification');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('heure_debut_reelle', $e->errors());
            $this->assertArrayHasKey('observation', $e->errors());
        }
        $this->assertSame(0, $requete->heures_realisees()->count());
    }

    public function test_zero_initial_amount_keeps_corrective_tax_rate_across_entities(): void
    {
        $this->preparerCoursManuel();
        $this->import->heures_realisees()->update(['duree_realisee' => 4, 'montant_total' => 12000]);
        $this->ajouterHeure($this->personnel->id, 2);
        $requete = $this->nouvelleRequete();
        $this->saisirHeures($requete, '12:00', 'lever');
        $requete->heures_realisees()->update(['sanction_annulee' => true]);
        $this->import = $requete;
        $this->controller->store(Request::create('/', 'POST', $this->data()), $requete);
        \App\Models\TauxImpot::query()->update(['taux' => 20]);
        $ligne = (new \App\Services\PaieImportVacataire())->lignes($requete)->firstWhere('id_entite', 2);
        $this->assertEquals(660, $ligne['impot']);
        $this->controller->store(Request::create('/', 'POST', $this->data(['id_entite' => 2])), $requete);
        $this->assertEquals(22680, \App\Models\LigneVirementVacataire::sum('net_a_payer'));
    }

    public function test_request_cannot_bypass_duplicate_payment_check_on_an_unrelated_import(): void
    {
        $this->preparerCoursManuel();
        $this->controller->store(Request::create('/', 'POST', $this->data()), $this->import);
        $autre = $this->import->replicate();
        $autre->save();
        $heure = $this->import->heures_realisees()->first()->replicate();
        $heure->id_biometrie_import = $autre->id;
        $heure->save();
        $this->import = $autre;
        $requete = $this->nouvelleRequete();
        $this->saisirHeures($requete, '16:00');
        $this->import = $requete;
        try { $this->controller->store(Request::create('/', 'POST', $this->data()), $requete); $this->fail('Un autre import deja paye ne doit pas etre contourne'); }
        catch (ValidationException $e) { $this->assertArrayHasKey('personnels', $e->errors()); }
        $this->assertSame(1, VirementVacataire::count());
    }

    public function test_reduction_in_rights_cannot_create_a_supplementary_payment(): void
    {
        $this->preparerCoursManuel();
        $this->controller->store(Request::create('/', 'POST', $this->data()), $this->import);
        $requete = $this->nouvelleRequete();
        $this->saisirHeures($requete, '13:00');
        $ligne = (new \App\Services\PaieImportVacataire())->lignes($requete)->first();
        $this->assertLessThan(0, $ligne['net_avant_frais']);
        $this->assertTrue($ligne['reprise_a_verifier']);
        $this->import = $requete;
        try { $this->controller->store(Request::create('/', 'POST', $this->data()), $requete); $this->fail('Diminution de droits'); }
        catch (ValidationException $e) { $this->assertArrayHasKey('personnels', $e->errors()); }
        $this->assertEquals(1, VirementVacataire::count());
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

    public function test_unpaid_request_can_be_deleted_without_touching_initial_import_or_other_requests(): void
    {
        $this->preparerCoursManuel();
        $this->controller->store(Request::create('/', 'POST', $this->data()), $this->import);
        $parent = $this->import;
        $requete = $this->nouvelleRequete();
        $this->saisirHeures($requete, '16:00');
        $autre = $this->nouvelleRequete();
        $controller = new \App\Http\Controllers\Admin\BiometrieHeureController();
        $html = $controller->manualCreate(new Request(['id_biometrie_import' => $requete->id]))->render();
        $this->assertStringContainsString('Supprimer le dossier', $html);
        $this->assertStringContainsString(route('biometrie_heures.destroy', $requete), $html);
        $response = $controller->destroy($requete);
        $this->assertSame(route('biometrie_heures.manual.create'), $response->getTargetUrl());
        $this->assertNull(BiometrieImport::find($requete->id));
        $this->assertSame(0, HeureRealiseeEnseignant::where('id_biometrie_import', $requete->id)->count());
        $this->assertNotNull($parent->fresh());
        $this->assertNotNull($autre->fresh());
        $this->assertEquals(6, $parent->heures_realisees()->first()->duree_realisee);
        $this->assertSame(1, VirementVacataire::count());
    }

    public function test_request_with_validated_order_cannot_be_deleted(): void
    {
        $this->preparerCoursManuel();
        $this->controller->store(Request::create('/', 'POST', $this->data()), $this->import);
        $requete = $this->nouvelleRequete();
        $this->saisirHeures($requete, '16:00');
        $this->import = $requete;
        $this->controller->store(Request::create('/', 'POST', $this->data()), $requete);
        try {
            (new \App\Http\Controllers\Admin\BiometrieHeureController())->destroy($requete);
            $this->fail('Un dossier avec ordre valide doit etre conserve.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('import', $exception->errors());
        }
        try {
            (new \App\Http\Controllers\Admin\BiometrieHeureController())->clearConsolidation($requete);
            $this->fail('Les heures servant au paiement valide doivent etre conservees.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('import', $exception->errors());
        }
        $this->assertNotNull($requete->fresh());
        $this->assertSame(1, $requete->heures_realisees()->count());
        $this->assertSame(2, VirementVacataire::count());
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
