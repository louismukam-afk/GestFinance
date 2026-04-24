<?php

namespace App\Exports;

use App\Models\Budget;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class BudgetsExport implements FromView
{
    protected $date_debut;
    protected $date_fin;

    public function __construct($date_debut = null, $date_fin = null)
    {
        $this->date_debut = $date_debut;
        $this->date_fin = $date_fin;
    }

    public function view(): View
    {
        $query = Budget::query();

        if ($this->date_debut && $this->date_fin) {
            $query->whereBetween('date_creation', [$this->date_debut, $this->date_fin]);
        }

        $budgets = $query->with('user')->get();

        return view('Budget.excel', compact('budgets'));
    }
}
