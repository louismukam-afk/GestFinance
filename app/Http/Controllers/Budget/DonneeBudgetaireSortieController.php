<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Models\budget;
use App\Models\donnee_budgetaire_sortie;
use App\Models\ligne_budgetaire_sortie;
use Illuminate\Http\Request;
use App\Exports\DonneeBudgetaireSortieExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
class DonneeBudgetaireSortieController extends Controller
{
    protected $values=[];
    public function __construct()
    {
        $this->values['big_title']='Administration';

        $this->values['title']='Gestion des bons';
    }
    // Liste avec filtre par période
    public function index1(Request $request)
    {
        $query = donnee_budgetaire_sortie::with(['budgets', 'ligne_budgetaire_sortie']);

        if ($request->filled('date_debut') && $request->filled('date_fin')) {
            $query->whereBetween('date_creation', [$request->date_debut, $request->date_fin]);
        }

        $donnees = $query->latest()->paginate(10);
        return view('Budget.donnee_sorties.index', compact('donnees'));
    }
    public function index(Request $request)
    {
        $query = donnee_budgetaire_sortie::with(['budgets', 'ligne_budgetaire_sortie']);

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
                return $item->ligne_budgetaire_sortie->libelle_ligne_budgetaire_sortie ?? 'Sans ligne budgétaire';
            });
        });
        $this->values['title']='Données Budgétaires sorties';

        return view('Budget.donnee_sorties.index', compact('grouped'),$this->values);
    }


    // Formulaire création
    public function create()
    {
        $budgets = budget::all();
        $lignes = ligne_budgetaire_sortie::all();
        $this->values['title']='création d\'une donnée';
        return view('Budget.donnee_sorties.create', compact('budgets', 'lignes'),$this->values);
    }




    // Enregistrement
    public function store(Request $request)
    {
        $request->validate([
            'donnee_ligne_budgetaire_sortie.*' => 'required|string|max:255',
            'code_donnee_budgetaire_sortie.*' => 'required|string|max:50',
            'numero_donnee_budgetaire_sortie.*' => 'required|string|max:50',
            'description.*' => 'nullable|string',
            'date_creation.*' => 'required|date',
            'id_budget' => 'required|integer',
            'id_ligne_budgetaire_sortie' => 'required|integer',
            'montant.*' => 'required|numeric',
        ]);

        foreach ($request->donnee_ligne_budgetaire_sortie as $i => $val) {
            donnee_budgetaire_sortie::create([
                'donnee_ligne_budgetaire_sortie' => $val,
                'code_donnee_budgetaire_sortie' => $request->code_donnee_budgetaire_sortie[$i],
                'numero_donnee_budgetaire_sortie' => $request->numero_donnee_budgetaire_sortie[$i],
                'description' => $request->description[$i] ?? null,
                'date_creation' => $request->date_creation[$i],
                'id_budget' => $request->id_budget, // commun à tous
                'id_ligne_budgetaire_sortie' => $request->id_ligne_budgetaire_sortie, // commun à tous
                'montant' => $request->montant[$i],
                'id_user' => Auth::id(),
            ]);
        }
        $this->values['title']='création d\'une donnée';
        return redirect()->route('donnee_sorties.index')
            ->with('success', '✅ Données budgétaires sorties ajoutées avec succès.',$this->values);
    }

    public function store1(Request $request)
    {
        $request->validate([
            'donnee_ligne_budgetaire_sortie' => 'required|string|max:255',
            'code_donnee_budgetaire_sortie' => 'required|string|max:50|unique:donnee_budgetaire_sorties,code_donnee_budgetaire_sortie',
            'numero_donnee_budgetaire_sortie' => 'required|string|max:50',
            'description' => 'nullable|string',
            'date_creation' => 'required|date',
            'id_budget' => 'required|integer',
            'id_ligne_budgetaire_sortie' => 'required|integer',
            'montant' => 'required|numeric',
        ]);

        donnee_budgetaire_sortie::create([
            'donnee_ligne_budgetaire_sortie' => $request->donnee_ligne_budgetaire_sortie,
            'code_donnee_budgetaire_sortie' => $request->code_donnee_budgetaire_sortie,
            'numero_donnee_budgetaire_sortie' => $request->numero_donnee_budgetaire_sortie,
            'description' => $request->description,
            'date_creation' => $request->date_creation,
            'id_budget' => $request->id_budget,
            'id_ligne_budgetaire_sortie' => $request->id_ligne_budgetaire_sortie,
            'montant' => $request->montant,
            'id_user' => Auth::id(), // ✅ utilisateur connecté
        ]);
        $this->values['title']='création d\'une donnée';
        return redirect()->route('donnee_sorties.index')
            ->with('success', '✅ Donnée budgétaire sortie ajoutée avec succès.',$this->values);
    }


// Mise à jour
    public function update(Request $request, $id)
    {
        $donnee = donnee_budgetaire_sortie::findOrFail($id);

        $request->validate([
            'donnee_ligne_budgetaire_sortie' => 'required|string|max:255',
            'code_donnee_budgetaire_sortie' => 'required|string|max:50|unique:donnee_budgetaire_sorties,code_donnee_budgetaire_sortie,' . $donnee->id,
            'numero_donnee_budgetaire_sortie' => 'required|string|max:50',
            'description' => 'nullable|string',
            'date_creation' => 'required|date',
            'id_budget' => 'required|integer',
            'id_ligne_budgetaire_sortie' => 'required|integer',
            'montant' => 'required|numeric',
        ]);

        $donnee->update([
            'donnee_ligne_budgetaire_sortie' => $request->donnee_ligne_budgetaire_sortie,
            'code_donnee_budgetaire_sortie' => $request->code_donnee_budgetaire_sortie,
            'numero_donnee_budgetaire_sortie' => $request->numero_donnee_budgetaire_sortie,
            'description' => $request->description,
            'date_creation' => $request->date_creation,
            'id_budget' => $request->id_budget,
            'id_ligne_budgetaire_sortie' => $request->id_ligne_budgetaire_sortie,
            'montant' => $request->montant,
            'id_user' => Auth::id(), // ✅ utilisateur connecté mis à jour
        ]);
        $this->values['title']='Modification  d\'une donnée';
        return redirect()->route('donnee_sorties.index')
            ->with('success', '✅ Donnée budgétaire sortie modifiée avec succès.',$this->values);
    }


    // Edition
    public function edit($id)
    {
        $donnee = donnee_budgetaire_sortie::findOrFail($id);
        $budgets = Budget::all();
        $lignes = ligne_budgetaire_sortie::all();
        $this->values['title']='Modification  d\'une donnée';

        return view('Budget.donnee_sorties.edit', compact('donnee', 'budgets', 'lignes'),$this->values);
    }

    public function update1(Request $request, $id)
    {
        $donnee = donnee_budgetaire_sortie::findOrFail($id);

        $request->validate([
            'donnee_ligne_budgetaire_sortie' => 'required|string|max:255',
            'code_donnee_budgetaire_sortie' => 'required|string|max:50',
            'numero_donnee_budgetaire_sortie' => 'required|string|max:50',
            'date_creation' => 'required|date',
            'id_budget' => 'required|integer',
            'id_ligne_budgetaire_sortie' => 'required|integer',
            'montant' => 'required|numeric',
        ]);

        $donnee->update([$request->all(), 'id_user' => Auth::id()]);

        return redirect()->route('donnee_sorties.index')->with('success', 'Mise à jour réussie.');
    }

    public function destroy($id)
    {
        $donnee = donnee_budgetaire_sortie::findOrFail($id);
        $donnee->delete();
        $this->values['title']='Données Budgétaires sorties';

        return redirect()->route('donnee_sorties.index')->with('success', 'Supprimée avec succès.',$this->values);
    }

    // Export Excel
    public function exportExcel(Request $request)
    {
        return Excel::download(new DonneeBudgetaireSortieExport($request->date_debut, $request->date_fin), 'donnees_sorties.xlsx');
    }

    // Export PDF
    public function exportPdf1(Request $request)
    {
        $query = donnee_budgetaire_sortie::with(['budgets', 'ligne_budgetaire_sortie']);

        if ($request->filled('date_debut') && $request->filled('date_fin')) {
            $query->whereBetween('date_creation', [$request->date_debut, $request->date_fin]);
        }

        $donnees = $query->get();
        $pdf = PDF::loadView('Budget.donnee_sorties.pdf', compact('donnees'));
        return $pdf->download('donnees_sorties.pdf');
    }
    public function exportPdf(Request $request)
    {
        $query = donnee_budgetaire_sortie::with(['budgets', 'ligne_budgetaire_sortie']);

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
                return $item->ligne_budgetaire_sortie->libelle_ligne_budgetaire_sortie ?? 'Ligne inconnue';
            });
        });

        // ✅ Passer les données regroupées à la vue PDF
        $pdf = PDF::loadView('Budget.donnee_sorties.pdf', compact('grouped'));

        return $pdf->download('donnees_sorties.pdf');
    }

}
