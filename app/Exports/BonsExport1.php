<?php

namespace App\Exports;

use App\Models\bon_commandeok;
use App\Models\element_bon_commande;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class BonsExport1 implements FromView
{
    protected $dateDebut;
    protected $dateFin;
    protected $bonId;

    public function __construct($dateDebut = null, $dateFin = null, $bonId = null)
    {
        $this->dateDebut = $dateDebut;
        $this->dateFin = $dateFin;
        $this->bonId = $bonId;
    }

    public function view(): View
    {
        if ($this->bonId) {
            $bon = bon_commandeok::with(['personnels', 'user'])->findOrFail($this->bonId);
            $elements = element_bon_commande::where('id_bon_commande', $this->bonId)->get();
            return view('Admin.etat_bons.export_one', compact('bon', 'elements'));
        }

        $bons = bon_commandeok::with(['personnels', 'user'])
            ->whereBetween('date_debut', [$this->dateDebut, $this->dateFin])
            ->get();

        return view('Admin.etat_bons.export_all', compact('bons'));
    }
}

