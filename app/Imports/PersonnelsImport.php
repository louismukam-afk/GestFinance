<?php

namespace App\Imports;

use App\Models\personnel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class PersonnelsImport implements SkipsEmptyRows, ToCollection, WithHeadingRow
{
    private int $created = 0;
    private int $skipped = 0;
    private array $errors = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $nom = $this->clean($row['nom'] ?? null);
            $dateNaissance = $this->parseDate($row['date_naissance'] ?? null);
            $lieuNaissance = $this->clean($row['lieu_naissance'] ?? null);
            $adresse = $this->clean($row['adresse'] ?? null);
            $sexe = $this->normalizeSexe($row['sexe'] ?? null);
            $statut = $this->normalizeStatut($row['statut_matrimonial'] ?? null);
            $telephone = $this->clean($row['telephone'] ?? null);
            $dateRecrutement = $this->parseDate($row['date_recrutement'] ?? null);
            $nationalite = $this->clean($row['nationalite'] ?? null);

            if ($nom === '') {
                $this->skip($line, 'Le nom est obligatoire.');
                continue;
            }

            if (! $dateNaissance) {
                $this->skip($line, 'La date de naissance est obligatoire ou invalide.');
                continue;
            }

            if ($lieuNaissance === '') {
                $this->skip($line, 'Le lieu de naissance est obligatoire.');
                continue;
            }

            if ($adresse === '') {
                $this->skip($line, 'L adresse est obligatoire.');
                continue;
            }

            if ($sexe === '') {
                $this->skip($line, 'Le sexe est obligatoire. Valeurs acceptees : Masculin, Feminin, Autre.');
                continue;
            }

            if ($statut === '') {
                $this->skip($line, 'Le statut matrimonial est obligatoire. Valeurs acceptees : Celibataire, Marie(e), Divorce(e), Veuf(ve).');
                continue;
            }

            if ($telephone === '') {
                $this->skip($line, 'Le telephone est obligatoire.');
                continue;
            }

            if (! $dateRecrutement) {
                $this->skip($line, 'La date de recrutement est obligatoire ou invalide.');
                continue;
            }

            if ($nationalite === '') {
                $this->skip($line, 'La nationalite est obligatoire.');
                continue;
            }

            $email = $this->clean($row['email'] ?? null);
            if ($email !== '' && personnel::where('email', $email)->exists()) {
                $this->skip($line, "Email deja existant : {$email}.");
                continue;
            }

            if (personnel::where('nom', $nom)->whereDate('date_naissance', $dateNaissance)->exists()) {
                $this->skip($line, 'Personnel deja existant avec le meme nom et la meme date de naissance.');
                continue;
            }

            personnel::create([
                'nom' => $nom,
                'date_naissance' => $dateNaissance,
                'lieu_naissance' => $lieuNaissance,
                'adresse' => $adresse,
                'sexe' => $sexe,
                'statut_matrimonial' => $statut,
                'email' => $email,
                'telephone' => $telephone,
                'telephone_whatsapp' => $this->clean($row['telephone_whatsapp'] ?? null),
                'numero_cnps' => $this->clean($row['numero_cnps'] ?? null),
                'numero_contribuable' => $this->clean($row['numero_contribuable'] ?? null),
                'diplome' => $this->clean($row['diplome'] ?? null),
                'niveau_etude' => $this->clean($row['niveau_etude'] ?? null),
                'domaine_formation' => $this->clean($row['domaine_formation'] ?? null),
                'date_recrutement' => $dateRecrutement,
                'nationalite' => $nationalite,
                'type_personnel' => $this->normalizeIn($row['type_personnel'] ?? null, ['permanent', 'vacataire'], 'permanent'),
                'mode_horaire' => $this->normalizeIn($row['mode_horaire'] ?? null, ['strict', 'souple'], 'strict'),
                'categorie_horaire' => $this->normalizeIn($row['categorie_horaire'] ?? null, ['standard', 'conseil_administration', 'chef_service', 'coordination'], 'standard'),
                'horaire_travail' => $this->normalizeIn($row['horaire_travail'] ?? null, ['permanent_jour', 'permanent_soir', 'vacataire_jour', 'vacataire_soir'], null),
                'id_user' => Auth::id() ?? 0,
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
        $value = $this->normalizeKey($value);

        if (in_array($value, ['m', 'masculin', 'homme'], true)) {
            return 'Masculin';
        }

        if (in_array($value, ['f', 'feminin', 'femme'], true)) {
            return 'Féminin';
        }

        return $value === 'autre' ? 'Autre' : '';
    }

    private function normalizeStatut($value): string
    {
        $value = str_replace([' ', '_'], '', $this->normalizeKey($value));

        if ($value === 'celibataire') {
            return 'Célibataire';
        }

        if (in_array($value, ['marie', 'mariee', 'marie(e)'], true)) {
            return 'Marié(e)';
        }

        if (in_array($value, ['divorce', 'divorcee', 'divorce(e)'], true)) {
            return 'Divorcé(e)';
        }

        if (in_array($value, ['veuf', 'veuve', 'veuf(ve)'], true)) {
            return 'Veuf(ve)';
        }

        return '';
    }

    private function normalizeIn($value, array $allowed, $default)
    {
        $value = $this->normalizeKey($value);

        return in_array($value, $allowed, true) ? $value : $default;
    }

    private function normalizeKey($value): string
    {
        $value = mb_strtolower($this->clean($value));
        $value = strtr($value, [
            'à' => 'a',
            'á' => 'a',
            'â' => 'a',
            'ã' => 'a',
            'ä' => 'a',
            'ç' => 'c',
            'è' => 'e',
            'é' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'î' => 'i',
            'ï' => 'i',
            'ô' => 'o',
            'ö' => 'o',
            'ù' => 'u',
            'û' => 'u',
            'ü' => 'u',
            'ÿ' => 'y',
        ]);

        return trim($value);
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
