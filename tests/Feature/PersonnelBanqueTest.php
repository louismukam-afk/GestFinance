<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\PersonnelBanqueController;
use App\Models\banque;
use App\Models\personnel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PersonnelBanqueTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('banques', function (Blueprint $table) {
            $table->id(); $table->string('nom_banque'); $table->string('code')->nullable(); $table->timestamps();
        });
        Schema::create('personnels', function (Blueprint $table) {
            $table->id(); $table->string('nom'); $table->string('niveau_etude')->nullable(); $table->timestamps();
        });
        (require database_path('migrations/2026_11_19_000001_add_id_banque_to_personnels_table.php'))->up();
        $this->app['view']->share('errors', new \Illuminate\Support\ViewErrorBag());
    }

    public function test_batch_assignment_transfer_and_print_preserve_account_numbers(): void
    {
        $cca = banque::create(['nom_banque' => 'CCA']);
        $other = banque::create(['nom_banque' => 'Autre banque']);
        $first = personnel::create(['nom' => 'Alice', 'niveau_etude' => '000123456789']);
        $second = personnel::create(['nom' => 'Bob', 'niveau_etude' => '000987654321']);
        $unselected = personnel::create(['nom' => 'Claire', 'niveau_etude' => '001111']);
        $controller = new PersonnelBanqueController();
        $controller->store(Request::create('/affecter', 'POST', ['personnels' => [$first->id, $second->id]]), $cca);
        $this->assertEquals($cca->id, $first->fresh()->id_banque);
        $this->assertEquals($cca->id, $second->fresh()->id_banque);
        $this->assertNull($unselected->fresh()->id_banque);
        $this->assertSame('000123456789', $first->fresh()->niveau_etude);
        $controller->update(Request::create('/banque', 'PUT', ['id_banque' => $other->id]), $second);
        $this->assertEquals($other->id, $second->fresh()->id_banque);
        $view = $controller->show(Request::create('/liste', 'GET', ['imprimer' => 1]), $cca);
        $this->assertSame('Admin.PersonnelBanque.imprimer', $view->name());
        $html = $view->render();
        $this->assertStringContainsString('000123456789', $html);
        $this->assertStringNotContainsString('Bob', $html);
        $this->assertStringContainsString('Affecter', $controller->create($cca)->render());
        $this->assertStringContainsString('CCA', $controller->index()->render());
        $controller->destroy($cca, $first);
        $this->assertNull($first->fresh()->id_banque);
    }

    public function test_invalid_selection_does_not_partially_assign_staff(): void
    {
        $bank = banque::create(['nom_banque' => 'CCA']);
        $personnel = personnel::create(['nom' => 'Alice']);
        try {
            (new PersonnelBanqueController())->store(Request::create('/affecter', 'POST', ['personnels' => [$personnel->id, 999999]]), $bank);
            $this->fail('La selection invalide doit etre refusee.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('personnels.1', $exception->errors());
        }
        $this->assertNull($personnel->fresh()->id_banque);
    }
}
