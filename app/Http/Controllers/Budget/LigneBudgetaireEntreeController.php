<?php

namespace App\Http\Controllers\Budget;

use App\Exports\LigneBudgetaireEntreesExport;
use App\Http\Controllers\Controller;
use App\Models\ligne_budgetaire_Entree;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LigneBudgetaireEntreeController extends Controller
{
    protected $values=[];

    public function __construct()
    {
        $this->middleware('auth');


        $this->values['big_title']='Gestion des budgets';

        $this->values['title']='Ligne budgétaire Entrée';
    }

    public function index()
    {
        $lignes = ligne_budgetaire_Entree::with('user')
            ->orderBy('date_creation','desc')
           ->get();
        $this->values['title']='Ligne budgétaire Entrée';
        return view('Budget/ligne_budgetaire_entrees.index', compact('lignes'),$this->values);
    }

    public function create()
    {
        return view('ligne_budgetaire_entrees.create');
    }

    public function store1(Request $request)
    {
        $request->validate([
            'libelle_ligne_budgetaire_entree' => 'required',
            'code_ligne_budgetaire_entree' => 'required',
            'numero_compte_ligne_budgetaire_entree' => 'required',
            'description' => 'nullable',
            'date_creation' => 'required|date',
        ]);

        ligne_budgetaire_Entree::create($request->all());

        return redirect()->route('ligne_budgetaire_entrees.index')
            ->with('success', 'Ligne budgétaire ajoutée avec succès.');
    }

    public function edit($id)
    {
        $ligne = ligne_budgetaire_Entree::findOrFail($id);
        return view('ligne_budgetaire_entrees.edit', compact('ligne'));
    }

    public function update1(Request $request, $id)
    {
        $ligne = ligne_budgetaire_Entree::findOrFail($id);

        $request->validate([
            'libelle_ligne_budgetaire_entree' => 'required',
            'code_ligne_budgetaire_entree' => 'required',
            'numero_compte_ligne_budgetaire_entree' => 'required',
            'date_creation' => 'required|date',
        ]);

        $ligne->update($request->all());

        return redirect()->route('ligne_budgetaire_entrees.index')
            ->with('success', 'Ligne budgétaire modifiée avec succès.');
    }


    public function store(Request $request)
    {
        $request->validate([
            'libelle_ligne_budgetaire_entree' => 'required|string|max:255',
            'code_ligne_budgetaire_entree' => 'required|string|max:50|unique:ligne_budgetaire_entrees,code_ligne_budgetaire_entree',
            'numero_compte_ligne_budgetaire_entree' => 'required|string|max:50',
            'description' => 'nullable|string',
            'date_creation' => 'required|date',
        ]);

        ligne_budgetaire_Entree::create([
            'libelle_ligne_budgetaire_entree' => $request->libelle_ligne_budgetaire_entree,
            'code_ligne_budgetaire_entree' => $request->code_ligne_budgetaire_entree,
            'numero_compte_ligne_budgetaire_entree' => $request->numero_compte_ligne_budgetaire_entree,
            'description' => $request->description,
            'date_creation' => $request->date_creation,
            'id_user' => Auth::id(), // ✅ utilisateur connecté
        ]);

        return redirect()->route('ligne_budgetaire_entrees.index')
            ->with('success', 'Ligne budgétaire entrée ajoutée avec succès.');
    }

    public function update(Request $request, $id)
    {
        $ligne = ligne_budgetaire_Entree::findOrFail($id);

        $request->validate([
            'libelle_ligne_budgetaire_entree' => 'required|string|max:255',
            'code_ligne_budgetaire_entree' => 'required|string|max:50',
            'numero_compte_ligne_budgetaire_entree' => 'required|string|max:50',
            'date_creation' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $ligne->update([
            'libelle_ligne_budgetaire_entree' => $request->libelle_ligne_budgetaire_entree,
            'code_ligne_budgetaire_entree' => $request->code_ligne_budgetaire_entree,
            'numero_compte_ligne_budgetaire_entree' => $request->numero_compte_ligne_budgetaire_entree,
            'description' => $request->description,
            'date_creation' => $request->date_creation,
            'id_user' => Auth::id(), // ✅ utilisateur connecté mis à jour
        ]);

        return redirect()->route('ligne_budgetaire_entrees.index')
            ->with('success', 'Ligne budgétaire entrée modifiée avec succès.');
    }


    public function destroy($id)
    {
        ligne_budgetaire_Entree::destroy($id);

        return redirect()->route('ligne_budgetaire_entrees.index')
            ->with('success', 'Ligne budgétaire supprimée avec succès.');
    }
    // ✅ Affichage d’une ligne
    public function show($id)
    {
        $ligne = ligne_budgetaire_Entree::findOrFail($id);
        return view('ligne_budgetaire_entrees.show', compact('ligne'));
    }

    // ✅ Export global en PDF
    public function exportPdf()
    {
        $lignes = ligne_budgetaire_Entree::all();
        $pdf = PDF::loadView('ligne_budgetaire_entrees.exports.pdf', compact('lignes'));
        return $pdf->download('lignes_budgetaires_entrees.pdf');
    }

    // ✅ Export global en Excel
    public function exportExcel()
    {
        return Excel::download(new LigneBudgetaireEntreesExport(), 'lignes_budgetaires_entrees.xlsx');
    }

    // ✅ Export individuel en PDF
    public function exportPdfOne($id)
    {
        $ligne = ligne_budgetaire_Entree::findOrFail($id);
        $pdf = PDF::loadView('ligne_budgetaire_entrees.exports.pdf_one', compact('ligne'));
        return $pdf->download('ligne_budgetaire_entree_'.$id.'.pdf');
    }

    // ✅ Export individuel en Excel
    public function exportExcelOne($id)
    {
        $ligne = ligne_budgetaire_Entree::findOrFail($id);
        return Excel::download(new LigneBudgetaireEntreesExport($ligne), 'ligne_budgetaire_entree_'.$id.'.xlsx');
    }

}
