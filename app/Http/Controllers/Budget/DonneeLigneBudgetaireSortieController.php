<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Models\budget;
use App\Models\donnee_budgetaire_sortie;
use App\Models\donnee_ligne_budgetaire_sortie;
use App\Models\element_ligne_budgetaire_sortie;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DonneeLigneBudgetaireSortiesExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class DonneeLigneBudgetaireSortieController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->values['big_title'] = 'Gestion des budgets';
        $this->values['title'] = 'Données ligne budgétaire sorties';
    }

    /** Page intermédiaire */
    public function manage1($donnee_id)
    {
        $donnee = donnee_ligne_budgetaire_sortie::with(['budgets','ligne_budgetaire_sorties'])
            ->findOrFail($donnee_id);

        $lignes = donnee_ligne_budgetaire_sortie::with(['element_ligne_budgetaire_sorties','ligne_budgetaire_sorties','budget'])
            ->where('id_donnee_budgetaire_sortie', $donnee_id)
            ->get();

        return view('Budget.donnee_ligne_sorties.manage', compact('donnee', 'lignes') + $this->values);
    }
    public function manage($donnee_id)
    {
        // Charger la donnée budgétaire sortie parente
        $donnee = \App\Models\donnee_budgetaire_sortie::with(['budgets','ligne_budgetaire_sortie'])
            ->findOrFail($donnee_id);

        // Charger ses lignes associées
        $lignes = \App\Models\donnee_ligne_budgetaire_sortie::with([
            'element_ligne_budgetaire_sorties',
            'ligne_budgetaire_sortie',
            'budgets'
        ])
            ->where('id_donnee_budgetaire_sortie', $donnee_id)
            ->get();
        $this->values['title'] = 'Données ligne budgétaire sorties';

        return view('Budget.donnee_ligne_sorties.manage', compact('donnee', 'lignes') + $this->values);
    }


    /** Liste */


    public function index($donnee_id)
    {
        // Charger la donnée budgétaire sortie parente
        $donnee = \App\Models\donnee_budgetaire_sortie::with(['budgets','ligne_budgetaire_sortie'])
            ->findOrFail($donnee_id);

        // Charger les lignes liées à cette donnée
        $lignes = \App\Models\donnee_ligne_budgetaire_sortie::with([
            'element_ligne_budgetaire_sorties',
            'ligne_budgetaire_sortie',   // ✅ correspond au modèle
            'budgets',                   // ✅ correspond au modèle
            'donnee_budgetaire_sorties'  // ✅ correspond au modèle
        ])
            ->where('id_donnee_budgetaire_sortie', $donnee_id)
            ->orderBy('date_creation','desc')
            ->get();

        // Groupement Budget → Ligne → Donnée
        $grouped = $lignes
            ->groupBy(fn($i) => $i->budgets?->libelle_ligne_budget ?? 'Budget inconnu')
        ->map(fn($byBudget) =>
            $byBudget->groupBy(fn($i) => $i->ligne_budgetaire_sortie?->libelle_ligne_budgetaire_sortie ?? 'Ligne inconnue')
                     ->map(fn($byLigne) =>
                         $byLigne->groupBy(fn($i) => $i->donnee_budgetaire_sorties?->donnee_ligne_budgetaire_sortie ?? 'Donnée inconnue')
                     )
        );

    $this->values['title'] = 'Données ligne budgétaire sorties';

    return view('Budget.donnee_ligne_sorties.index', compact('donnee', 'grouped') + $this->values);
}

    public function index1($donnee_id)
    {
        $donnee = donnee_ligne_budgetaire_sortie::with(['budgets','ligne_budgetaire_sorties'])
            ->findOrFail($donnee_id);

        $lignes = donnee_ligne_budgetaire_sortie::with([
            'element_ligne_budgetaire_sorties',
            'ligne_budgetaire_sorties',
            'budget',
            'donnee_budgetaire_sorties'
        ])
            ->where('id_donnee_budgetaire_sortie', $donnee_id)
            ->orderBy('date_creation','desc')
            ->get();

        return view('Budget.donnee_ligne_sorties.index', compact('donnee', 'lignes') + $this->values);
    }

    /** Formulaire création */
    public function create($donnee_id)
    {
        $donnee = donnee_budgetaire_sortie::with(['budgets','ligne_budgetaire_sortie'])
            ->findOrFail($donnee_id);

        $budgets =  budget::all();

        $elements = element_ligne_budgetaire_sortie::where(
            'id_ligne_budgetaire_sortie',
            $donnee->id_ligne_budgetaire_sortie
        )->get();
        $this->values['title'] = 'Création des Données ligne budgétaire sorties';

        return view('Budget.donnee_ligne_sorties.create', compact('donnee', 'budgets', 'elements') + $this->values);
    }
    public function getElements($donnee_id)
    {
        $donnee = \App\Models\donnee_budgetaire_sortie::findOrFail($donnee_id);

        $elements = \App\Models\element_ligne_budgetaire_sortie::where(
            'id_ligne_budgetaire_sortie',
            $donnee->id_ligne_budgetaire_sortie
        )
            ->select('id', 'libelle_elements_ligne_budgetaire_sortie')
            ->orderBy('libelle_elements_ligne_budgetaire_sortie')
            ->get();

        return response()->json($elements);
    }

    /** Enregistrement */
    public function store(Request $request, $donnee_id)
    {
        $donnee = donnee_budgetaire_sortie::findOrFail($donnee_id);

        $request->validate([
            'libelle.*'   => 'required|string|max:255',
            'code.*'      => 'required|string|max:50',
            'compte.*'    => 'required|string|max:50',
            'description.*' => 'nullable|string',
            'date_creation.*' => 'required|date',
            'id_element_ligne_budgetaire_sortie.*' => 'required|integer|exists:element_ligne_budgetaire_sorties,id',
        ]);

        foreach ((array) $request->libelle as $i => $val) {
            donnee_ligne_budgetaire_sortie::create([
                'donnee_ligne_budgetaire_sortie'       => $val,
                'code_donnee_ligne_budgetaire_sortie'  => $request->code[$i] ?? null,
                'numero_donne_ligne_budgetaire_sortie' => $request->compte[$i] ?? null,
                'description'                          => $request->description[$i] ?? null,
                'date_creation'                        => $request->date_creation[$i] ?? now()->toDateString(),
                'id_budget'                            => $donnee->id_budget,
                'id_ligne_budgetaire_sortie'           => $donnee->id_ligne_budgetaire_sortie,
                'montant'           => $donnee->montant,
                'id_donnee_budgetaire_sortie'          => $donnee->id,
                'id_element_ligne_budgetaire_sortie'   => $request->id_element_ligne_budgetaire_sortie[$i],
                'id_user'                              => Auth::id(),
            ]);
        }
        $this->values['title'] = 'Création des Données ligne budgétaire sorties';

        return redirect()->route('donnee_ligne_sorties.index', $donnee->id)
            ->with('success', '✅ Données ligne budgétaire sorties enregistrées avec succès.',$this->values);
    }

    /** Edition */
    public function edit($id)
    {
        $ligne = donnee_ligne_budgetaire_sortie::with(['donnee_budgetaire_sorties','budgets'])
            ->findOrFail($id);

        $budgets = Budget::all();
        $donnee = $ligne->donnee_budgetaire_sorties;
        $elements = element_ligne_budgetaire_sortie::where('id_ligne_budgetaire_sortie', $ligne->id_ligne_budgetaire_sortie)->get();
        $this->values['title'] = 'Modification D\'une Données ligne budgétaire sorties';

        return view('Budget.donnee_ligne_sorties.edit', compact('ligne', 'budgets', 'elements', 'donnee') + $this->values);
    }

    /** Mise à jour */
    public function update(Request $request, $id)
    {
        $ligne = donnee_ligne_budgetaire_sortie::findOrFail($id);

        $request->validate([
            'libelle'       => 'required|string|max:255',
            'code'          => 'required|string|max:50',
            'compte'        => 'required|string|max:50',
            'description'   => 'nullable|string',
            'date_creation' => 'required|date',
            'id_element_ligne_budgetaire_sortie' => 'required|integer|exists:element_ligne_budgetaire_sorties,id',
        ]);

        $ligne->update([
            'donnee_ligne_budgetaire_sortie'       => $request->libelle,
            'code_donnee_ligne_budgetaire_sortie'  => $request->code,
            'numero_donne_ligne_budgetaire_sortie' => $request->compte,
            'description'                          => $request->description,
            'date_creation'                        => $request->date_creation,
            'montant'                        => $request->montant,
            'id_element_ligne_budgetaire_sortie'   => $request->id_element_ligne_budgetaire_sortie,
            'id_user'                              => Auth::id(),
        ]);
        $this->values['title'] = 'Modification D\'une Données ligne budgétaire sorties';
        return redirect()->route('donnee_ligne_sorties.index', $ligne->id_donnee_budgetaire_sortie)
            ->with('success', '✅ Donnée ligne budgétaire sortie mise à jour.',$this->values);
    }

    /** Suppression */
    public function destroy($id)
    {
        $ligne = donnee_ligne_budgetaire_sortie::findOrFail($id);
        $donnee_id = $ligne->id_donnee_budgetaire_sortie;
        $ligne->delete();

        return redirect()->route('donnee_ligne_sorties.index', $donnee_id)
            ->with('success', '🗑️ Donnée ligne supprimée.');
    }

    /** Export PDF */

    public function exportPdf($donnee_id)
    {
        $donnee = donnee_budgetaire_sortie::with(['budgets','ligne_budgetaire_sortie'])
            ->findOrFail($donnee_id);

        $lignes = donnee_ligne_budgetaire_sortie::with([
            'element_ligne_budgetaire_sorties',
            'ligne_budgetaire_sortie',
            'budgets',
            'donnee_budgetaire_sorties'
        ])
            ->where('id_donnee_budgetaire_sortie', $donnee_id)
            ->orderBy('date_creation','desc')
            ->get();

        // Groupement Budget → Ligne → Donnée
        $grouped = $lignes
            ->groupBy(fn($i) => $i->budgets?->libelle_ligne_budget ?? 'Budget inconnu')
        ->map(fn($byBudget) =>
            $byBudget->groupBy(fn($i) => $i->ligne_budgetaire_sortie?->libelle_ligne_budgetaire_sortie ?? 'Ligne inconnue')
                ->map(fn($byLigne) =>
                    $byLigne->groupBy(fn($i) => $i->donnee_budgetaire_sorties?->donnee_ligne_budgetaire_sortie ?? 'Donnée inconnue')
                )
        );
 $this->values['title'] = ' Données ligne budgétaire sorties';
    $pdf = PDF::loadView('Budget.donnee_ligne_sorties.pdf', compact('donnee', 'grouped'));
    return $pdf->download('donnee_ligne_budgetaire_sorties.pdf',$this->values);
}

    public function exportPdf1($donnee_id)
    {
        $donnee = donnee_budgetaire_sortie::with(['budgets','ligne_budgetaire_sorties'])
            ->findOrFail($donnee_id);

        $lignes = donnee_ligne_budgetaire_sortie::with([
            'element_ligne_budgetaire_sorties',
            'ligne_budgetaire_sorties',
            'budget',
            'donnee_budgetaire_sorties'
        ])
            ->where('id_donnee_budgetaire_sortie', $donnee_id)
            ->get();

        $pdf = PDF::loadView('Budget.donnee_ligne_sorties.pdf', compact('donnee', 'lignes'));
        return $pdf->download('donnee_ligne_budgetaire_sorties.pdf');
    }

    /** Export Excel */
    public function exportExcel($donnee_id)
    {
        return Excel::download(new DonneeLigneBudgetaireSortiesExport($donnee_id), 'donnee_ligne_budgetaire_sorties.xlsx');
    }

}
