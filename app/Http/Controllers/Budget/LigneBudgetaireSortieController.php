<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Models\ligne_budgetaire_sortie;
use Illuminate\Http\Request;
use App\Exports\LigneBudgetaireSortiesExport;
use App\Exports\LigneBudgetaireSortiesExportView;
use Illuminate\Support\Facades\Auth;

class LigneBudgetaireSortieController extends Controller
{
    protected $values=[];

    public function __construct()
    {
        $this->middleware('auth');


        $this->values['big_title']='Gestion des budgets';

        $this->values['title']='Ligne budgétaire Sorties';
    }


    public function index()
    {
        $lignes = ligne_budgetaire_sortie::with('user')
            ->orderBy('date_creation','desc')
            ->get();
        $this->values['title']='Ligne budgétaire Sorties';
        return view('Budget/ligne_budgetaire_sorties.index', compact('lignes'),$this->values);
    }

    public function create()
    {
        return view('Budget/ligne_budgetaire_sorties.create');
    }

    public function store1(Request $request)
    {
        $request->validate([
            'libelle_ligne_budgetaire_sortie' => 'required|string|max:255',
            'code_ligne_budgetaire_sortie' => 'required|string|max:50',
            'numero_compte_ligne_budgetaire_sortie' => 'required|string|max:50',
            'description' => 'nullable|string',
            'date_creation' => 'required|date',
            'id_user'=>Auth::id(),// ✅ l'utilisateur connecté
        ]);

       ligne_budgetaire_sortie::create($request->all());

        return redirect()->route('ligne_budgetaire_sorties.index')
            ->with('success', 'Ligne budgétaire sortie ajoutée avec succès.');
    }

    public function store(Request $request)
    {
        // Validation
        $request->validate([
            'libelle_ligne_budgetaire_sortie' => 'required|string|max:255',
            'code_ligne_budgetaire_sortie' => 'required|string|max:50|unique:ligne_budgetaire_sorties,code_ligne_budgetaire_sortie',
            'numero_compte_ligne_budgetaire_sortie' => 'required|string|max:50',
            'description' => 'nullable|string',
            'date_creation' => 'required|date',
        ]);

        // Création avec l'utilisateur connecté
        ligne_budgetaire_sortie::create([
            'libelle_ligne_budgetaire_sortie' => $request->libelle_ligne_budgetaire_sortie,
            'code_ligne_budgetaire_sortie' => $request->code_ligne_budgetaire_sortie,
            'numero_compte_ligne_budgetaire_sortie' => $request->numero_compte_ligne_budgetaire_sortie,
            'description' => $request->description,
            'date_creation' => $request->date_creation,
            'id_user' => Auth::id(), // ✅ Enregistre l’utilisateur connecté
        ]);

        return redirect()->route('ligne_budgetaire_sorties.index')
            ->with('success', 'Ligne budgétaire sortie ajoutée avec succès.');
    }


    public function edit($id)
    {
        $ligne = ligne_budgetaire_sortie::findOrFail($id);
        return view('Budget/ligne_budgetaire_sorties.edit', compact('ligne'));
    }



    public function update(Request $request, $id)
    {
        $ligne = ligne_budgetaire_sortie::findOrFail($id);

        // Validation
        $request->validate([
            'libelle_ligne_budgetaire_sortie' => 'required|string|max:255',
            'code_ligne_budgetaire_sortie' => 'required|string|max:50',
            'numero_compte_ligne_budgetaire_sortie' => 'required|string|max:50',
            'date_creation' => 'required|date',
            'description' => 'nullable|string',
        ]);

        // Mise à jour avec l'utilisateur connecté
        $ligne->update([
            'libelle_ligne_budgetaire_sortie' => $request->libelle_ligne_budgetaire_sortie,
            'code_ligne_budgetaire_sortie' => $request->code_ligne_budgetaire_sortie,
            'numero_compte_ligne_budgetaire_sortie' => $request->numero_compte_ligne_budgetaire_sortie,
            'description' => $request->description,
            'date_creation' => $request->date_creation,
            'id_user' => Auth::id(), // ✅ l'utilisateur connecté
        ]);

        return redirect()->route('ligne_budgetaire_sorties.index')
            ->with('success', 'Ligne budgétaire sortie modifiée avec succès.');
    }

    public function update1(Request $request, $id)
    {
        $ligne = ligne_budgetaire_sortie::findOrFail($id);

        $request->validate([
            'libelle_ligne_budgetaire_sortie' => 'required|string|max:255',
            'code_ligne_budgetaire_sortie' => 'required|string|max:50',
            'numero_compte_ligne_budgetaire_sortie' => 'required|string|max:50',
            'date_creation' => 'required|date',
            'id_user' => Auth::id(),// utilisateur connecté

        ]);

        $ligne->update($request->all());

        return redirect()->route('ligne_budgetaire_sorties.index')
            ->with('success', 'Ligne budgétaire sortie modifiée avec succès.');
    }

    public function destroy($id)
    {
        ligne_budgetaire_sortie::destroy($id);

        return redirect()->route('ligne_budgetaire_sorties.index')
            ->with('success', 'Ligne budgétaire sortie supprimée avec succès.');
    }

    public function show($id)
    {
        $ligne = ligne_budgetaire_sortie::findOrFail($id);
        return view('Budget/ligne_budgetaire_sorties.show', compact('ligne'));
    }

    // ✅ Exports
    public function exportPdf()
    {
        $lignes = ligne_budgetaire_sortie::all();
        $pdf = PDF::loadView('Budget/ligne_budgetaire_sorties.pdf', compact('lignes'));
        return $pdf->download('lignes_budgetaires_sorties.pdf');
    }

    public function exportExcel1()
    {
        return Excel::download(new LigneBudgetaireSortiesExport(), 'lignes_budgetaires_sorties.xlsx');
    }

    public function exportPdfOne($id)
    {
        $ligne = ligne_budgetaire_sortie::findOrFail($id);
        $pdf = PDF::loadView('Budget/ligne_budgetaire_sorties.pdf_one', compact('ligne'));
        return $pdf->download('ligne_budgetaire_sortie_'.$id.'.pdf');
    }
// ✅ Export global avec vue Blade
    public function exportExcel()
    {
        $lignes = \App\Models\ligne_budgetaire_Sortie::all();
        return Excel::download(new \App\Exports\LigneBudgetaireSortiesExportView($lignes, 'ligne_budgetaire_sorties.excel'), 'lignes_budgetaires_sorties.xlsx');
    }

// ✅ Export individuel avec vue Blade
    public function exportExcelOne($id)
    {
        $ligne = \App\Models\ligne_budgetaire_Sortie::findOrFail($id);
        return Excel::download(new \App\Exports\LigneBudgetaireSortiesExportView([$ligne], 'ligne_budgetaire_sorties.excel_one'), 'ligne_budgetaire_sortie_'.$id.'.xlsx');
    }

    public function exportExcelOne1($id)
    {
        $ligne = ligne_budgetaire_sortie::findOrFail($id);
        return Excel::download(new LigneBudgetaireSortiesExport($ligne), 'ligne_budgetaire_sortie_'.$id.'.xlsx');
    }

}
