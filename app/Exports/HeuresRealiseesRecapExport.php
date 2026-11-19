<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class HeuresRealiseesRecapExport implements FromCollection, WithHeadings, WithTitle
{
    public function __construct(private Collection $totaux)
    {
    }

    public function headings(): array
    {
        return ['Enseignant', 'Mois', 'Heures prevues', 'Heures realisees', 'Heures sanctionnees', 'Heures payables', 'Montant brut', 'Sanction', 'Net a payer'];
    }

    public function collection(): Collection
    {
        return $this->totaux->map(fn($total) => array_values($total));
    }

    public function title(): string
    {
        return 'Recapitulatif mensuel';
    }
}
