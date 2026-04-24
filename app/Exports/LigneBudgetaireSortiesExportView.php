<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class LigneBudgetaireSortiesExportView implements FromView
{
    protected $lignes;
    protected $view;

    /**
     * @param array|\Illuminate\Support\Collection $lignes
     * @param string $view
     */
    public function __construct($lignes, $view)
    {
        $this->lignes = $lignes;
        $this->view   = $view;
    }

    /**
     * Retourne la vue Blade à exporter vers Excel
     */
    public function view(): View
    {
        return view($this->view, [
            'lignes' => $this->lignes
        ]);
    }
}
