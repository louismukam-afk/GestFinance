<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Models\budget;
use App\Models\donnee_budgetaire_entree;
use App\Models\ligne_budgetaire_Entree;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DonneeBudgetaireEntreesExport;
use Barryvdh\DomPDF\Facade\Pdf;
use function Nette\Utils\map;


class DonneeBudgetaireEntreeController extends Controller
{
    // Index avec regroupement
    public function index(Request $request)
    {
        $query = donnee_budgetaire_entree::with(['budgets', 'ligne_budgetaire_entrees']);

        // Filtre par période
        if ($request->filled('date_debut') && $request->filled('date_fin')) {
            $query->whereBetween('date_creation', [$request->date_debut, $request->date_fin]);
        }

        // On récupère toutes les données
        $donnees = $query->orderBy('date_creation', 'desc')->get();

        // Regrouper par budget puis par ligne
        $grouped = $donnees->groupBy(function ($item) {
            return $item->budgets->libelle_ligne_budget ?? 'Sans budget';
        })->map(function ($budgetGroup) {
            return $budgetGroup->groupBy(function ($item) {
                return $item->ligne_budgetaire_entrees->libelle_ligne_budgetaire_entree ?? 'Sans ligne budgétaire';
            });
        });

        $this->values['title'] = 'Données Budgétaires entrées';

        return view('Budget.donnee_entrees.index', compact('grouped'), $this->values);
    }


    // Formulaire création
    public function create()
    {
        $budgets = budget::all();
        $lignes = ligne_budgetaire_Entree::all();
        $this->values['title']='Création d\'une donnée' ;
        return view('Budget.donnee_entrees.create', compact('budgets', 'lignes'),$this->values);
    }

    // Enregistrement multiple
    public function store(Request $request)
    {
        $request->validate([
            'donnee_ligne_budgetaire_entree.*' => 'required|string|max:255',
            'code_donnee_budgetaire_entree.*' => 'required|string|max:50',
            'numero_donnee_budgetaire_entree.*' => 'required|string|max:50',
            'description.*' => 'nullable|string',
            'date_creation.*' => 'required|date',
            'id_budget.*' => 'required|integer',
            'id_ligne_budgetaire_entree.*' => 'required|integer',
            'montant.*' => 'required|numeric',
        ]);

        foreach ($request->donnee_ligne_budgetaire_entree as $i => $val) {
            donnee_budgetaire_entree::create([
                'donnee_ligne_budgetaire_entree' => $val,
                'code_donnee_budgetaire_entree' => $request->code_donnee_budgetaire_entree[$i],
                'numero_donnee_budgetaire_entree' => $request->numero_donnee_budgetaire_entree[$i],
                'description' => $request->description[$i] ?? null,
                'date_creation' => $request->date_creation[$i],
                'id_budget' => $request->id_budget[$i],
                'id_ligne_budgetaire_entree' => $request->id_ligne_budgetaire_entree[$i],
                'montant' => $request->montant[$i],
                'id_user' => Auth::id(),
            ]);
        }
        $this->values['title']='Création d\'une donnée' ;
        return redirect()->route('donnee_entrees.index')->with('success', '✅ Données ajoutées avec succès.',$this->values);
    }

    // Edition
    public function edit($id)
    {
        $donnee = donnee_budgetaire_entree::findOrFail($id);
        $budgets = Budget::all();
        $lignes = ligne_budgetaire_Entree::all();
        $this->values['title']='Modification d\'une donnée' ;
        return view('Budget.donnee_entrees.edit', compact('donnee', 'budgets', 'lignes'),$this->values);
    }

    // Mise à jour
    public function update(Request $request, $id)
    {
        $donnee = donnee_budgetaire_entree::findOrFail($id);

        $request->validate([
            'donnee_ligne_budgetaire_entree' => 'required|string|max:255',
            'code_donnee_budgetaire_entree' => 'required|string|max:50',
            'numero_donnee_budgetaire_entree' => 'required|string|max:50',
            'description' => 'nullable|string',
            'date_creation' => 'required|date',
            'id_budget' => 'required|integer',
            'id_ligne_budgetaire_entree' => 'required|integer',
            'montant' => 'required|numeric',
        ]);

        $donnee->update([
            'donnee_ligne_budgetaire_entree' => $request->donnee_ligne_budgetaire_entree,
            'code_donnee_budgetaire_entree' => $request->code_donnee_budgetaire_entree,
            'numero_donnee_budgetaire_entree' => $request->numero_donnee_budgetaire_entree,
            'description' => $request->description,
            'date_creation' => $request->date_creation,
            'id_budget' => $request->id_budget,
            'id_ligne_budgetaire_entree' => $request->id_ligne_budgetaire_entree,
            'montant' => $request->montant,
            'id_user' => Auth::id(),
        ]);
        $this->values['title']='Modification d\'une donnée' ;
        return redirect()->route('donnee_entrees.index')->with('success', '✅ Donnée modifiée avec succès.',$this->values);
    }

    // Suppression
    public function destroy($id)
    {
        $donnee = donnee_budgetaire_entree::findOrFail($id);
        $donnee->delete();
        $this->values['title']='Données budgétaires entrées' ;
        return redirect()->route('donnee_entrees.index')->with('success', '🗑️ Supprimée avec succès.',$this->values);
    }

    // Export Excel
    public function exportExcel(Request $request)
    {
        return Excel::download(new DonneeBudgetaireEntreesExport($request), 'donnees_entrees.xlsx');
    }

    // Export PDF
    public function exportPdf(Request $request)
    {
        $query = donnee_budgetaire_entree::with(['budgets', 'ligne_budgetaire_entrees']);

        // ✅ Filtre période
        if ($request->filled('date_debut') && $request->filled('date_fin')) {
            $query->whereBetween('date_creation', [$request->date_debut, $request->date_fin]);
        }

        $donnees = $query->get();

        // ✅ Regrouper par budget puis par ligne
        $grouped = $donnees->groupBy(function ($item) {
            return $item->budgets->libelle_ligne_budget ?? 'Budget inconnu';
        })->map(function ($items) {
            return $items->groupBy(function ($item) {
                return $item->ligne_budgetaire_entrees->libelle_ligne_budgetaire_entree ?? 'Ligne inconnue';
            });
        });
        $this->values['title']='Données budgétaires entrées' ;
        // ✅ Passer les données regroupées à la vue PDF
        $pdf = PDF::loadView('Budget.donnee_entrees.pdf', compact('grouped'),$this->values);

        return $pdf->download('donnees_entrees.pdf');
    }

}
