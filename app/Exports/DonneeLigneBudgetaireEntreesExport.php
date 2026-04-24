<?php

namespace App\Exports;

use App\Models\donnee_ligne_budgetaire_entree;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;

class DonneeLigneBudgetaireEntreesExport implements FromView
{
    use Exportable;

    protected $donnee_id;

    public function __construct($donnee_id)
    {
        $this->donnee_id = $donnee_id;
    }

    /**
     * Exportation via une vue Blade
     */
    public function view(): View
    {
        $lignes = donnee_ligne_budgetaire_entree::with([
            'element_ligne_budgetaire_entrees',
            'ligne_budgetaire_entrees',
            'budget',
            'donnee_budgetaire_entrees'
        ])
            ->where('id_donnee_budgetaire_entree', $this->donnee_id)
            ->orderBy('date_creation', 'desc')
            ->get();

        return view('exports.donnee_ligne_budgetaire_entrees', [
            'lignes' => $lignes
        ]);
    }
}
