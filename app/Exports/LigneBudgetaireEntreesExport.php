<?php

namespace App\Exports;

use App\Models\ligne_budgetaire_Entree;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LigneBudgetaireEntreesExport implements FromCollection, WithHeadings
{
    protected $ligne;

    public function __construct($ligne = null)
    {
        $this->ligne = $ligne;
    }

    public function collection()
    {
        if ($this->ligne) {
            return collect([$this->ligne]);
        }
        return ligne_budgetaire_Entree::all();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Libellé',
            'Code',
            'N° Compte',
            'Description',
            'Date Création',
            'Utilisateur',
            'Créé le',
            'Mis à jour le'
        ];
    }
}
