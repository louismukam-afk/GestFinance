<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\bon_commande;
use App\Models\bon_commandeok;
use App\Models\element_bon_commande;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; // si tu utilises barryvdh/laravel-dompdf

class ElementBonCommandeController extends Controller
{
    public function create($bon_id)
    {
        $bon = bon_commandeok::findOrFail($bon_id);
        return view('Admin.element_bon.choose_lines', compact('bon'));
    }

    // 2. Afficher le formulaire après choix
    public function buildForm(Request $request, $bon_id)
    {
        $request->validate([
            'nombre_lignes' => 'required|integer|min:1'
        ]);

        $bon = bon_commandeok::findOrFail($bon_id);
        $nombre = $request->nombre_lignes;

        return view('Admin.element_bon.create', compact('bon', 'nombre'));
    }

    public function manage($bon_id)
    {
        $bon = bon_commandeok::findOrFail($bon_id);
        $elements = element_bon_commande::where('id_bon_commande', $bon_id)->get();

        // Si aucun élément encore
        if ($elements->isEmpty()) {
            return redirect()->route('element_bon.create', $bon_id);
        }

        // Sinon, on affiche les choix
        return view('Admin.element_bon.manage', compact('bon', 'elements'));
    }


    public function storetest(Request $request, $bon_id)
    {
        $bon = bon_commandeok::findOrFail($bon_id);

        $data = $request->validate([
            'nom_element_bon_commande.*' => 'required|string',
            'description_elements_bon_commande.*' => 'nullable|string',
            'quantite_element_bon_commande.*' => 'required|integer|min:1',
            'prix_unitaire_element_bon_commande.*' => 'required|numeric|min:0',
            'date_realisation.*' => 'required|date',
        ]);

        DB::beginTransaction();

        try {

            // 🔥 OPTION : supprimer anciens éléments (évite doublons)
            element_bon_commande::where('id_bon_commande', $bon->id)->delete();

            $total_global = 0;
            $elements = [];

            foreach ($data['nom_element_bon_commande'] as $i => $nom) {

                $qte = $data['quantite_element_bon_commande'][$i];
                $pu  = $data['prix_unitaire_element_bon_commande'][$i];
                $total = $qte * $pu;

                $total_global += $total;

                $elements[] = [
                    'nom_element_bon_commande' => $nom,
                    'description_elements_bon_commande' => $data['description_elements_bon_commande'][$i] ?? '',
                    'quantite_element_bon_commande' => $qte,
                    'prix_unitaire_element_bon_commande' => $pu,
                    'montant_total_element_bon_commande' => $total,
                    'date_realisation' => $data['date_realisation'][$i],
                    'id_user' => auth()->id(),
                    'id_bon_commande' => $bon->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // 🔒 VALIDATION MÉTIER
            if ($total_global != $bon->montant_total) {
                return back()->with('error',
                    "⚠️ Total ($total_global FCFA) ≠ Montant du bon ({$bon->montant_total} FCFA)"
                );
            }

            // 🚀 INSERT EN MASSE (PERFORMANCE)
            element_bon_commande::insert($elements);

            // 🔥 MISE À JOUR STATUT DU BON
            $bon->update([
                'statuts' => 1 // ou statut personnalisé
            ]);

            DB::commit();

            return redirect()->route('bon_commande_management')
                ->with('success', '✅ Éléments enregistrés avec succès.');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with('error', '❌ Erreur : '.$e->getMessage());
        }
    }
    public function store(Request $request, $bon_id)
    {
        $bon = bon_commandeok::findOrFail($bon_id);

        $data = $request->validate([
            'nom_element_bon_commande.*' => 'required|string',
            'description_elements_bon_commande.*' => 'nullable|string',
            'quantite_element_bon_commande.*' => 'required|integer|min:1',
            'prix_unitaire_element_bon_commande.*' => 'required|numeric|min:0',
            'date_realisation.*' => 'required|date',
        ]);

        $total_global = 0;
        $elements = [];

        foreach ($data['nom_element_bon_commande'] as $i => $nom) {

            $qte = $data['quantite_element_bon_commande'][$i];
            $pu  = $data['prix_unitaire_element_bon_commande'][$i];
            $total = $qte * $pu;

            $total_global += $total;

            $elements[] = [
                'nom_element_bon_commande' => $nom,
                'description_elements_bon_commande' => $data['description_elements_bon_commande'][$i] ?? '',
                'quantite_element_bon_commande' => $qte,
                'prix_unitaire_element_bon_commande' => $pu,
                'montant_total_element_bon_commande' => $total,
                'date_realisation' => $data['date_realisation'][$i],
                'id_user' => auth()->id(),
                'id_bon_commande' => $bon->id,
            ];
        }

        // 🔥 TOTAL DÉJÀ EXISTANT EN BASE
        $total_existant = element_bon_commande::where('id_bon_commande', $bon->id)
            ->sum('montant_total_element_bon_commande');

        $nouveau_total = $total_existant + $total_global;

        // ❌ SI DÉPASSEMENT
        if ($nouveau_total > $bon->montant_total) {

            return redirect()
                ->route('element_bon.editForm', $bon->id)
                ->withInput()
                ->with('error', "⚠️ Le montant total dépasse le montant du bon !");
        }

        // ✅ INSERTION (PARTIEL AUTORISÉ)
        foreach ($elements as $element) {
            element_bon_commande::create($element);
        }

        // 🔥 CALCUL RESTE
        $reste = $bon->montant_total - $nouveau_total;

        // 🔥 STATUT
        $statut = ($reste > 0) ? 'En cours de réalisation' : 'Bon réalisé';

        // (optionnel si tu veux stocker)
        $bon->update([
            'statuts' => ($reste > 0) ? 0 : 1
        ]);

        return redirect()
            ->route('element_bon.editForm', $bon->id)
            ->with('success', "✅ Enregistrement effectué. Reste : $reste FCFA");
    }
    public function storevo(Request $request, $bon_id)
    {
       // dd('ok store');
        $bon = bon_commandeok::findOrFail($bon_id);

        $data = $request->validate([
            'nom_element_bon_commande.*' => 'required|string',
            'description_elements_bon_commande.*' => 'nullable|string',
            'quantite_element_bon_commande.*' => 'required|integer|min:1',
            'prix_unitaire_element_bon_commande.*' => 'required|numeric|min:0',
            'date_realisation.*' => 'required|date',
        ]);

        $total_global = 0;
        $elements = [];

        // 1️⃣ Calculer le total global avant insertion
        foreach ($data['nom_element_bon_commande'] as $i => $nom) {
            $qte = $data['quantite_element_bon_commande'][$i];
            $pu  = $data['prix_unitaire_element_bon_commande'][$i];
            $total = $qte * $pu;
            $total_global += $total;

            $elements[] = [
                'nom_element_bon_commande' => $nom,
                'description_elements_bon_commande' => $data['description_elements_bon_commande'][$i] ?? '',
                'quantite_element_bon_commande' => $qte,
                'prix_unitaire_element_bon_commande' => $pu,
                'montant_total_element_bon_commande' => $total,
                'date_realisation' => $data['date_realisation'][$i],
                'id_user' => auth()->id(),
                'id_bon_commande' => $bon->id,
            ];
        }

       // dd($elements);
        // 2️⃣ Validation du montant global
     /*   if ($total_global > $bon->montant_total) {
            return back()->with('error', "⚠️ Le montant global des éléments ($total_global FCFA) dépasse le montant du bon ({$bon->montant_total} FCFA).");
        }

        if ($total_global < $bon->montant_total) {
            return back()->with('error', "⚠️ Le montant global des éléments ($total_global FCFA) est inférieur au montant du bon ({$bon->montant_total} FCFA).");
        }*/
        if ($total_global > $bon->montant_total) {
            return redirect()
                ->route('element_bon.create', $bon->id)
                ->withInput()
                ->with('error', "⚠️ Le montant global ($total_global FCFA) dépasse le montant du bon ({$bon->montant_total} FCFA).");
        }

        if ($total_global < $bon->montant_total) {
            return redirect()
                ->route('element_bon.create', $bon->id)
                ->withInput()
                ->with('error', "⚠️ Le montant global ($total_global FCFA) est inférieur au montant du bon ({$bon->montant_total} FCFA).");
        }

        // 3️⃣ Insertion en base seulement si OK
        foreach ($elements as $element) {
            element_bon_commande::create($element);
        }


        return redirect()->route('bon_commande_management')
            ->with('success', '✅ Éléments ajoutés avec succès.');
    }
    public function editForm($bon_id)
    {
        $bon = bon_commandeok::findOrFail($bon_id);
        $elements = element_bon_commande::where('id_bon_commande', $bon_id)->get();

        return view('Admin.element_bon.edit', compact('bon', 'elements'));
    }


    // 3. Enregistrer les éléments
    public function store1(Request $request, $bon_id)
    {
        $bon = bon_commandeok::findOrFail($bon_id);

        $data = $request->validate([
            'nom_element_bon_commande.*' => 'required|string',
            'description_elements_bon_commande.*' => 'nullable|string',
            'quantite_element_bon_commande.*' => 'required|integer|min:1',
            'prix_unitaire_element_bon_commande.*' => 'required|numeric|min:0',
            'date_realisation.*' => 'required|date',
        ]);

        $total_global = 0;

        foreach ($data['nom_element_bon_commande'] as $i => $nom) {
            $qte = $data['quantite_element_bon_commande'][$i];
            $pu  = $data['prix_unitaire_element_bon_commande'][$i];
            $total = $qte * $pu;
            $total_global += $total;

            element_bon_commande::create([
                'nom_element_bon_commande' => $nom,
                'description_elements_bon_commande' => $data['description_elements_bon_commande'][$i],
                'quantite_element_bon_commande' => $qte,
                'prix_unitaire_element_bon_commande' => $pu,
                'montant_total_element_bon_commande' => $total,
                'date_realisation' => $data['date_realisation'][$i],
                'id_user' => Auth::user()->id,
                'id_bon_commande' => $bon->id,
            ]);
        }

        // ✅ Validation montant global
        if ($total_global != $bon->montant_total) {
            return back()->with('error', "Le montant global des éléments ($total_global) doit être égal au montant du bon ({$bon->montant_total}).");
        }

        return redirect()->route('bon_commande_management')->with('success', 'Éléments ajoutés avec succès.');
    }


    public function index($bon_id)
    {
        $bon = bon_commandeok::findOrFail($bon_id);
        $elements = element_bon_commande::where('id_bon_commande', $bon_id)->get();
        $title='Les elements du bon de commande';

        return view('Admin.element_bon.index', compact('bon', 'elements','title'));
    }

    public function exportPdf($bon_id)
    {
        $bon = bon_commandeok::findOrFail($bon_id);
        $elements = element_bon_commande::where('id_bon_commande', $bon_id)->get();

        $pdf = Pdf::loadView('Admin.element_bon.pdf', compact('bon', 'elements'));
        return $pdf->download("elements_bon_{$bon->id}.pdf");
    }


    public function updateAll(Request $request, $bon_id)
    {
        $bon = bon_commandeok::findOrFail($bon_id);

        $data = $request->validate([
            'nom_element_bon_commande.*' => 'required|string',
            'description_elements_bon_commande.*' => 'nullable|string',
            'quantite_element_bon_commande.*' => 'required|integer|min:1',
            'prix_unitaire_element_bon_commande.*' => 'required|numeric|min:0',
            'date_realisation.*' => 'required|date',
        ]);

        $elements = element_bon_commande::where('id_bon_commande', $bon_id)->get();

        $total_global = 0;
        foreach ($elements as $i => $element) {
            $qte = $data['quantite_element_bon_commande'][$i];
            $pu  = $data['prix_unitaire_element_bon_commande'][$i];
            $total = $qte * $pu;
            $total_global += $total;

            $element->update([
                'nom_element_bon_commande' => $data['nom_element_bon_commande'][$i],
                'description_elements_bon_commande' => $data['description_elements_bon_commande'][$i],
                'quantite_element_bon_commande' => $qte,
                'prix_unitaire_element_bon_commande' => $pu,
                'montant_total_element_bon_commande' => $total,
                'date_realisation' => $data['date_realisation'][$i],
            ]);
        }

        if ($total_global > $bon->montant_total) {
            return back()->with('error', "⚠️ Le montant global ($total_global) dépasse le montant du bon ({$bon->montant_total}).");
        }

        return redirect()->route('element_bon.manage', $bon_id)->with('success', 'Éléments mis à jour avec succès ✅');
    }

    public function destroy($id)
    {
        $element = element_bon_commande::findOrFail($id);
        $bon_id = $element->id_bon_commande;
        $element->delete();

        return redirect()->route('element_bon.manage', $bon_id)->with('success', 'Élément supprimé avec succès 🗑️');
    }

}
