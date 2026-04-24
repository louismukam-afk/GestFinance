<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Models\budget;
use App\Models\donnee_budgetaire_entree;
use App\Models\donnee_ligne_budgetaire_entree;
use App\Models\element_ligne_budgetaire_entree;
use App\Models\ligne_budgetaire_Entree;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DonneeBudgetaireEntreesExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
class DonneeLigneBudgetaireEntreeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->values['big_title'] = 'Gestion des budgets';
        $this->values['title'] = 'Données ligne budgétaire entrées';
    }
    /** Page intermédiaire (options) */
    public function manage($donnee_id)
    {
        $donnee = donnee_budgetaire_entree::with(['budgets','ligne_budgetaire_entrees'])
            ->findOrFail($donnee_id);

        $lignes = donnee_ligne_budgetaire_entree::with(['element_ligne_budgetaire_entrees','ligne_budgetaire_entrees','budget'])
            ->where('id_donnee_budgetaire_entree', $donnee_id)
            ->get();
        $this->values['title'] = 'Données ligne budgétaire entrées';
        return view('Budget.donnee_ligne_entrees.manage', compact('donnee', 'lignes') + $this->values);
    }

    /** Liste des lignes (pour une donnée budgétaire entrée) */

    public function index($donnee_id)
    {
        $donnee = donnee_budgetaire_entree::with(['budgets','ligne_budgetaire_entrees'])
            ->findOrFail($donnee_id);

        $lignes = donnee_ligne_budgetaire_entree::with([
            'element_ligne_budgetaire_entrees',
            'ligne_budgetaire_entrees',
            'budget',
            'donnee_budgetaire_entrees'
        ])
            ->where('id_donnee_budgetaire_entree', $donnee_id)
            ->orderBy('date_creation','desc')
            ->get();

        // Groupement Budget → Ligne → Donnée
        $grouped = $lignes
            ->groupBy(fn($i) => $i->budget?->libelle_ligne_budget ?? 'Budget inconnu')
        ->map(fn($byBudget) => $byBudget->groupBy(fn($i) => $i->ligne_budgetaire_entrees?->libelle_ligne_budgetaire_entree ?? 'Ligne inconnue')
                     ->map(fn($byLigne) => $byLigne->groupBy(fn($i) => $i->donnee_budgetaire_entrees?->donnee_ligne_budgetaire_entree ?? 'Donnée inconnue')));

    $this->values['title'] = 'Données ligne budgétaire entrées';

    return view('Budget.donnee_ligne_entrees.index', compact('donnee', 'grouped') + $this->values);
}


    public function index1($donnee_id)
    {
        $donnee = donnee_budgetaire_entree::with(['budgets','ligne_budgetaire_entrees'])
            ->findOrFail($donnee_id);

        $lignes = donnee_ligne_budgetaire_entree::with(['element_ligne_budgetaire_entrees','ligne_budgetaire_entrees','budget'])
            ->where('id_donnee_budgetaire_entree', $donnee_id)
            ->orderBy('date_creation','desc')
            ->get();

        return view('Budget.donnee_ligne_entrees.index', compact('donnee', 'lignes') + $this->values);
    }
    public function createok($donnee_id)
    {
        $donnee = donnee_budgetaire_entree::with(['budgets','ligne_budgetaire_entrees'])
            ->findOrFail($donnee_id);

        // Charger tous les budgets
        $budgets = Budget::all();

        // Ligne parente
        $ligne = ligne_budgetaire_Entree::find($donnee->id_ligne_budgetaire_entree);

        // Éléments liés à cette ligne
        $elements = element_ligne_budgetaire_entree::where('id_ligne_budgetaire_entree', $donnee->id_ligne_budgetaire_entree)->get();
       dump($ligne,$donnee,$elements);
       die();
        return view('Budget.donnee_ligne_entrees.create', compact('donnee', 'ligne', 'elements', 'budgets') + $this->values);
    }
    public function create($donnee_id)
    {
        $donnee = donnee_budgetaire_entree::with(['budgets','ligne_budgetaire_entrees'])
            ->findOrFail($donnee_id);

        $budgets = Budget::all();

        // Charger immédiatement les éléments liés à la ligne budgétaire de cette donnée
        $elements = element_ligne_budgetaire_entree::where(
            'id_ligne_budgetaire_entree',
            $donnee->id_ligne_budgetaire_entree
        )->get();
        $this->values['title'] = 'Création d\'une  ligne budgétaire entrées';
        return view('Budget.donnee_ligne_entrees.create', [
                'donnee'   => $donnee,
                'budgets'  => $budgets,
                'elements' => $elements,
            ] + $this->values);
    }
    public function edit($id)
    {
        // On récupère la ligne (donnée ligne budgétaire entrée)
        $ligne = donnee_ligne_budgetaire_entree::with([
            'donnee_budgetaire_entrees',
            'budget'
        ])->findOrFail($id);

        // On prépare les valeurs pour la vue
        $this->values['title'] = 'Modification d\'une  donnée ligne budgétaire entrée';
        $this->values['big_title'] = 'Gestion des budgets';

        return view('Budget.donnee_ligne_entrees.edit', compact('ligne') + $this->values);
    }

    /**
     * Mettre à jour une donnée ligne budgétaire entrée
     */
    public function update(Request $request, $id)
    {
        $ligne = donnee_ligne_budgetaire_entree::findOrFail($id);

        // Validation des champs
        $request->validate([
            'libelle'   => 'required|string|max:255',
            'code'      => 'required|string|max:50',
            'compte'    => 'required|string|max:50',
            'date_creation' => 'required|date',
            'id_budget' => 'required|integer',
            'id_donnee_budgetaire_entree' => 'required|integer',
            'id_element_ligne_budgetaire_entree' => 'required|integer',
            'montant'   => 'required|numeric|min:0',
        ]);

        // Mise à jour
        $ligne->update([
            'donnee_ligne_budgetaire_entree'  => $request->libelle,
            'code_donnee_ligne_budgetaire_entree' => $request->code,
            'numero_donne_ligne_budgetaire_entree' => $request->compte,
            'description' => $request->description,
            'date_creation' => $request->date_creation,
            'id_budget'  => $request->id_budget,
            'id_donnee_budgetaire_entree' => $request->id_donnee_budgetaire_entree,
            'id_element_ligne_budgetaire_entree' => $request->id_element_ligne_budgetaire_entree,
            'montant'   => $request->montant,
            'id_user'   => Auth::id(),
        ]);
        $this->values['title'] = 'Modification d\'une  donnée ligne budgétaire entrée';

        return redirect()
            ->route('donnee_ligne_entrees.index',$ligne->id)
            ->with('success', 'Donnée ligne budgétaire mise à jour avec succès.',$this->values);
    }

    public function edit2($id)
    {
        $ligne = donnee_ligne_budgetaire_entree::with(['donnee_budgetaire_entrees'])
            ->findOrFail($id);

        // Charger tous les budgets
        $budgets = Budget::all();

        // Donnée parente
        $donnee = $ligne->donnee_budgetaire_entrees;

        // Éléments liés à la même ligne budgétaire entrée
        $elements = element_ligne_budgetaire_entree::where('id_ligne_budgetaire_entree', $ligne->id_ligne_budgetaire_entree)->get();

        return view('Budget.donnee_ligne_entrees.edit', compact('ligne', 'elements', 'budgets', 'donnee') + $this->values);
    }

    /** Formulaire de création multiple */
    public function create11($donnee_id)
    {
        $donnees = donnee_budgetaire_entree::with(['budgets','ligne_budgetaire_entrees'])
            ->findOrFail($donnee_id);

        // La ligne vient de la donnée parente
        $ligne = ligne_budgetaire_Entree::find($donnees->id_ligne_budgetaire_entree);

        // On limite les éléments à cette ligne
        $elements = element_ligne_budgetaire_entree::where('id_ligne_budgetaire_entree', $donnees->id_ligne_budgetaire_entree)->get();

        return view('Budget.donnee_ligne_entrees.create', compact('donnees', 'ligne', 'elements') + $this->values);
    }
    public function create12($donnee_id)
    {
        $donnee = donnee_budgetaire_entree::with(['budgets','ligne_budgetaire_entrees'])
            ->findOrFail($donnee_id);


        $ligne = ligne_budgetaire_Entree::find($donnee->id_ligne_budgetaire_entree);

        $elements = element_ligne_budgetaire_entree::where('id_ligne_budgetaire_entree', $donnee->id_ligne_budgetaire_entree)->get();
       /* dump($elements,$donnee,$ligne);
        die();*/

        return view('Budget.donnee_ligne_entrees.create', compact('donnee', 'ligne', 'elements') + $this->values);
    }


    public function getElements1($donnee_id)
    {
        $donnee = \App\Models\donnee_budgetaire_entree::with('ligne_budgetaire_entrees.element_ligne_budgetaire_entrees')->findOrFail($donnee_id);

        $elements = $donnee->ligne_budgetaire_entree->element_ligne_budgetaire_entrees ?? collect();

        return response()->json($elements);
    }
// DonneeLigneBudgetaireEntreeController.php
    public function getElements($donnee_id)
    {
        $donnee = donnee_budgetaire_entree::findOrFail($donnee_id);

        $elements = element_ligne_budgetaire_entree::where(
            'id_ligne_budgetaire_entree',
            $donnee->id_ligne_budgetaire_entree
        )
            ->select('id','libelle_elements_ligne_budgetaire_entree')
            ->orderBy('libelle_elements_ligne_budgetaire_entree')
            ->get();

        return response()->json($elements);
    }



    /** Enregistrement multiple */
    public function store(Request $request, $donnee_id)
    {
        $donnee = donnee_budgetaire_entree::findOrFail($donnee_id);

        // On valide chaque tableau + l’appartenance des éléments (FK)
        $request->validate([
            'libelle.*'        => 'required|string|max:255',
            'code.*'           => 'required|string|max:50',
            'compte.*'         => 'required|string|max:50',
            'description.*'    => 'nullable|string',
            'date_creation.*'  => 'required|date',
            'montant.*'  => 'required',
            // l’élément est obligatoire ET doit exister
            'id_element_ligne_budgetaire_entree.*' => 'required|integer|exists:element_ligne_budgetaire_entrees,id',
        ]);

        foreach ((array) $request->libelle as $i => $val) {
            donnee_ligne_budgetaire_entree::create([
                'donnee_ligne_budgetaire_entree'         => $val,
                'code_donnee_ligne_budgetaire_entree'    => $request->code[$i] ?? null,
                'numero_donne_ligne_budgetaire_entree'   => $request->compte[$i] ?? null,
                'description'                            => $request->description[$i] ?? null,
                'montant'                            => $request->montant[$i] ?? null,
                'date_creation'                          => $request->date_creation[$i] ?? now()->toDateString(),

                // 🔗 FK héritées de la donnée parente
                'id_budget'                               => $donnee->id_budget,
                'id_ligne_budgetaire_entree'              => $donnee->id_ligne_budgetaire_entree,
                'id_donnee_budgetaire_entree'             => $donnee->id,

                // 🔗 FK élément choisi dans le formulaire
                'id_element_ligne_budgetaire_entree'      => $request->id_element_ligne_budgetaire_entree[$i],

                // 👤 utilisateur connecté
                'id_user'                                 => Auth::id(),
            ]);
        }
        $this->values['title'] = 'Création d\'une  donnée ligne budgétaire entrée';

        return redirect()
            ->route('donnee_ligne_entrees.index', $donnee->id)
            ->with('success', '✅ Données ligne budgétaire enregistrées avec succès.',$this->values);
    }

    /** Edition d’une ligne */
    public function edit1($id)
    {
        $ligne = donnee_ligne_budgetaire_entree::with(['donnee_budgetaire_entrees'])
            ->findOrFail($id);

        // on restreint le choix des éléments à la même ligne parente
        $elements = element_ligne_budgetaire_entree::where('id_ligne_budgetaire_entree', $ligne->id_ligne_budgetaire_entree)->get();

        return view('Budget.donnee_ligne_entrees.edit', compact('ligne', 'elements') + $this->values);
    }

    /** Mise à jour d’une ligne */
    public function update1(Request $request, $id)
    {
        $ligne = donnee_ligne_budgetaire_entree::findOrFail($id);

        $request->validate([
            'libelle'       => 'required|string|max:255',
            'code'          => 'required|string|max:50',
            'compte'        => 'required|string|max:50',
            'description'   => 'nullable|string',
            'date_creation' => 'required|date',
            'id_element_ligne_budgetaire_entree' => 'required|integer|exists:element_ligne_budgetaire_entrees,id',
        ]);

        $ligne->update([
            'donnee_ligne_budgetaire_entree'       => $request->libelle,
            'code_donnee_ligne_budgetaire_entree'  => $request->code,
            'numero_donne_ligne_budgetaire_entree' => $request->compte,
            'description'                          => $request->description,
            'date_creation'                        => $request->date_creation,

            // 🔗 FK élément (modifiable)
            'id_element_ligne_budgetaire_entree'   => $request->id_element_ligne_budgetaire_entree,

            // 👤 utilisateur connecté
            'id_user'                               => Auth::id(),
        ]);

        return redirect()
            ->route('donnee_ligne_entrees.index', $ligne->id_donnee_budgetaire_entree)
            ->with('success', '✅ Donnée ligne budgétaire mise à jour.');
    }

    /** Suppression */
    public function destroy($id)
    {
        $ligne = donnee_ligne_budgetaire_entree::findOrFail($id);
        $donnee_id = $ligne->id_donnee_budgetaire_entree;
        $ligne->delete();
        $this->values['title'] = 'Gestion des  données ligne budgétaire entrées';

        return redirect()
            ->route('donnee_ligne_entrees.index', $donnee_id)
            ->with('success', '🗑️ Donnée ligne supprimée.',$this->values);
    }

    /** Export PDF (pour une donnée parente) */
    public function exportPdf($donnee_id)
    {
        $donnee = donnee_budgetaire_entree::with(['budgets','ligne_budgetaire_entrees'])
            ->findOrFail($donnee_id);

        $lignes = donnee_ligne_budgetaire_entree::with([
            'element_ligne_budgetaire_entrees',
            'ligne_budgetaire_entrees',
            'budget',
            'donnee_budgetaire_entrees'
        ])
            ->where('id_donnee_budgetaire_entree', $donnee_id)
            ->orderBy('date_creation','desc')
            ->get();

        // Groupement Budget → Ligne → Donnée
        $grouped = $lignes
            ->groupBy(fn($i) => $i->budget?->libelle_ligne_budget ?? 'Budget inconnu')
        ->map(fn($byBudget) =>
            $byBudget->groupBy(fn($i) => $i->ligne_budgetaire_entrees?->libelle_ligne_budgetaire_entree ?? 'Ligne inconnue')
                     ->map(fn($byLigne) =>
                         $byLigne->groupBy(fn($i) => $i->donnee_budgetaire_entrees?->donnee_ligne_budgetaire_entree ?? 'Donnée inconnue')
                     )
        );
                $this->values['title'] = 'Gestion des  donnée ligne budgétaire entrée';


    $pdf = PDF::loadView('Budget.donnee_ligne_entrees.pdf', compact('donnee', 'grouped'));
    return $pdf->download('donnee_ligne_budgetaire_entrees.pdf',$this->values);
}

    public function exportPdf12($donnee_id)
    {
        $donnee = donnee_budgetaire_entree::with(['budgets','ligne_budgetaire_entrees'])->findOrFail($donnee_id);
        $lignes = donnee_ligne_budgetaire_entree::with(['element_ligne_budgetaire_entrees','ligne_budgetaire_entrees','budget'])
            ->where('id_donnee_budgetaire_entree', $donnee_id)
            ->get();

        $pdf = PDF::loadView('Budget.donnee_ligne_entrees.pdf', compact('donnee', 'lignes'));
        return $pdf->download('donnee_ligne_budgetaire_entrees.pdf');
    }

    /** Export Excel (pour une donnée parente) */
    public function exportExcel($donnee_id)
    {
        return Excel::download(new DonneeLigneBudgetaireEntreesExport($donnee_id), 'donnee_ligne_budgetaire_entrees.xlsx');
    }
/*
    // ✅ Page intermédiaire (manage)
    public function manage($donnee_id)
    {
        $donnee = donnee_budgetaire_entree::findOrFail($donnee_id);
        $lignes = donnee_ligne_budgetaire_entree::where('id_donnee_budgetaire_entree', $donnee_id)->get();
        return view('Budget.donnee_ligne_entrees.manage', compact('donnee', 'lignes'), $this->values);
    }

    // ✅ Liste des données
    public function index($donnee_id)
    {
        $donnee = donnee_budgetaire_entree::findOrFail($donnee_id);
        $lignes = donnee_ligne_budgetaire_entree::where('id_donnee_budgetaire_entree', $donnee_id)->get();
        return view('Budget.donnee_ligne_entrees.index', compact('donnee', 'lignes'), $this->values);
    }

    public function getElements($donnee_id)
    {
        $donnee = \App\Models\donnee_budgetaire_entree::with('ligne_budgetaire_entree.element_ligne_budgetaire_entrees')->findOrFail($donnee_id);

        $elements = $donnee->ligne_budgetaire_entree->element_ligne_budgetaire_entrees ?? collect();

        return response()->json($elements);
    }

    // ✅ Formulaire création multiple
    public function create($donnee_id)
    {
        $donnee = donnee_budgetaire_entree::findOrFail($donnee_id);
        $lignes = ligne_budgetaire_Entree::all();
        $budgets=budget::all();
        return view('Budget.donnee_ligne_entrees.create', compact('donnee', 'lignes','budgets'), $this->values);
    }

    // ✅ Enregistrement multiple
    public function store(Request $request, $donnee_id)
    {
        $donnee = donnee_budgetaire_entree::findOrFail($donnee_id);

        $request->validate([
            'libelle.*' => 'required|string|max:255',
            'code.*' => 'required|string|max:50',
            'compte.*' => 'required|string|max:50',
            'date_creation.*' => 'required|date',
        ]);

        foreach ($request->libelle as $i => $val) {
            donnee_ligne_budgetaire_entree::create([
                'donnee_ligne_budgetaire_entree' => $val,
                'code_donnee_ligne_budgetaire_entree' => $request->code[$i],
                'numero_donne_ligne_budgetaire_entree' => $request->compte[$i],
                'description' => $request->description[$i] ?? null,
                'date_creation' => $request->date_creation[$i],
                'id_ligne_budgetaire_entree' => $request->id_ligne_budgetaire_entree[$i] ?? 0,
                'id_budget' => $donnee->id_budget,
                'id_donnee_budgetaire_entree' => $donnee->id,
                'id_user' => Auth::id(),
            ]);
        }

        return redirect()->route('donnee_ligne_entrees.index', $donnee->id)
            ->with('success', '✅ Données ligne budgétaire ajoutées avec succès.');
    }

    // ✅ Edition
    public function edit($id)
    {
        $ligne = donnee_ligne_budgetaire_entree::findOrFail($id);
        $lignes = ligne_budgetaire_Entree::all();
        return view('Budget.donnee_ligne_entrees.edit', compact('ligne', 'lignes'), $this->values);
    }

    // ✅ Mise à jour
    public function update(Request $request, $id)
    {
        $ligne = donnee_ligne_budgetaire_entree::findOrFail($id);

        $request->validate([
            'libelle' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'compte' => 'required|string|max:50',
            'date_creation' => 'required|date',
        ]);

        $ligne->update([
            'donnee_ligne_budgetaire_entree' => $request->libelle,
            'code_donnee_ligne_budgetaire_entree' => $request->code,
            'numero_donne_ligne_budgetaire_entree' => $request->compte,
            'description' => $request->description,
            'date_creation' => $request->date_creation,
            'id_ligne_budgetaire_entree' => $request->id_ligne_budgetaire_entree ?? 0,
            'id_user' => Auth::id(),
        ]);

        return redirect()->route('donnee_ligne_entrees.index', $ligne->id_donnee_budgetaire_entree)
            ->with('success', '✅ Donnée ligne budgétaire modifiée avec succès.');
    }

    // ✅ Suppression
    public function destroy($id)
    {
        $ligne = donnee_ligne_budgetaire_entree::findOrFail($id);
        $donnee_id = $ligne->id_donnee_budgetaire_entree;
        $ligne->delete();

        return redirect()->route('donnee_ligne_entrees.index', $donnee_id)
            ->with('success', '🗑️ Donnée ligne supprimée avec succès.');
    }

    // ✅ Export PDF
    public function exportPdf($donnee_id)
    {
        $donnee = donnee_budgetaire_entree::findOrFail($donnee_id);
        $lignes = donnee_ligne_budgetaire_entree::where('id_donnee_budgetaire_entree', $donnee_id)->get();

        $pdf = PDF::loadView('Budget.donnee_ligne_entrees.pdf', compact('donnee', 'lignes'));
        return $pdf->download('donnee_ligne_budgetaire_entrees.pdf');
    }

    // ✅ Export Excel
    public function exportExcel($donnee_id)
    {
        return Excel::download(new DonneeLigneBudgetaireEntreesExport($donnee_id), 'donnee_ligne_budgetaire_entrees.xlsx');
    }*/
}
