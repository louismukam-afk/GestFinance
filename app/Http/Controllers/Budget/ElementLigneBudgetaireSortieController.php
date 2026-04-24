<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Models\element_ligne_budgetaire_sortie;
use App\Models\ligne_budgetaire_sortie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class ElementLigneBudgetaireSortieController extends Controller
{
// Page intermédiaire
    public function manage($ligne_id)
    {
        $ligne = ligne_budgetaire_sortie::findOrFail($ligne_id);
        $elements = element_ligne_budgetaire_sortie::where('id_ligne_budgetaire_sortie', $ligne_id)->get();
        $this->values['title']='Eléments ligne budgétaire sorties';
        return view('Budget.element_sorties.manage', compact('ligne', 'elements'),$this->values);
    }

    // Liste des éléments
    public function indexElements($ligne_id)
    {
        $ligne = ligne_budgetaire_sortie::findOrFail($ligne_id);
        $elements = element_ligne_budgetaire_sortie::where('id_ligne_budgetaire_sortie', $ligne_id)->get();
        $this->values['title']='Eléments ligne budgétaire sorties';
        return view('Budget.element_sorties.index', compact('ligne', 'elements'),$this->values);
    }

    // Formulaire création
    public function create($ligne_id)
    {
        $ligne = ligne_budgetaire_sortie::findOrFail($ligne_id);
        $this->values['title']='Eléments ligne budgétaire sorties';
        return view('Budget.element_sorties.create', compact('ligne'),$this->values);
    }

    // Enregistrement
    public function store(Request $request, $ligne_id)
    {
        $ligne = ligne_budgetaire_sortie::findOrFail($ligne_id);

        $request->validate([
            'libelle.*' => 'required|string|max:255',
            'code.*' => 'required|string|max:50',
            'compte.*' => 'required|string|max:50',
            'date_creation.*' => 'required|date',
        ]);

        foreach ($request->libelle as $i => $val) {
            element_ligne_budgetaire_sortie::create([
                'libelle_elements_ligne_budgetaire_sortie' => $val,
                'code_elements_ligne_budgetaire_sortie' => $request->code[$i],
                'numero_compte_elements_ligne_budgetaire_sortie' => $request->compte[$i],
                'description' => $request->description[$i] ?? null,
                'date_creation' => $request->date_creation[$i],
                'id_ligne_budgetaire_sortie' => $ligne->id,
                'id_user' => Auth::id(),
            ]);
        }

        return redirect()->route('element_sorties.index', $ligne->id)
            ->with('success', 'Éléments ajoutés avec succès.');
    }

    // Edit
    public function edit($id)
    {
        $element = element_ligne_budgetaire_sortie::findOrFail($id);
        $this->values['title']='Eléments ligne budgétaire sorties';
        return view('Budget.element_sorties.edit', compact('element'),$this->values);
    }

    // Update
    public function update(Request $request, $id)
    {
        $element = element_ligne_budgetaire_sortie::findOrFail($id);

        $request->validate([
            'libelle' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'compte' => 'required|string|max:50',
            'date_creation' => 'required|date',
        ]);

        $element->update([
            'libelle_elements_ligne_budgetaire_sortie' => $request->libelle,
            'code_elements_ligne_budgetaire_sortie' => $request->code,
            'numero_compte_elements_ligne_budgetaire_sortie' => $request->compte,
            'description' => $request->description,
            'date_creation' => $request->date_creation,
            'id_user' => Auth::id(),
        ]);

        return redirect()->route('element_sorties.index', $element->id_ligne_budgetaire_sortie)
            ->with('success', 'Élément modifié avec succès.');
    }

    // Suppression
    public function destroy($id)
    {
        $element = element_ligne_budgetaire_sortie::findOrFail($id);
        $ligne_id = $element->id_ligne_budgetaire_sortie;
        $element->delete();

        return redirect()->route('element_sorties.index', $ligne_id)
            ->with('success', 'Élément supprimé avec succès.');
    }
}
