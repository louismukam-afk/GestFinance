<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Models\element_ligne_budgetaire_entree;
use App\Models\ligne_budgetaire_Entree;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DonneeBudgetaireEntreesExport;
use Barryvdh\DomPDF\Facade\Pdf;
class ElementLigneBudgetaireEntreeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');


        $this->values['big_title']='Gestion des budgets';

        $this->values['title']='Ligne budgétaire Sorties';
    }
    // Page intermédiaire (choix gérer éléments)
    public function manage($ligne_id)
    {
        $ligne = ligne_budgetaire_Entree::findOrFail($ligne_id);
        $elements = element_ligne_budgetaire_entree::where('id_ligne_budgetaire_entree', $ligne_id)->get();
        $this->values['title']='Eléments ligne budgétaire entrées';
        return view('Budget.element_entrees.manage', compact('ligne', 'elements'),$this->values);
    }

    // Liste des éléments
    public function indexElements($ligne_id)
    {
        $ligne = ligne_budgetaire_Entree::findOrFail($ligne_id);
        $elements = element_ligne_budgetaire_entree::where('id_ligne_budgetaire_entree', $ligne_id)->get();
        $this->values['title']='Eléments ligne budgétaire entrées';
        return view('Budget.element_entrees.index', compact('ligne', 'elements'),$this->values);
    }

    // Formulaire création
    public function create($ligne_id)
    {
        $ligne = ligne_budgetaire_Entree::findOrFail($ligne_id);
        $this->values['title']='Eléments ligne budgétaire entrées';
        return view('Budget.element_entrees.create', compact('ligne'),$this->values);
    }

    // Enregistrement

    public function store(Request $request, $ligne_id)
    {
        $ligne = ligne_budgetaire_Entree::findOrFail($ligne_id);

        $request->validate([
            'libelle.*' => 'required|string|max:255',
            'code.*' => 'required|string|max:50',
            'compte.*' => 'required|string|max:50',
            'date_creation.*' => 'required|date',
        ]);

        foreach ($request->libelle as $i => $val) {
            element_ligne_budgetaire_entree::create([
                'libelle_elements_ligne_budgetaire_entree' => $val,
                'code_elements_ligne_budgetaire_entree' => $request->code[$i],
                'numero_compte_elements_ligne_budgetaire_entree' => $request->compte[$i],
                'description' => $request->description[$i] ?? null,
                'date_creation' => $request->date_creation[$i],
                'id_ligne_budgetaire_entree' => $ligne->id,
                'id_user' => Auth::id(),
            ]);
        }

        return redirect()->route('element_entrees.index', $ligne->id)
            ->with('success', 'Éléments ajoutés avec succès.');
    }

    public function store1(Request $request, $ligne_id)
    {
        $ligne = ligne_budgetaire_Entree::findOrFail($ligne_id);

        $request->validate([
            'libelle.*' => 'required|string|max:255',
            'code.*' => 'required|string|max:50',
            'compte.*' => 'required|string|max:50',
            'date_creation.*' => 'required|date',
        ]);
        foreach ($request->libelle_ligne_budgetaire_entree as $i => $val) {
            element_ligne_budgetaire_entree::create([
                'libelle_elements_ligne_budgetaire_entree' => $val,
                'code_elements_ligne_budgetaire_entree' => $request->code[$i],
                'numero_elements_compte_ligne_budgetaire_entree' => $request->compte[$i],
                'description' => $request->description[$i] ?? null,
                'date_creation' => $request->date_creation[$i],
                'id_ligne_budgetaire_entree' => $ligne->id,
                'id_user' => Auth::id(),
            ]);
        }

        return redirect()->route('element_entrees.index', $ligne->id)
            ->with('success', 'Éléments ajoutés avec succès.');
    }

    // Edit
    public function edit($id)
    {
        $element = element_ligne_budgetaire_entree::findOrFail($id);
        $this->values['title']='Eléments ligne budgétaire entrées';
        return view('Budget.element_entrees.edit', compact('element'),$this->values);
    }

    // Update
    public function update(Request $request, $id)
    {
        $element = element_ligne_budgetaire_entree::findOrFail($id);

        $request->validate([
            'libelle' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'compte' => 'required|string|max:50',
            'date_creation' => 'required|date',
        ]);

        $element->update([

            'libelle_elements_ligne_budgetaire_entree' => $request->libelle,
            'code_elements_ligne_budgetaire_entree' => $request->code,
            'numero_compte_elements_ligne_budgetaire_entree' => $request->compte,
            'description' => $request->description,
            'date_creation' => $request->date_creation,
            'id_user' => Auth::id(),
        ]);

        return redirect()->route('element_entrees.index', $element->id_ligne_budgetaire_entree)
            ->with('success', 'Élément modifié avec succès.');
    }

    // Suppression
    public function destroy($id)
    {
        $element = element_ligne_budgetaire_entree::findOrFail($id);
        $ligne_id = $element->id_ligne_budgetaire_entree;
        $element->delete();

        return redirect()->route('element_entrees.index', $ligne_id)
            ->with('success', 'Élément supprimé avec succès.');
    }
}
