<?php

namespace App\Exports;

use App\Models\donnee_budgetaire_sortie;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class DonneeBudgetaireSortieExport implements FromView, ShouldAutoSize, WithTitle
{
    protected $date_debut;
    protected $date_fin;

    public function __construct($date_debut = null, $date_fin = null)
    {
        $this->date_debut = $date_debut;
        $this->date_fin   = $date_fin;
    }

    public function view(): View
    {
        $query = donnee_budgetaire_sortie::with(['budget', 'ligne_budgetaire_sortie']);

        if ($this->date_debut && $this->date_fin) {
            $query->whereBetween('date_creation', [$this->date_debut, $this->date_fin]);
        }

        $donnees = $query->get();

        return view('Budget.donnee_sorties.excel', compact('donnees'));
    }

    public function title(): string
    {
        return 'Données budgetaires sortie';
    }
}
