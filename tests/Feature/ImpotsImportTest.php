<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\BiometrieHeureController;
use App\Models\BiometrieImport;
use App\Models\HeureRealiseeEnseignant;
use App\Models\personnel;
use App\Models\TauxImpot;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ImpotsImportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('personnels', function (Blueprint $table) {
            $table->id(); $table->string('nom'); $table->timestamps();
        });
        foreach (['cours_enseignants', 'seance_cours'] as $table) {
            Schema::create($table, fn(Blueprint $schema) => $schema->id());
        }
        (require database_path('migrations/2026_05_09_000019_create_biometrie_decompte_tables.php'))->up();
        Schema::table('biometrie_imports', fn(Blueprint $table) => $table->string('type_import')->nullable());
        (require database_path('migrations/2026_11_19_000002_create_taux_impots_table.php'))->up();
        (require database_path('migrations/2026_11_19_000003_add_id_taux_impot_to_personnels_table.php'))->up();
        $this->app['view']->share('errors', new \Illuminate\Support\ViewErrorBag());
    }

    private function importAvecHeures(personnel $personnel): BiometrieImport
    {
        $import = BiometrieImport::create(['date_debut' => '2026-10-01', 'date_fin' => '2026-10-31', 'type_import' => 'cours']);
        HeureRealiseeEnseignant::create([
            'id_biometrie_import' => $import->id, 'id_personnel' => $personnel->id,
            'id_cours_enseignant' => 1, 'id_seance_cours' => 1, 'id_programme_specialite' => 1,
            'id_cycle' => 1, 'id_filiere' => 1, 'id_niveau' => 1, 'id_specialite' => 1,
            'id_annee_academique' => 1, 'id_entite' => 1, 'id_plage_horaire' => 1,
            'date_seance' => '2026-10-01', 'jour_semaine' => 4,
            'heure_debut_prevue' => '08:00:00', 'heure_fin_prevue' => '16:00:00',
            'duree_prevue' => 8, 'duree_realisee' => 6, 'montant_taux' => 3000, 'montant_total' => 18000,
        ]);

        return $import;
    }

    public function test_preparation_uses_default_and_saves_personnel_rate_for_pdf(): void
    {
        $personnel = personnel::create(['nom' => 'Test fiscal']);
        $import = $this->importAvecHeures($personnel);
        $controller = new BiometrieHeureController();
        $view = $controller->impotsImport($import);
        $this->assertEquals(660, $view->getData()['lignes'][0]['impot']);
        $this->assertStringContainsString('12 000,00', $view->render());
        $taux = TauxImpot::create(['nom' => 'Taux de test', 'taux' => 10]);
        $controller->enregistrerImpotsImport(Request::create('/impots', 'POST', ['taux_personnels' => [$personnel->id => $taux->id]]), $import);
        $this->assertEquals($taux->id, $personnel->fresh()->id_taux_impot);
        $lignes = $controller->impotsImport($import)->getData()['lignes'];
        $this->assertEquals(1200, $lignes[0]['impot']);
        $this->assertEquals(10800, $lignes[0]['net']);
        $response = $controller->pdfImpotsImport($import);
        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->assertStringContainsString('inline', $response->headers->get('Content-Disposition'));
        $controller->enregistrerImpotsImport(Request::create('/impots', 'POST', ['taux_personnels' => [$personnel->id => null]]), $import);
        $this->assertNull($personnel->fresh()->id_taux_impot);
    }

    public function test_tax_updates_cannot_target_personnel_outside_the_import(): void
    {
        $personnel = personnel::create(['nom' => 'Dans import']);
        $outside = personnel::create(['nom' => 'Hors import']);
        $import = $this->importAvecHeures($personnel);
        $taux = TauxImpot::where('par_defaut', true)->firstOrFail();
        try {
            (new BiometrieHeureController())->enregistrerImpotsImport(Request::create('/impots', 'POST', ['taux_personnels' => [$personnel->id => $taux->id, $outside->id => $taux->id]]), $import);
            $this->fail('Le personnel hors import doit etre refuse.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('taux_personnels', $exception->errors());
        }
        $this->assertNull($personnel->fresh()->id_taux_impot);
        $this->assertNull($outside->fresh()->id_taux_impot);
    }
}
