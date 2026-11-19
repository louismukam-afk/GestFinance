<?php

namespace App\Exports;

use App\Services\DecompteHeuresEnseignant;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class HeuresRealiseesExport implements WithMultipleSheets
{
    public function __construct(private Collection $heures)
    {
    }

    public function sheets(): array
    {
        return [
            new HeuresRealiseesDetailExport($this->heures),
            new HeuresRealiseesRecapExport((new DecompteHeuresEnseignant())->totaux($this->heures)),
        ];
    }
}
