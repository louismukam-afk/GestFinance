<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PersonnelsTemplateExport implements FromArray, ShouldAutoSize, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return [
            'nom',
            'date_naissance',
            'lieu_naissance',
            'adresse',
            'sexe',
            'statut_matrimonial',
            'email',
            'telephone',
            'telephone_whatsapp',
            'numero_cnps',
            'numero_contribuable',
            'diplome',
            'niveau_etude',
            'domaine_formation',
            'date_recrutement',
            'nationalite',
            'type_personnel',
            'mode_horaire',
            'categorie_horaire',
            'horaire_travail',
        ];
    }

    public function array(): array
    {
        return [
            [
                'Exemple Nom Prenom',
                '1990-01-31',
                'Yaounde',
                'Adresse du personnel',
                'Masculin',
                'Celibataire',
                'personnel@example.com',
                '690000000',
                '691000000',
                'CNPS-001',
                'CONT-001',
                'Licence',
                'Bac+3',
                'Comptabilite',
                '2026-01-15',
                'Camerounaise',
                'permanent',
                'strict',
                'standard',
                'permanent_jour',
            ],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('1')->getFont()->setBold(true);
        $sheet->getStyle('1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE8F0FE');

        return [];
    }
}
