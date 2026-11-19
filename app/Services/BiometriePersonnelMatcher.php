<?php

namespace App\Services;

use App\Models\personnel;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BiometriePersonnelMatcher
{
    public function __construct(private Collection $personnels, private Collection $mappings)
    {
    }

    public function match(?string $name, ?string $number = null): ?personnel
    {
        $number = trim($number ?? '');
        if ($number !== '') {
            $mappings = $this->mappings->filter(fn($mapping) => trim((string) $mapping->numero_biometrie) === $number);
            if ($mappings->isNotEmpty()) {
                return $this->uniquePersonnel($mappings->pluck('id_personnel'));
            }
        }

        $name = $this->normalize($name ?? '');
        if ($name === '') {
            return null;
        }

        $mappings = $this->mappings->filter(fn($mapping) => $this->normalize($mapping->nom_biometrie ?? '') === $name);
        if ($mappings->isNotEmpty()) {
            return $this->uniquePersonnel($mappings->pluck('id_personnel'));
        }

        return $this->uniquePersonnel($this->personnels
            ->filter(fn($personnel) => $this->normalize($personnel->nom_biometrie ?? '') === $name)
            ->pluck('id'));
    }

    private function uniquePersonnel(Collection $ids): ?personnel
    {
        $ids = $ids->unique()->values();

        return $ids->count() === 1 ? $this->personnels->firstWhere('id', $ids->first()) : null;
    }

    private function normalize(string $name): string
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower(Str::ascii($name))) ?? '';
    }
}
