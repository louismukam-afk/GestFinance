<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\ligne_budgetaire_Entree;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BudgetsExport;
use App\Exports\BudgetOneExport;
use function SebastianBergmann\Diff\diffToArray;

class BudgetController extends Controller
{

    protected $values=[];

    public function __construct()
    {
        $this->middleware('auth');


        $this->values['big_title']='Gestion des budgets';

        $this->values['title']='Ligne budgétaire';
    }

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
        $this->values['title']='Ligne budgétaire';
        return view('Budget.index', compact('budgets', 'users'), $this->values);
    }

    /**
     * Afficher un budget spécifique.
     */
    public function show($id)
    {
        $budget = Budget::with('user')->findOrFail($id);
        return view('Budget.show', compact('budget'));
    }

    public function create()
{
    // Tu peux charger des données complémentaires si besoin (ex: users)
    $users = \App\Models\User::all();

    return view('Budget.create', compact('users'));
}


    /**
     * Enregistrer un nouveau budget.
     */
   public function store(Request $request)
    {
        $request->validate([
            'libelle_ligne_budget' => 'required|string|max:255',
            'code_budget' => 'required|string|max:50|unique:budgets',
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

        return redirect()->route('budgets.index')->with('success', 'Budget créé avec succès.');
    }

   /* public function store(Request $request)
    {
        $request->validate([
            'libelle_ligne_budgetaire_entree'   => 'required|string|max:255',
            'code_ligne_budgetaire_entree'      => 'required|string|max:50|unique:ligne_budgetaire__entrees',
            'numero_compte_ligne_budgetaire_entree' => 'required|string|max:100',
            'date_creation'                     => 'required|date',
        ]);
          dump($request);
          die();
        $ligne = new ligne_budgetaire_Entree();
        $ligne->libelle_ligne_budgetaire_entree = $request->libelle_ligne_budgetaire_entree;
        $ligne->code_ligne_budgetaire_entree = $request->code_ligne_budgetaire_entree;
        $ligne->numero_compte_ligne_budgetaire_entree = $request->numero_compte_ligne_budgetaire_entree;
        $ligne->description = $request->description;
        $ligne->date_creation = $request->date_creation;
        $ligne->id_user = Auth::id(); // utilisateur connecté
        $ligne->save();

        return redirect()->route('ligne_budgetaire_entrees.index')
            ->with('success', 'Ligne budgétaire ajoutée avec succès.');
    }*/


    /**
     * Mettre à jour un budget existant.
     */

   /* public function update(Request $request, $id)
    {
        $ligne = ligne_budgetaire_Entree::findOrFail($id);

        $request->validate([
            'libelle_ligne_budgetaire_entree'   => 'required|string|max:255',
            'code_ligne_budgetaire_entree'      => 'required|string|max:50|unique:ligne_budgetaire__entrees,code_ligne_budgetaire_entree,' . $ligne->id,
            'numero_compte_ligne_budgetaire_entree' => 'required|string|max:100',
            'description'                       => 'nullable|string',
            'date_creation'                     => 'required|date',
        ]);

        $ligne->libelle_ligne_budgetaire_entree = $request->libelle_ligne_budgetaire_entree;
        $ligne->code_ligne_budgetaire_entree = $request->code_ligne_budgetaire_entree;
        $ligne->numero_compte_ligne_budgetaire_entree = $request->numero_compte_ligne_budgetaire_entree;
        $ligne->description = $request->description;
        $ligne->date_creation = $request->date_creation;
        $ligne->id_user = Auth::id(); // on met à jour l’utilisateur qui modifie
        $ligne->save();

        return redirect()->route('ligne_budgetaire_entrees.index')
            ->with('success', 'Ligne budgétaire modifiée avec succès.');
    }*/

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

        return redirect()->route('budgets.index')->with('success', 'Budget mis à jour avec succès.');
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
    // ✅ Formulaire édition
   /* public function edit(Budget $budget)
    {
        return view('Budget.edit', compact('budget'));
    }*/
    public function edit($id)
    {
        $budget = Budget::findOrFail($id); // Récupère bien l'objet
        return view('Budget.edit', compact('budget'));
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
