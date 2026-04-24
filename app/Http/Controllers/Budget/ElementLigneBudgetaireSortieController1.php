<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Models\element_ligne_budgetaire_sortie;
use App\Models\ligne_budgetaire_sortie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class ElementLigneBudgetaireSortieController extends Controller
{
// Liste des éléments d'une ligne
    public function index($id_ligne)
    {
        $ligne = ligne_budgetaire_sortie::findOrFail($id_ligne);
        $elements = element_ligne_budgetaire_sortie::where('id_ligne_budgetaire_sortie', $id_ligne)->get();

        return view('Budget.element_ligne_budgetaire_sorties.index', compact('ligne', 'elements'));
    }

    // Formulaire pour définir le nombre d’éléments
    public function create(Request $request, $ligne_id)
    {
        $ligne = ligne_budgetaire_sortie::findOrFail($ligne_id);

        // nombre de lignes demandé dans manage.blade (par GET)
        $nb_lignes = $request->get('nb_lignes', 1);
        $this->values['title']='Eléments ligne budgétaire sorties';
        return view('Budget.ligne_budgetaire_sorties.element_ligne_budgetaire_sorties.create', compact('ligne', 'nb_lignes'),$this->values);
    }

    public function create1($id_ligne)
    {
        $ligne = ligne_budgetaire_sortie::findOrFail($id_ligne);
        return view('Budget.element_ligne_budgetaire_sorties.create', compact('ligne'));
    }
    public function manage($ligne_id)
    {
        $ligne = ligne_budgetaire_sortie::findOrFail($ligne_id);
        $elements = element_ligne_budgetaire_sortie::where('id_ligne_budgetaire_sortie', $ligne_id)->get();
        $this->values['title']='Eléments ligne budgétaire sorties';
        return view('Budget.ligne_budgetaire_sorties.element_ligne_budgetaire_sorties.manage', compact('ligne', 'elements'),$this->values);
    }


    // Formulaire avec N lignes
    public function generateForm(Request $request, $id_ligne)
    {
        $ligne = ligne_budgetaire_sortie::findOrFail($id_ligne);
        $count = $request->input('count'); // nombre de lignes demandé

        return view('Budget.element_ligne_budgetaire_sorties.generate', compact('ligne', 'count'));
    }
    public function store(Request $request, $ligne_id)
    {
        $ligne = ligne_budgetaire_sortie::findOrFail($ligne_id);

        $elements = $request->input('elements', []);

        foreach ($elements as $el) {
            element_ligne_budgetaire_sortie::create([
                'libelle_elements_ligne_budgetaire_sortie' => $el['libelle'],
                'code_elements_ligne_budgetaire_sortie' => $el['code'],
                'numero_compte_elements_ligne_budgetaire_sortie' => $el['compte'],
                'description' => $el['description'] ?? null,
                'date_creation' => $el['date_creation'],
                'id_ligne_budgetaire_sortie' => $ligne->id,
                'id_user' => Auth::id(), // ✅ utilisateur connecté
            ]);
        }

        return redirect()->route('element_ligne_budgetaire_sorties.manage', $ligne->id)
            ->with('success', 'Éléments ajoutés avec succès.');
    }
    public function indexElements($ligne_id)
    {
        $ligne = ligne_budgetaire_sortie::findOrFail($ligne_id);
        $elements = element_ligne_budgetaire_sortie::where('id_ligne_budgetaire_sortie', $ligne_id)
            ->with('user')
            ->get();

        return view('Budget.ligne_budgetaire_sorties.element_ligne_budgetaire_sorties.index', compact('ligne', 'elements'));
    }

    public function updateAll(Request $request, $ligne_id)
    {
        $ligne = ligne_budgetaire_sortie::findOrFail($ligne_id);

        $elements = $request->input('elements', []);

        foreach ($elements as $el) {
            $element = element_ligne_budgetaire_sortie::findOrFail($el['id']);
            $element->update([
                'libelle_elements_ligne_budgetaire_sortie' => $el['libelle'],
                'code_elements_ligne_budgetaire_sortie' => $el['code'],
                'numero_compte_elements_ligne_budgetaire_sortie' => $el['compte'],
                'description' => $el['description'] ?? null,
                'date_creation' => $el['date_creation'],
                'id_user' => Auth::id(), // ✅ utilisateur connecté
            ]);
        }

        return redirect()->route('Budget.ligne_budgetaire_sorties.element_ligne_budgetaire_sorties.manage', $ligne->id)
            ->with('success', 'Éléments mis à jour avec succès.');
    }
    public function editForm($ligne_id)
    {
        $ligne = ligne_budgetaire_sortie::findOrFail($ligne_id);
        $elements = element_ligne_budgetaire_sortie::where('id_ligne_budgetaire_sortie', $ligne_id)->get();

        return view('Budget.ligne_budgetaire_sorties.element_ligne_budgetaire_sorties.editForm', compact('ligne', 'elements'));
    }

    // Enregistrement des éléments
    public function store1(Request $request, $id_ligne)
    {
        $ligne = ligne_budgetaire_sortie::findOrFail($id_ligne);

        $request->validate([
            'libelle.*' => 'required|string|max:255',
            'code.*' => 'required|string|max:50',
            'numero_compte.*' => 'required|string|max:50',
            'description.*' => 'nullable|string',
            'date_creation.*' => 'required|date',
        ]);

        foreach ($request->libelle as $key => $val) {
            element_ligne_budgetaire_sortie::create([
                'libelle_elements_ligne_budgetaire_sortie' => $val,
                'code_elements_ligne_budgetaire_sortie' => $request->code[$key],
                'numero_compte_elements_ligne_budgetaire_sortie' => $request->numero_compte[$key],
                'description' => $request->description[$key],
                'date_creation' => $request->date_creation[$key],
                'id_ligne_budgetaire_sortie' => $ligne->id,
                'id_user' => Auth::id(),
            ]);
        }

        return redirect()->route('ligne_budgetaire_sorties.index')
            ->with('success', 'Éléments ajoutés avec succès à la ligne budgétaire sortie.');
    }
}
