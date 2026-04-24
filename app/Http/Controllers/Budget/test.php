<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BudgetsExport;
use App\Exports\BudgetOneExport;

class BudgetController extends Controller
{
    /**
     * Afficher la liste des budgets avec filtres (date début/fin).
     */
    public function index(Request $request)
    {
        $query = Budget::with('user')->orderBy('date_debut', 'desc');

        if ($request->filled('date_debut')) {
            $query->where('date_debut', '>=', $request->date_debut);
        }
        if ($request->filled('date_fin')) {
            $query->where('date_fin', '<=', $request->date_fin);
        }

        $budgets = $query->get();
        $users = User::all();

        return view('Budget.index', compact('budgets', 'users'));
    }

    /**
     * Afficher un budget spécifique.
     */
    public function show($id)
    {
        $budget = Budget::with('user')->findOrFail($id);
        return view('Budget.show', compact('budget'));
    }

    /**
     * Enregistrer un nouveau budget.
     */
    public function store(Request $request)
    {
        $request->validate([
            'libelle_ligne_budget' => 'required|string|max:255',
            'code_budget' => 'required|string|max:50|unique:budgets',
            'description' => 'required|string',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'date_creation' => 'required|date',
            'montant_global' => 'required|numeric|min:0',
        ]);

        $budget = new Budget();
        $budget->libelle_ligne_budget = $request->libelle_ligne_budget;
        $budget->code_budget = $request->code_budget;
        $budget->description = $request->description;
        $budget->date_debut = $request->date_debut;
        $budget->date_fin = $request->date_fin;
        $budget->date_creation = $request->date_creation;
        $budget->montant_global = $request->montant_global;
        $budget->id_user = Auth::id();
        $budget->save();

        return redirect()->route('Budget.index')->with('success', 'Budget créé avec succès.');
    }

    /**
     * Mettre à jour un budget existant.
     */
    public function update(Request $request, $id)
    {
        $budget = Budget::findOrFail($id);

        $request->validate([
            'libelle_ligne_budget' => 'required|string|max:255',
            'code_budget' => 'required|string|max:50|unique:budgets,code_budget,' . $budget->id,
            'description' => 'required|string',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'date_creation' => 'required|date',
            'montant_global' => 'required|numeric|min:0',
        ]);

        $budget->update($request->all());

        return redirect()->route('Budget.index')->with('success', 'Budget mis à jour avec succès.');
    }

    /**
     * Supprimer un budget.
     */
    public function destroy($id)
    {
        $budget = Budget::findOrFail($id);
        $budget->delete();

        return redirect()->route('budgets.index')->with('success', 'Budget supprimé avec succès.');
    }

    /**
     * Exporter tous les budgets en PDF.
     */
    public function exportPdf(Request $request)
    {
        $query = Budget::query();

        if ($request->filled('date_debut')) {
            $query->where('date_debut', '>=', $request->date_debut);
        }
        if ($request->filled('date_fin')) {
            $query->where('date_fin', '<=', $request->date_fin);
        }

        $budgets = $query->get();

        $pdf = PDF::loadView('Budget.export_pdf', compact('budgets'));
        return $pdf->download('Budget.pdf');
    }

    /**
     * Exporter tous les budgets en Excel.
     */
    public function exportExcel(Request $request)
    {
        return Excel::download(new BudgetsExport($request), 'budgets.xlsx');
    }

    /**
     * Exporter un budget individuel en PDF.
     */
    public function exportPdfOne($id)
    {
        $budget = Budget::with('user')->findOrFail($id);
        $pdf = PDF::loadView('Budget.export_pdf_one', compact('budget'));
        return $pdf->download('budget_'.$budget->id.'.pdf');
    }

    /**
     * Exporter un budget individuel en Excel.
     */
    public function exportExcelOne($id)
    {
        return Excel::download(new BudgetOneExport($id), 'budget_'.$id.'.xlsx');
    }
}
