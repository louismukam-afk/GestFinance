<?php

namespace App\Exports;

use App\Models\donnee_budgetaire_entree;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Http\Request;

class DonneeBudgetaireEntreesExport implements FromView
{
    use Exportable;

    protected $date_debut;
    protected $date_fin;

    public function __construct($date_debut = null, $date_fin = null)
    {
        $this->date_debut = $date_debut;
        $this->date_fin = $date_fin;
    }

    public function view(): View
    {
        $query = donnee_budgetaire_entree::with(['budgets', 'ligne_budgetaire_entree']);

        // ✅ Appliquer le filtre sur la période si présent
        if (!empty($this->date_debut) && !empty($this->date_fin)) {
            $query->whereBetween('date_creation', [$this->date_debut, $this->date_fin]);
        }

        $donnees = $query->get();

        // ✅ Regrouper par budget puis par ligne
        $grouped = $donnees->groupBy(function ($item) {
            return $item->budgets->libelle_ligne_budget ?? 'Budget inconnu';
        })->map(function ($items) {
            return $items->groupBy(function ($item) {
                return $item->ligne_budgetaire_entree->libelle_ligne_budgetaire_entree ?? 'Ligne inconnue';
            });
        });

        return view('Budget.donnee_entrees.excel', compact('grouped'));
    }
}
