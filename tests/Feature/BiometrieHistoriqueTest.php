<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\BiometrieHeureController;
use App\Models\BiometrieImport;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BiometrieHistoriqueTest extends TestCase
{
    public function test_history_uses_overlapping_periods_and_includes_manual_but_not_permanent_imports(): void
    {
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        (require database_path('migrations/2026_05_09_000019_create_biometrie_decompte_tables.php'))->up();
        Schema::table('biometrie_imports', fn(Blueprint $table) => $table->string('type_import')->nullable());
        $expected = BiometrieImport::create(['libelle' => 'Import octobre', 'date_debut' => '2026-10-01', 'date_fin' => '2026-10-31', 'type_import' => 'cours']);
        foreach (['permanent', 'manuel'] as $type) {
            BiometrieImport::create(['date_debut' => '2026-10-01', 'date_fin' => '2026-10-31', 'type_import' => $type]);
        }
        BiometrieImport::create(['date_debut' => '2026-09-01', 'date_fin' => '2026-09-30', 'type_import' => 'cours']);
        $request = Request::create('/historique', 'GET', ['date_debut' => '2026-10-12', 'date_fin' => '2026-10-18']);
        $view = (new BiometrieHeureController())->historique($request);
        $imports = $view->getData()['imports'];

        $this->assertCount(2, $imports);
        $this->assertContains($expected->id, $imports->pluck('id')->all());
        $this->assertEquals(0, $imports->first()->heures_realisees_count);
        $this->app['view']->share('errors', new \Illuminate\Support\ViewErrorBag());
        $html = $view->render();
        $this->assertStringContainsString('Import octobre', $html);
        $this->assertStringContainsString('Consolider', $html);
        $this->assertStringNotContainsString('Impact financier', $html);
    }
}
