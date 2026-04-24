<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class EtatBudgetaireExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
protected Collection $data;

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    public function collection(): Collection
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Budget',
            'Ligne budgétaire',
            'Donnée budgétaire',
            'Montant prévu',
            'Montant facturé',
            'Montant encaissé',
            'Reste à recouvrer'
        ];
    }

    public function map($row): array
    {
        return [
            $row['budget'],
            $row['ligne'],
            $row['donnee'],
            number_format($row['prevu'], 0, ',', ' '),
            number_format($row['facture'], 0, ',', ' '),
            number_format($row['encaisse'], 0, ',', ' '),
            number_format($row['reste'], 0, ',', ' ')
        ];
    }
}
