<?php

namespace App\Imports;

use App\Models\Matiere;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MatieresImport implements SkipsEmptyRows, ToCollection, WithHeadingRow
{
    private int $created = 0;
    private int $updated = 0;
    private int $skipped = 0;
    private array $errors = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $nom = $this->firstValue($row, ['nom_matiere', 'nom_de_la_matiere', 'matiere', 'nom']);
            $code = $this->firstValue($row, ['code_matiere', 'code_de_la_matiere', 'code']);
            $description = $this->firstValue($row, ['description']);

            if ($nom === '') {
                $this->skip($line, 'Le nom de la matiere est obligatoire.');
                continue;
            }

            if ($code !== '') {
                $matiere = Matiere::where('code_matiere', $code)->first();

                if ($matiere) {
                    $matiere->update([
                        'nom_matiere' => $nom,
                        'description' => $description,
                    ]);
                    $this->updated++;
                    continue;
                }
            } elseif (Matiere::where('nom_matiere', $nom)->exists()) {
                $this->skip($line, "Matiere deja existante sans code : {$nom}.");
                continue;
            }

            Matiere::create([
                'nom_matiere' => $nom,
                'code_matiere' => $code !== '' ? $code : null,
                'description' => $description,
                'id_user' => Auth::id() ?? 0,
            ]);

            $this->created++;
        }
    }

    public function createdCount(): int
    {
        return $this->created;
    }

    public function updatedCount(): int
    {
        return $this->updated;
    }

    public function skippedCount(): int
    {
        return $this->skipped;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    private function skip(int $line, string $message): void
    {
        $this->skipped++;
        $this->errors[] = "Ligne {$line} : {$message}";
    }

    private function firstValue($row, array $keys): string
    {
        foreach ($keys as $key) {
            $value = $row[$key] ?? null;
            $value = $this->clean($value);

            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private function clean($value): string
    {
        if ($value === null) {
            return '';
        }

        $value = trim((string) $value);

        return strtolower($value) === 'null' ? '' : $value;
    }
}
