<?php

namespace Tests\Unit;

use App\Models\personnel;
use App\Services\BiometriePersonnelMatcher;
use PHPUnit\Framework\TestCase;

class BiometriePersonnelMatcherTest extends TestCase
{
    public function test_only_the_biometric_name_is_used_for_automatic_matching(): void
    {
        $personnel = new personnel(['nom' => 'Nom civil', 'nom_biometrie' => 'Jean-Paul Test']);
        $personnel->id = 1;
        $matcher = new BiometriePersonnelMatcher(collect([$personnel]), collect());

        $this->assertSame(1, $matcher->match(' JEAN PAUL TEST ')?->id);
        $this->assertNull($matcher->match('Nom civil'));
        $this->assertNull($matcher->match('Jean Paul'));
        $this->assertNull($matcher->match(''));
    }

    public function test_manual_number_mapping_takes_precedence_over_a_name(): void
    {
        $first = new personnel(['nom_biometrie' => 'Jean Test']);
        $first->id = 1;
        $second = new personnel(['nom_biometrie' => 'Autre nom']);
        $second->id = 2;
        $mapping = (object) ['id_personnel' => 2, 'numero_biometrie' => '263', 'nom_biometrie' => 'Ancien nom'];
        $matcher = new BiometriePersonnelMatcher(collect([$first, $second]), collect([$mapping]));

        $this->assertSame(2, $matcher->match('Jean Test', '263')?->id);
        $this->assertSame(2, $matcher->match('Ancien nom')?->id);
    }

    public function test_ambiguous_names_and_conflicting_manual_numbers_remain_unmatched(): void
    {
        $first = new personnel(['nom_biometrie' => 'Jean Test']);
        $first->id = 1;
        $second = new personnel(['nom_biometrie' => 'Jean Test']);
        $second->id = 2;
        $mappings = collect([1, 2])->map(fn($id) => (object) ['id_personnel' => $id, 'numero_biometrie' => '9', 'nom_biometrie' => null]);
        $matcher = new BiometriePersonnelMatcher(collect([$first, $second]), $mappings);

        $this->assertNull($matcher->match('Jean Test'));
        $this->assertNull($matcher->match('Jean Test', '9'));
    }
}
