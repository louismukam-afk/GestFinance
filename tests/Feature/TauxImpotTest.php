<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\TauxImpotController;
use App\Models\TauxImpot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TauxImpotTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        (require database_path('migrations/2026_11_19_000002_create_taux_impots_table.php'))->up();
        $this->app['view']->share('errors', new \Illuminate\Support\ViewErrorBag());
    }

    public function test_default_is_seeded_and_can_be_replaced_but_not_accidentally_removed(): void
    {
        $this->assertSame('5.50', TauxImpot::where('par_defaut', true)->sole()->taux);
        $controller = new TauxImpotController();
        $controller->store(Request::create('/taux', 'POST', ['nom' => 'Taux specifique', 'taux' => '10,25', 'par_defaut' => 1]));
        $taux = TauxImpot::where('nom', 'Taux specifique')->sole();
        $this->assertSame('10.25', $taux->taux);
        $this->assertEquals($taux->id, TauxImpot::where('par_defaut', true)->sole()->id);
        $controller->update(Request::create('/taux', 'PUT', ['nom' => 'Taux specifique', 'taux' => '10.50']), $taux);
        $this->assertTrue($taux->fresh()->par_defaut);
        $this->assertStringContainsString('10.50', $controller->edit($taux->fresh())->render());
        $this->assertStringContainsString('Taux specifique', $controller->index()->render());
        $this->assertStringContainsString('5.50', $controller->create()->render());
    }

    public function test_rates_above_one_hundred_percent_are_rejected(): void
    {
        $this->expectException(ValidationException::class);
        (new TauxImpotController())->store(Request::create('/taux', 'POST', ['nom' => 'Invalide', 'taux' => 101]));
    }
}
