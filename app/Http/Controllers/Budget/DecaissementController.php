<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Models\annee_academique;
use App\Models\bon_commandeok;
use App\Models\budget;
use App\Models\caisse;
use App\Models\decaissement;
use App\Models\donnee_budgetaire_sortie;
use App\Models\donnee_ligne_budgetaire_sortie;
use App\Models\element_ligne_budgetaire_sortie;
use App\Models\entite;
use App\Models\ligne_budgetaire_sortie;
use App\Models\personnel;
use App\Models\Transfert_caisse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PDF;

class DecaissementController extends Controller
{

    // LISTE DES BONS VALIDÉS

    public function index(Request $request)
    {
        $query = bon_commandeok::with(['personnels', 'user'])
            ->where(function ($q) {
                $q->where('statuts', 1)
                    ->orWhere('validation_pdg', true);
            });

        // 🔍 FILTRES
        if ($request->date_debut) {
            $query->whereDate('date_debut', '>=', $request->date_debut);
        }

        if ($request->date_fin) {
            $query->whereDate('date_fin', '<=', $request->date_fin);
        }

        if ($request->id_personnel) {
            $query->where('id_personnel', $request->id_personnel);
        }

        if ($request->id_user) {
            $query->where('id_user', $request->id_user);
        }

        $bons = $query->get()->map(function ($bon) {

            $total = decaissement::where('id_bon_commande', $bon->id)
                ->sum('montant');

            $bon->total_decaisse = $total;
            $bon->reste = $bon->montant_total - $total;

            $bon->statut_financement = $bon->reste > 0
                ? 'En cours'
                : 'Réalisé';

            return $bon;
        });

        $personnels = personnel::all();
        $users = User::all();

        return view('decaissements.index', compact('bons','personnels','users'));
    }
    public function indexvalide()
    {
        $bons = bon_commandeok::with(['personnels'])
            ->where(function ($q) {
                $q->where('statuts', 1)
                    ->orWhere('validation_pdg', true);
            })
            ->get()
            ->map(function ($bon) {

                $total = decaissement::where('id_bon_commande', $bon->id)
                    ->sum('montant');

                $bon->total_decaisse = $total;
                $bon->reste = $bon->montant_total - $total;

                if ($bon->reste > 0) {
                    $bon->statut_financement = 'En cours de réalisation';
                } else {
                    $bon->statut_financement = 'Bon réalisé';
                }

                return $bon;
            });

        return view('decaissements.index', compact('bons'));
    }
    public function index1()
    {
        $bons = bon_commandeok::where(function ($q) {
            $q->where('statuts', 1)
                ->orWhere('validation_pdg', true);
        })->get();

        return view('decaissements.index', compact('bons'));
    }
    public function getTransfertCaisse($id)
    {
        $transfert = Transfert_caisse::where('id_caisse_arrivee', $id)
            ->latest()
            ->first();

        return response()->json([
            'id' => $transfert ? $transfert->id : 0
        ]);
    }
    // FORMULAIRE FINANCEMENT
    public function create($id)
    {
        $bon = bon_commandeok::findOrFail($id);

        $totalDecaisse = decaissement::where('id_bon_commande', $id)->sum('montant');
        $reste = $bon->montant_total - $totalDecaisse;
        $caissest = caisse::where('type_caisse', 1)->get();
        // 🔥 DONNÉES
        $budgets = budget::all();
        $annees = annee_academique::all();

        $donnees = donnee_ligne_budgetaire_sortie::all();
        $caisses = Transfert_caisse::all();

        return view('decaissements.create', compact(
            'bon',
            'reste',
            'budgets',
            'annees',
            'donnees',
            'caisses',
            'caissest'
        ));
    }
    public function getSoldeAjax($id)
    {
        $entree = Transfert_caisse::where('id_caisse_arrivee', $id)
            ->sum('montant_transfert');

        $sortie = Transfert_caisse::where('id_caisse_depart', $id)
            ->sum('montant_transfert');

        $decaisse = decaissement::where('id_caisse', $id)
            ->sum('montant');

        $solde = $entree - $sortie - $decaisse;

        return response()->json([
            'solde' => $solde
        ]);
    }
    public function getSoldeAjax1($id_caisse)
    {
        $entree = Transfert_caisse::where('id_caisse_arrivee', $id_caisse)
            ->sum('montant_transfert');

        $sortie = Transfert_caisse::where('id_caisse_depart', $id_caisse)
            ->sum('montant_transfert');

        $decaisse = decaissement::where('id_caisse', $id_caisse)
            ->sum('montant');

        return $entree - $sortie - $decaisse;
    }
    public function getSoldeCaisse($id_caisse)
    {
        $entree = Transfert_caisse::where('id_caisse_arrivee', $id_caisse)
            ->sum('montant_transfert');

        $sortie = Transfert_caisse::where('id_caisse_depart', $id_caisse)
            ->sum('montant_transfert');

        $decaisse = decaissement::where('id_caisse', $id_caisse)
            ->sum('montant');

        return $entree - $sortie - $decaisse;
    }
    // ENREGISTREMENT

    public function store(Request $request)
    {

        $request->validate([
            'id_bon_commande' => 'required',
            'montant' => 'required|numeric|min:1',
            'id_budget' => 'required',
            'id_ligne_budgetaire_sortie' => 'required',
            'id_elements_ligne_budgetaire_sortie' => 'required',
            'id_donnee_budgetaire_sortie' => 'required',
            'id_donnee_ligne_budgetaire_sortie' => 'required',
            'id_annee_academique' => 'required',
        ]);

        $bon = bon_commandeok::findOrFail($request->id_bon_commande);

        // 🔒 Vérification validation
        if (!$bon->validation_pdg && $bon->statuts != 1) {
            return back()->with('error', 'Bon non validé');
        }

        // 🔒 Vérification cohérence budgétaire
        $donnee = donnee_ligne_budgetaire_sortie::where('id', $request->id_donnee_ligne_budgetaire_sortie)
            ->where('id_budget', $request->id_budget)
            ->where('id_ligne_budgetaire_sortie', $request->id_ligne_budgetaire_sortie)
            ->where('id_element_ligne_budgetaire_sortie', $request->id_elements_ligne_budgetaire_sortie)
            ->where('id_donnee_budgetaire_sortie', $request->id_donnee_budgetaire_sortie)
            ->first();


        if (!$donnee) {
            return back()->with('error', 'Incohérence budgétaire détectée');
        }

        // 🔒 Vérification budget
        $deja = decaissement::where('id_donnee_ligne_budgetaire_sortie', $donnee->id)->sum('montant');
        $resteBudget = $donnee->montant - $deja;

        if ($request->montant > $resteBudget) {
            return back()->with('error', 'Budget insuffisant');
        }
// 🔒 Vérification bon
        $total = decaissement::where('id_bon_commande', $bon->id)->sum('montant');
        $reste = $bon->montant_total - ($total + $request->montant);
        $statut = ($reste > 0) ? 'En cours de réalisation' : 'Bon réalisé';

// 🔥 STATUT
        $statut = ($reste > 0) ? 'En cours de réalisation' : 'Bon réalisé';
        // ==========================
        // 🔥 LOGIQUE CAISSE / BANQUE
        // ==========================

        $id_caisse = 0;
        $id_transfert_caisse = 0;
        $id_banque = 0;
        // ==========================
        // CAS ESPÈCE
        // ==========================
        if ($request->filled('id_caisse')) {

            if (!$request->id_transfert_caisse) {
                return back()->with('error', 'Aucun transfert trouvé');
            }

            $transfert = Transfert_caisse::find($request->id_transfert_caisse);

            if (!$transfert) {
                return back()->with('error', 'Transfert invalide');
            }

            $entree = Transfert_caisse::where('id_caisse_arrivee', $transfert->id_caisse_arrivee)
                ->sum('montant_transfert');

            $sortie = decaissement::where('id_caisse', $transfert->id_caisse_arrivee)
                ->sum('montant');

            $solde = $entree - $sortie;

            if ($request->montant > $solde) {
                return back()->with('error', 'Fonds insuffisants');
            }

            $id_caisse = $transfert->id_caisse_arrivee;
            $id_transfert_caisse = $transfert->id;

        }

        // ==========================
        // CAS BANQUE
        // ==========================
        else {
            $id_banque = $request->id_banque ?? 0;
        }
        $total = decaissement::where('id_bon_commande', $bon->id)->sum('montant');
        $reste = $bon->montant_total - $total;

        if ($request->montant > $reste) {
            return back()->with('error', 'Montant dépasse le reste du bon');
        }
        // ==========================
        // INSERT
      /*  if (!empty($request->id_caisse)) {

            // 💵 ESPÈCE
            $transfert = Transfert_caisse::findOrFail($request->id_transfert_caisse);

            $entree = Transfert_caisse::where('id_caisse_arrivee', $transfert->id_caisse_arrivee)
                ->sum('montant_transfert');

            $sortie = decaissement::where('id_caisse', $transfert->id_caisse_arrivee)
                ->sum('montant');

            $solde = $entree - $sortie;

            if ($request->montant > $solde) {
                return back()->with('error', 'Fonds insuffisants');
            }

            $id_caisse = $transfert->id_caisse_arrivee;
            $id_transfert_caisse = $transfert->id;

        } else {

            // 🏦 BANQUE
            $id_banque = $request->id_banque ?? 0;
        }
      //  dd($request, $donnee,$resteBudget,$transfert);
        // 🔒 Vérification bon
        $total = decaissement::where('id_bon_commande', $bon->id)->sum('montant');
        $reste = $bon->montant_total - $total;

        if ($request->montant > $reste) {
            return back()->with('error', 'Montant dépasse le reste du bon');
        }*/

        // ✅ INSERT
        //dd($bon->id, $request->id_bon_commande);
        decaissement::create([
            'id_bon_commande' => $bon->id,
            'id_budget' => $request->id_budget,
            'id_ligne_budgetaire_sortie' => $request->id_ligne_budgetaire_sortie,
            'id_elements_ligne_budgetaire_sortie' => $request->id_elements_ligne_budgetaire_sortie,
            'id_donnee_budgetaire_sortie' => $request->id_donnee_budgetaire_sortie,
            'id_donnee_ligne_budgetaire_sortie' => $request->id_donnee_ligne_budgetaire_sortie,
            'id_annee_academique' => $request->id_annee_academique,

            'id_caisse' => $id_caisse,
            'id_transfert_caisse' => $id_transfert_caisse,
            'id_banque' => $id_banque,

            'numero_depense' => 'DEP'.time(),
            'motif' => $request->motif,
            'date_depense' => $request->date_depense ?? now(),
            'reste' => $reste,
            'statut_financement' => $statut,
            'montant' => $request->montant,
            'id_user' => auth()->id(),
            'id_personnel' => $bon->id_personnel,
        ]);

        return redirect()->route('decaissements.index')
            ->with('success', 'Décaissement enregistré');
    }

    public function store2(Request $request)
    {
        $request->validate([
            'id_bon_commande' => 'required',
            'montant' => 'required|numeric|min:1',
            'id_budget' => 'required',
            'id_ligne_budgetaire_sortie' => 'required',
            'id_elements_ligne_budgetaire_sortie' => 'required',
            'id_donnee_budgetaire_sortie' => 'required',
            'id_donnee_ligne_budgetaire_sortie' => 'required',
            'id_annee_academique' => 'required',
        ]);

        $bon = bon_commandeok::findOrFail($request->id_bon_commande);

        // 🔒 Vérification validation
        if (!$bon->validation_pdg && $bon->statuts != 1) {
            return back()->with('error', 'Bon non validé');
        }

        // 🔒 Vérification cohérence hiérarchique
        $donnee = donnee_ligne_budgetaire_sortie::where('id', $request->id_donnee_ligne_budgetaire_sortie)
            ->where('id_budget', $request->id_budget)
            ->where('id_ligne_budgetaire_sortie', $request->id_ligne_budgetaire_sortie)
            ->where('id_element_ligne_budgetaire_sortie', $request->id_elements_ligne_budgetaire_sortie)
            ->where('id_donnee_budgetaire_sortie', $request->id_donnee_budgetaire_sortie)
            ->first();

        if (!$donnee) {
            return back()->with('error', 'Incohérence budgétaire détectée');
        }

        // 🔒 Vérification budget disponible
        $deja = decaissement::where('id_donnee_ligne_budgetaire_sortie', $donnee->id)
            ->sum('montant');

        $resteBudget = $donnee->montant - $deja;

        if ($request->montant > $resteBudget) {
            return back()->with('error', 'Budget insuffisant');
        }

        // 🔒 Vérification caisse (calcul réel)
        $transfert = Transfert_caisse::findOrFail($request->id_transfert_caisse);

        $entree = Transfert_caisse::where('id_caisse_arrivee', $transfert->id_caisse_arrivee)
            ->sum('montant_transfert');

        $sortie = decaissement::where('id_caisse', $transfert->id_caisse_arrivee)
            ->sum('montant');

        $solde = $entree - $sortie;

        if ($request->montant > $solde) {
            return back()->with('error', 'Fonds insuffisants');
        }

        // 🔒 Vérification bon
        $total = decaissement::where('id_bon_commande', $bon->id)->sum('montant');
        $reste = $bon->montant_total - $total;

        if ($request->montant > $reste) {
            return back()->with('error', 'Montant dépasse le reste du bon');
        }
        $id_caisse = null;
        $id_banque = null;

        if($request->type_paiement == 'caisse'){
            $id_caisse = $request->id_caisse;
        } else {
            $id_banque = $request->id_banque;
        }
        // ✅ INSERT
        decaissement::create([
            'id_bon_commande' => $bon->id,

            'id_budget' => $request->id_budget,
            'id_ligne_budgetaire_sortie' => $request->id_ligne_budgetaire_sortie,
            'id_elements_ligne_budgetaire_sortie' => $request->id_elements_ligne_budgetaire_sortie,
            'id_donnee_budgetaire_sortie' => $request->id_donnee_budgetaire_sortie,
            'id_donnee_ligne_budgetaire_sortie' => $request->id_donnee_ligne_budgetaire_sortie,
            'id_annee_academique' => $request->id_annee_academique,
            'date_depense' => $request->date_depense,

            'id_caisse' => $transfert->id_caisse_arrivee,
            'id_transfert_caisse' => $transfert->id,
            //'id_caisse' => $id_caisse,
            'id_banque' => $id_banque,
            'numero_depense' => 'DEP'.time(),
            'motif' => $request->motif,
           // 'date_depense' => now(),

            'montant' => $request->montant,
            'id_user' => auth()->id(),
            'id_personnel' => $bon->id_personnel,
        ]);

        return redirect()->route('decaissements.index')
            ->with('success', 'Décaissement enregistré');
    }

    public function getLignes($budget)
    {
        return ligne_budgetaire_sortie::whereHas('donnee_budgetaire_sorties', function($q) use ($budget){
            $q->where('id_budget', $budget);
        })->get();
    }

    public function getElements($ligne)
    {
        return element_ligne_budgetaire_sortie::whereHas('donnee_ligne_budgetaire_sorties', function($q) use ($ligne){
            $q->where('id_ligne_budgetaire_sortie', $ligne);
        })->get();
    }

    public function getDonneesBudget($ligne)
    {
        return donnee_budgetaire_sortie::where('id_ligne_budgetaire_sortie', $ligne)
            ->whereNotNull('id_budget')
            ->get();
    }

    public function getDonneesLigne($element)
    {
        return donnee_ligne_budgetaire_sortie::where('id_element_ligne_budgetaire_sortie', $element)->get();
    }
    public function store1(Request $request)
    {
        $request->validate([
            'id_bon_commande' => 'required',
            'montant' => 'required|numeric|min:1',
            'id_donnee_ligne_budgetaire_sortie' => 'required',
            'id_transfert_caisse' => 'required',
        ]);

        $bon = bon_commandeok::findOrFail($request->id_bon_commande);

        // 🔒 Vérification validation bon
        if (!$bon->validation_pdg && $bon->statuts != 1) {
            return back()->with('error', 'Bon non validé');
        }

        // 🔒 Vérification budget
        $donnee = donnee_ligne_budgetaire_sortie::findOrFail($request->id_donnee_ligne_budgetaire_sortie);

        $dejaDepense = decaissement::where('id_donnee_ligne_budgetaire_sortie', $donnee->id)
            ->sum('montant');

        $resteBudget = $donnee->montant - $dejaDepense;

        if ($request->montant > $resteBudget) {
            return back()->with('error', 'Budget insuffisant');
        }

        // 🔒 Vérification caisse
        $transfert = Transfert_caisse::findOrFail($request->id_transfert_caisse);

        $solde = $transfert->sode_caisse;

        if ($request->montant > $solde) {
            return back()->with('error', 'Fonds insuffisants en caisse');
        }

        // 🔒 Vérification reste bon
        $totalDecaisse = decaissement::where('id_bon_commande', $bon->id)->sum('montant');
        $resteBon = $bon->montant_total - $totalDecaisse;

        if ($request->montant > $resteBon) {
            return back()->with('error', 'Montant supérieur au reste du bon');
        }

        // ✅ INSERTION
        decaissement::create([
            'id_bon_commande' => $bon->id,

            'id_budget' => $donnee->id_budget,
            'id_ligne_budgetaire_sortie' => $donnee->id_ligne_budgetaire_sortie,
            'id_elements_ligne_budgetaire_sortie' => $donnee->id_element_ligne_budgetaire_sortie,
            'id_donnee_budgetaire_sortie' => $donnee->id_donnee_budgetaire_sortie,
            'id_donnee_ligne_budgetaire_sortie' => $donnee->id,

            'id_caisse' => $transfert->id_caisse_arrivee,
            'id_transfert_caisse' => $transfert->id,

            'numero_depense' => 'DEP'.time(),
            'motif' => $request->motif,
            'date_depense' => now(),

            'montant' => $request->montant,

            'id_user' => Auth::id(),
            'id_personnel' => $bon->id_personnel,
            'id_annee_academique' => 1, // adapte si dynamique
        ]);

        return redirect()->route('decaissements.index')
            ->with('success', 'Décaissement effectué');
    }

    // SUPPRESSION
    public function destroy($id)
    {
        decaissement::findOrFail($id)->delete();

        return back()->with('success', 'Supprimé');
    }

    // REPORTING


    public function detailBon(Request $request, $id)
    {
        $bon = bon_commandeok::with(['personnels','user','entites'])->findOrFail($id);

        $query = decaissement::with(['personnels','user','caisses'])
            ->where('id_bon_commande', $id);

        // 🔍 FILTRES EXISTANTS
        if ($request->date_debut) {
            $query->whereDate('date_depense', '>=', $request->date_debut);
        }

        if ($request->date_fin) {
            $query->whereDate('date_depense', '<=', $request->date_fin);
        }

        if ($request->id_personnel) {
            $query->where('id_personnel', $request->id_personnel);
        }

        if ($request->id_user) {
            $query->where('id_user', $request->id_user);
        }

        if ($request->id_caisse) {
            $query->where('id_caisse', $request->id_caisse);
        }

        // 🔥 FILTRE ENTITÉ (via le bon)
        if ($request->id_entite) {
            if ($bon->id_entite != $request->id_entite) {
                // Aucun résultat si entité ne correspond pas
                $query->whereRaw('1=0');
            }
        }

        $decaissements = $query->orderBy('date_depense','desc')->get();

        $total = $decaissements->sum('montant');
        $reste = $bon->montant_total - $total;

        $personnels = personnel::all();
        $users = User::all();
        $caisses = caisse::where('type_caisse',1)->get();
        $entites = entite::all();

        return view('decaissements.detail_bon', compact(
            'bon',
            'decaissements',
            'total',
            'reste',
            'personnels',
            'users',
            'caisses',
            'entites'
        ));
    }
    public function detailBon1(Request $request, $id)
    {
        $bon = bon_commandeok::with(['personnels','user','entites'])->findOrFail($id);

        $query = decaissement::with(['personnels','user','caisses'])
            ->where('id_bon_commande', $id);

        // 🔍 FILTRES
        if ($request->date_debut) {
            $query->whereDate('date_depense', '>=', $request->date_debut);
        }

        if ($request->date_fin) {
            $query->whereDate('date_depense', '<=', $request->date_fin);
        }

        if ($request->id_personnel) {
            $query->where('id_personnel', $request->id_personnel);
        }

        if ($request->id_user) {
            $query->where('id_user', $request->id_user);
        }

        if ($request->id_caisse) {
            $query->where('id_caisse', $request->id_caisse);
        }

        $decaissements = $query->orderBy('date_depense','desc')->get();

        // 🔥 TOTAL
        $total = $decaissements->sum('montant');

        $reste = $bon->montant_total - $total;

        // 🔥 DONNÉES FILTRE
        $personnels = personnel::all();
        $users = User::all();
        $caisses = caisse::where('type_caisse',1)->get();

        return view('decaissements.detail_bon', compact(
            'bon',
            'decaissements',
            'total',
            'reste',
            'personnels',
            'users',
            'caisses'
        ));
    }

    public function reporting(Request $request)
    {
        $query = bon_commandeok::with(['decaissements','personnels','user','entites']);

        // 🔍 FILTRES EXISTANTS
        if ($request->date_debut) {
            $query->whereDate('date_debut', '>=', $request->date_debut);
        }

        if ($request->date_fin) {
            $query->whereDate('date_fin', '<=', $request->date_fin);
        }

        if ($request->id_personnel) {
            $query->where('id_personnel', $request->id_personnel);
        }

        if ($request->id_user) {
            $query->where('id_user', $request->id_user);
        }

        // 🔥 FILTRE PAR CAISSE (IMPORTANT)
        if ($request->id_caisse) {
            $query->whereHas('decaissements', function($q) use ($request) {
                $q->where('id_caisse', $request->id_caisse);
            });
        }

        $bons = $query->get()->map(function ($bon) {

            $fin = $bon->decaissements->sum('montant');
            $reste = $bon->montant_total - $fin;

            $bon->total_decaisse = $fin;
            $bon->reste = $reste;

            if ($fin == 0) {
                $bon->statut_financier = 'Non financé';
            } elseif ($reste > 0) {
                $bon->statut_financier = 'Partiel';
            } else {
                $bon->statut_financier = 'Financé';
            }

            return $bon;
        });

        // 🔥 CAISSES TYPE SORTIE UNIQUEMENT
        $caisses = caisse::where('type_caisse', 1)->get();

        $personnels = personnel::all();
        $users = User::all();

        return view('decaissements.reporting', compact(
            'bons','personnels','users','caisses'
        ));
    }
    public function reporting1(Request $request)
    {
        $query = bon_commandeok::with(['decaissements','personnels','user']);

        // 🔍 FILTRES
        if ($request->date_debut) {
            $query->whereDate('date_debut', '>=', $request->date_debut);
        }

        if ($request->date_fin) {
            $query->whereDate('date_fin', '<=', $request->date_fin);
        }

        if ($request->id_personnel) {
            $query->where('id_personnel', $request->id_personnel);
        }

        if ($request->id_user) {
            $query->where('id_user', $request->id_user);
        }

        $bons = $query->get()->map(function ($bon) {

            $fin = $bon->decaissements->sum('montant');
            $reste = $bon->montant_total - $fin;

            $bon->total_decaisse = $fin;
            $bon->reste = $reste;

            if ($fin == 0) {
                $bon->statut_financier = 'Non financé';
            } elseif ($reste > 0) {
                $bon->statut_financier = 'Partiel';
            } else {
                $bon->statut_financier = 'Financé';
            }

            return $bon;
        });

        $personnels = personnel::all();
        $users = User::all();

        return view('decaissements.reporting', compact('bons','personnels','users'));
    }


    // PDF
    public function exportPdf()
    {
        $bons = bon_commandeok::with('decaissements')->get();

        $pdf = PDF::loadView('decaissements.pdf', compact('bons'));

        return $pdf->download('decaissements.pdf');
    }
}
