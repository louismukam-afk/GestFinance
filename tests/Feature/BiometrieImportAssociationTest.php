<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\BiometrieHeureController;
use App\Models\BiometrieImport;
use App\Models\BiometriePointage;
use App\Models\personnel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

class BiometrieImportAssociationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('personnels', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('nom_biometrie')->nullable();
            $table->string('type_personnel')->default('vacataire');
            $table->timestamps();
        });
        Schema::create('cours_enseignants', function (Blueprint $table) {
            $table->id();
            $table->integer('id_personnel');
            $table->string('statut');
            $table->date('date_debut');
            $table->date('date_fin');
        });
        Schema::create('seance_cours', function (Blueprint $table) {
            $table->id();
            $table->integer('id_cours_enseignant');
            $table->integer('id_plage_horaire');
        });
        Schema::create('plage_horaires', function (Blueprint $table) {
            $table->id();
            $table->string('type_plage');
        });
        (require database_path('migrations/2026_05_09_000019_create_biometrie_decompte_tables.php'))->up();
        (require database_path('migrations/2026_05_09_000020_create_biometrie_personnel_mappings_table.php'))->up();
    }

    public function test_import_applies_manual_mappings_immediately_and_reassociation_refreshes_the_counter(): void
    {
        $first = personnel::create(['nom' => 'Civil', 'nom_biometrie' => 'Jean Test']);
        $second = personnel::create(['nom' => 'Autre civil', 'nom_biometrie' => 'Autre nom']);
        $this->planifier($second->id);
        DB::table('biometrie_personnel_mappings')->insert([
            'id_personnel' => $second->id, 'nom_biometrie' => 'Alias manuel', 'numero_biometrie' => '41',
        ]);
        $import = BiometrieImport::create(['date_debut' => '2026-10-01', 'date_fin' => '2026-10-31']);
        $path = tempnam(sys_get_temp_dir(), 'bio');
        file_put_contents($path, "Department,Name,No,Date/Time\nCompany,Jean Test,41,01/10/2026 08:00:00\nCompany,Civil,42,01/10/2026 10:00:00\n");
        $controller = new BiometrieHeureController();
        try {
            $result = (new ReflectionMethod($controller, 'importBiometriePointages'))->invoke($controller, $import, $path);
        } finally {
            unlink($path);
        }
        $this->assertSame([2, 1, 0], $result);
        $this->assertEquals($second->id, $import->pointages()->where('numero_biometrie', '41')->first()->id_personnel);
        $this->assertNull($import->pointages()->where('numero_biometrie', '42')->first()->id_personnel);

        // Simule une ancienne association approximative et un compteur obsolete.
        $import->pointages()->where('numero_biometrie', '41')->update(['id_personnel' => $first->id]);
        $import->update(['total_non_associees' => 99]);
        (new ReflectionMethod($controller, 'reassocierPointages'))->invoke($controller, $import);
        $this->assertEquals($second->id, BiometriePointage::where('numero_biometrie', '41')->first()->id_personnel);
        $this->assertEquals(1, $import->fresh()->total_non_associees);
    }

    private function planifier(int $personnelId, string $start = '2026-09-01', string $end = '2026-12-31'): void
    {
        $cours = DB::table('cours_enseignants')->insertGetId([
            'id_personnel' => $personnelId, 'statut' => 'actif', 'date_debut' => $start, 'date_fin' => $end,
        ]);
        $plage = DB::table('plage_horaires')->insertGetId(['type_plage' => 'cours']);
        DB::table('seance_cours')->insert(['id_cours_enseignant' => $cours, 'id_plage_horaire' => $plage]);
    }

    public function test_only_teachers_with_planned_courses_are_imported_without_restricting_the_permanent_import(): void
    {
        $vacataire = personnel::create(['nom' => 'Vacataire', 'nom_biometrie' => 'Vacataire', 'type_personnel' => 'vacataire']);
        $permanent = personnel::create(['nom' => 'Permanent', 'nom_biometrie' => 'Permanent', 'type_personnel' => 'permanent']);
        $sansCours = personnel::create(['nom' => 'Administration', 'nom_biometrie' => 'Administration', 'type_personnel' => 'permanent']);
        $horsPeriode = personnel::create(['nom' => 'Septembre', 'nom_biometrie' => 'Septembre', 'type_personnel' => 'vacataire']);
        $this->planifier($vacataire->id);
        $this->planifier($permanent->id);
        $this->planifier($horsPeriode->id, '2026-09-01', '2026-09-30');
        $import = BiometrieImport::create(['date_debut' => '2026-10-01', 'date_fin' => '2026-10-31']);
        $path = tempnam(sys_get_temp_dir(), 'bio');
        file_put_contents($path, "Department,Name,No,Date/Time\nCompany,Vacataire,1,01/10/2026 08:00:00\nCompany,Permanent,2,01/10/2026 08:00:00\nCompany,Administration,3,01/10/2026 08:00:00\nCompany,Septembre,4,01/10/2026 08:00:00\nCompany,Inconnu,5,01/10/2026 08:00:00\n");
        $controller = new BiometrieHeureController();
        $method = new ReflectionMethod($controller, 'importBiometriePointages');
        try {
            $this->assertSame([3, 1, 2], $method->invoke($controller, $import, $path));
            $this->assertEqualsCanonicalizing([$vacataire->id, $permanent->id], $import->pointages()->whereNotNull('id_personnel')->pluck('id_personnel')->all());
            $import->type_import = 'permanent';
            $this->assertSame([5, 1, 0], $method->invoke($controller, $import, $path));
        } finally {
            unlink($path);
        }
    }
}
