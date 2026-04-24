<?php

namespace App\Exports;

use App\Models\ligne_budgetaire_Sortie;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LigneBudgetaireSortiesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $ligne;

    public function __construct($ligne = null)
    {
        $this->ligne = $ligne;
    }

    /**
     * ✅ Exporter soit toutes les lignes, soit une seule.
     */
    public function collection()
    {
        if ($this->ligne) {
            return collect([$this->ligne]);
        }
        return ligne_budgetaire_Sortie::all();
    }

    /**
     * ✅ En-têtes Excel.
     */
    public function headings(): array
    {
        return [
            '#',
            'Libellé',
            'Code',
            'N° Compte',
            'Description',
            'Date Création'
        ];
    }

    /**
     * ✅ Format des données exportées.
     */
    public function map($ligne): array
    {
        return [
            $ligne->id,
            $ligne->libelle_ligne_budgetaire_sortie,
            $ligne->code_ligne_budgetaire_sortie,
            $ligne->numero_compte_ligne_budgetaire_sortie,
            $ligne->description,
            $ligne->date_creation,
        ];
    }
}
