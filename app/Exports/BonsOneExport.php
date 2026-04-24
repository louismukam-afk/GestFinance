<?php

namespace App\Exports;

use App\Models\bon_commandeok;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class BonsOneExport implements FromView
{
    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function view(): View
    {
        $bon = bon_commandeok::with(['personnels','user','entites','element_bon_commandes'])->findOrFail($this->id);
        return view('Admin.etat_bons.excel_one', compact('bon'));
    }
}
