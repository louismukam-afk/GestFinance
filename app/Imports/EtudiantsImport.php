<?php

namespace App\Imports;

use App\Models\Etudiant;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class EtudiantsImport implements SkipsEmptyRows, ToCollection, WithHeadingRow
{
    private int $created = 0;
    private int $skipped = 0;
    private array $errors = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $nom = $this->clean($row['nom'] ?? null);
            $sexe = $this->normalizeSexe($row['sexe'] ?? null);

            if ($nom === '') {
                $this->skip($line, 'Le nom est obligatoire.');
                continue;
            }

            if ($sexe === '') {
                $this->skip($line, 'Le sexe est obligatoire. Valeurs acceptees : Masculin, Feminin, Autre.');
                continue;
            }

            $email = $this->clean($row['email'] ?? null);
            $dateNaissance = $this->parseDate($row['date_naissance'] ?? null);
            $telephone = $this->clean($row['telephone_whatsapp'] ?? null);

            if ($email !== '' && Etudiant::where('email', $email)->exists()) {
                $this->skip($line, "Email deja existant : {$email}.");
                continue;
            }

            if ($nom !== '' && $dateNaissance && Etudiant::where('nom', $nom)->whereDate('date_naissance', $dateNaissance)->exists()) {
                $this->skip($line, "Etudiant deja existant avec le meme nom et la meme date de naissance.");
                continue;
            }

            Etudiant::create([
                'nom' => $nom,
                'telephone_whatsapp' => $telephone,
                'date_naissance' => $dateNaissance,
                'lieu_naissance' => $this->clean($row['lieu_naissance'] ?? null),
                'sexe' => $sexe,
                'email' => $email,
                'adresse' => $this->clean($row['adresse'] ?? null),
                'departement_origine' => $this->clean($row['departement_origine'] ?? null),
                'region_origine' => $this->clean($row['region_origine'] ?? null),
                'nom_pere' => $this->clean($row['nom_pere'] ?? null),
                'telephone_whatsapp_pere' => $this->clean($row['telephone_whatsapp_pere'] ?? null),
                'nom_mere' => $this->clean($row['nom_mere'] ?? null),
                'nom_tuteur' => $this->clean($row['nom_tuteur'] ?? null),
                'telephone_tuteur' => $this->clean($row['telephone_tuteur'] ?? null),
                'matricule' => '',
                'telephone_2_etudiants' => $this->clean($row['telephone_2_etudiants'] ?? null),
                'adresse_tuteur' => $this->clean($row['adresse_tuteur'] ?? null),
                'photo' => '',
                'dernier_etablissement_frequente' => $this->clean($row['dernier_etablissement_frequente'] ?? null),
            ]);

            $this->created++;
        }
    }

    public function createdCount(): int
    {
        return $this->created;
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

    private function clean($value): string
    {
        if ($value === null) {
            return '';
        }

        $value = trim((string) $value);

        return strtolower($value) === 'null' ? '' : $value;
    }

    private function normalizeSexe($value): string
    {
        $value = mb_strtolower($this->clean($value));

        return match ($value) {
            'm', 'masculin', 'homme' => 'Masculin',
            'f', 'feminin', 'féminin', 'femme' => 'Féminin',
            'autre' => 'Autre',
            default => '',
        };
    }

    private function parseDate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return ExcelDate::excelToDateTimeObject($value)->format('Y-m-d');
        }

        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}
