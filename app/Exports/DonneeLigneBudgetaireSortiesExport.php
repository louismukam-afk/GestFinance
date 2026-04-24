<?php

namespace App\Exports;

use App\Models\donnee_ligne_budgetaire_sortie;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DonneeLigneBudgetaireSortiesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $donnee_id;

    public function __construct($donnee_id)
    {
        $this->donnee_id = $donnee_id;
    }

    /**
     * Récupération des lignes en base
     */
    public function collection()
    {
        return donnee_ligne_budgetaire_sortie::with(['element_ligne_budgetaire_sorties','budgets','donnee_budgetaire_sorties'])
            ->where('id_donnee_budgetaire_sortie', $this->donnee_id)
            ->get();
    }

    /**
     * Mapping d’une ligne → une ligne Excel
     */
    public function map($ligne): array
    {
        return [
            $ligne->donnee_ligne_budgetaire_sortie,
            $ligne->code_donnee_ligne_budgetaire_sortie,
            $ligne->numero_donne_ligne_budgetaire_sortie,
            $ligne->description,
            $ligne->date_creation,
            $ligne->element_ligne_budgetaire_sorties->libelle_elements_ligne_budgetaire_sortie ?? '-',
            $ligne->budget->libelle_ligne_budget ?? '-',
            $ligne->donnee_budgetaire_sorties->donnee_ligne_budgetaire_sortie ?? '-',
            $ligne->id_user,
        ];
    }

    /**
     * En-têtes Excel
     */
    public function headings(): array
    {
        return [
            'Libellé',
            'Code',
            'N° Compte',
            'Description',
            'Date création',
            'Élément ligne budgétaire sortie',
            'Budget',
            'Donnée budgétaire sortie',
            'Utilisateur',
        ];
    }
}
