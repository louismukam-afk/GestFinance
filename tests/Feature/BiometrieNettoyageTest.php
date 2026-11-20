<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\BiometrieHeureController;
use App\Models\{BiometrieImport, BiometriePointage, HeureRealiseeEnseignant, personnel};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Schema};
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BiometrieNettoyageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('personnels', function (Blueprint $t) { $t->id(); $t->string('nom'); $t->string('type_personnel'); $t->timestamps(); });
        Schema::create('cours_enseignants', function (Blueprint $t) { $t->id(); $t->integer('id_personnel'); $t->string('statut'); });
        Schema::create('seance_cours', function (Blueprint $t) { $t->id(); $t->integer('id_cours_enseignant'); $t->integer('id_plage_horaire'); });
        Schema::create('plage_horaires', function (Blueprint $t) { $t->id(); $t->string('type_plage'); });
        (require database_path('migrations/2026_05_09_000019_create_biometrie_decompte_tables.php'))->up();
        Schema::table('biometrie_imports', fn(Blueprint $t) => $t->string('type_import')->nullable());
        $this->app['view']->share('errors', new \Illuminate\Support\ViewErrorBag());
    }

    private function import(string $nom = 'Import principal', string $type = 'cours'): BiometrieImport
    {
        return BiometrieImport::create(['libelle' => $nom, 'date_debut' => '2026-10-01', 'date_fin' => '2026-10-31', 'type_import' => $type]);
    }

    private function heure(?int $idImport): HeureRealiseeEnseignant
    {
        $p = personnel::create(['nom' => 'Enseignant '.HeureRealiseeEnseignant::count(), 'type_personnel' => 'vacataire']);
        return HeureRealiseeEnseignant::create([
            'id_biometrie_import' => $idImport, 'id_personnel' => $p->id,
            'id_cours_enseignant' => 1, 'id_seance_cours' => $p->id, 'id_programme_specialite' => 1,
            'id_cycle' => 1, 'id_filiere' => 1, 'id_niveau' => 1, 'id_specialite' => 1,
            'id_annee_academique' => 1, 'id_entite' => 1, 'id_plage_horaire' => 1,
            'date_seance' => '2026-10-01', 'jour_semaine' => 4,
            'heure_debut_prevue' => '08:00:00', 'heure_fin_prevue' => '16:00:00',
            'duree_prevue' => 8, 'duree_realisee' => 6, 'montant_taux' => 3000, 'montant_total' => 18000,
        ]);
    }

    private function pointage(int $idImport, string $nom): BiometriePointage
    {
        return BiometriePointage::create(['id_biometrie_import' => $idImport, 'nom_biometrie' => $nom, 'date_heure_pointage' => '2026-10-01 08:00:00']);
    }

    public function test_initial_page_hides_details_and_selection_only_loads_one_existing_import(): void
    {
        $premier = $this->import();
        $autre = $this->import('Autre import');
        $heure = $this->heure($premier->id);
        $this->heure($autre->id);
        $this->heure(null);
        $this->pointage($premier->id, 'Identite principale');
        $this->pointage($autre->id, 'Identite autre');
        $controller = new BiometrieHeureController();
        $view = $controller->index(new Request());
        $this->assertCount(0, $view->getData()['heures']);
        $this->assertCount(0, $view->getData()['biometrieIdentites']);
        $html = $view->render();
        $this->assertStringContainsString('Visualiser', $html);
        $this->assertStringNotContainsString('Detail des heures consolidees', $html);
        $this->assertStringNotContainsString('Totaux par enseignant', $html);
        $this->assertEquals(1, $view->getData()['orphelins']['heures']);
        $export = (new \ReflectionMethod($controller, 'heuresRealiseesCollection'))->invoke($controller, new Request());
        $this->assertCount(2, $export);
        $this->assertFalse($export->contains(fn($h) => $h->id_biometrie_import === null));
        $request = new Request(['id_biometrie_import' => $premier->id]);
        $this->app->instance('request', $request);
        $view = $controller->index($request);
        $this->assertSame([$heure->id], $view->getData()['heures']->pluck('id')->all());
        $this->assertSame(['Identite principale'], $view->getData()['biometrieIdentites']->pluck('nom_biometrie')->all());
        $html = $view->render();
        $this->assertStringContainsString('Detail des heures consolidees', $html);
        $this->assertStringContainsString('Totaux par enseignant', $html);
        $this->assertStringNotContainsString('Identite autre', $html);
    }

    public function test_deleted_or_permanent_import_cannot_be_visualised(): void
    {
        $permanent = $this->import('Permanent', 'permanent');
        foreach ([999, $permanent->id] as $id) {
            try {
                (new BiometrieHeureController())->index(new Request(['id_biometrie_import' => $id]));
                $this->fail('Import non disponible');
            } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { $this->assertEquals(404, $e->getStatusCode()); }
        }
    }

    public function test_cleanup_only_deletes_orphans_and_preserves_imports_and_archives(): void
    {
        $cours = $this->import();
        $permanent = $this->import('Permanent', 'permanent');
        $valide = $this->heure($cours->id);
        $this->heure(null);
        $pointage = $this->pointage($cours->id, 'Conserver');
        $pointagePermanent = $this->pointage($permanent->id, 'Permanent');
        Schema::disableForeignKeyConstraints();
        $this->pointage(999, 'Orphelin');
        Schema::enableForeignKeyConstraints();
        Schema::create('virements_vacataires', fn(Blueprint $t) => $t->id());
        DB::table('virements_vacataires')->insert(['id' => 1]);
        (new BiometrieHeureController())->nettoyerOrphelins(new Request(['confirmation' => 1]));
        $this->assertSame([$valide->id], HeureRealiseeEnseignant::pluck('id')->all());
        $this->assertEqualsCanonicalizing([$pointage->id, $pointagePermanent->id], BiometriePointage::pluck('id')->all());
        $this->assertEquals(2, BiometrieImport::count());
        $this->assertEquals(1, DB::table('virements_vacataires')->count());
    }

    public function test_cleanup_requires_confirmation_and_guest_is_rejected(): void
    {
        $this->get(route('biometrie_heures.index'))->assertRedirect(route('login'));
        $this->delete(route('biometrie_heures.nettoyer_orphelins'), ['confirmation' => 1])->assertRedirect(route('login'));
        $this->heure(null);
        try {
            (new BiometrieHeureController())->nettoyerOrphelins(new Request());
            $this->fail('Confirmation obligatoire');
        } catch (ValidationException $e) { $this->assertArrayHasKey('confirmation', $e->errors()); }
        $this->assertEquals(1, HeureRealiseeEnseignant::count());
    }

    public function test_deleting_an_import_removes_its_hours_without_touching_other_import(): void
    {
        $supprime = $this->import();
        $conserve = $this->import('Conserver');
        $this->heure($supprime->id);
        $heure = $this->heure($conserve->id);
        $this->pointage($supprime->id, 'Supprimer');
        $pointage = $this->pointage($conserve->id, 'Conserver');
        $response = (new BiometrieHeureController())->destroy($supprime);
        $this->assertSame(route('biometrie_heures.index'), $response->getTargetUrl());
        $this->assertSame([$heure->id], HeureRealiseeEnseignant::pluck('id')->all());
        $this->assertSame([$pointage->id], BiometriePointage::pluck('id')->all());
    }
}
