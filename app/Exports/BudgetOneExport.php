<?php

namespace App\Exports;

use App\Models\Budget;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class BudgetOneExport implements FromView
{
    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function view(): View
    {
        $budget = Budget::with('user')->findOrFail($this->id);

        return view('Budget.excel_one', compact('budget'));
    }
}
