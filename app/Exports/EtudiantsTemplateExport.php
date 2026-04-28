<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EtudiantsTemplateExport implements FromArray, ShouldAutoSize, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return [
            'nom',
            'telephone_whatsapp',
            'date_naissance',
            'lieu_naissance',
            'sexe',
            'email',
            'adresse',
            'departement_origine',
            'region_origine',
            'nom_pere',
            'telephone_whatsapp_pere',
            'nom_mere',
            'nom_tuteur',
            'telephone_tuteur',
            'telephone_2_etudiants',
            'adresse_tuteur',
            'dernier_etablissement_frequente',
        ];
    }

    public function array(): array
    {
        return [
            [
                'Exemple Nom Prenom',
                '690000000',
                '2000-01-31',
                'Yaounde',
                'Masculin',
                'exemple@email.com',
                'Adresse etudiant',
                'Mfoundi',
                'Centre',
                'Nom du pere',
                '691000000',
                'Nom de la mere',
                'Nom du tuteur',
                '692000000',
                '693000000',
                'Adresse du tuteur',
                'Dernier etablissement',
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
