<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Models\annee_academique;
use App\Models\bon_commandeok;
use App\Models\Budget;
use App\Models\BanqueUser;
use App\Models\CaisseUser;
use App\Models\caisse;
use App\Models\decaissement;
use App\Models\donnee_budgetaire_sortie;
use App\Models\donnee_ligne_budgetaire_sortie;
use App\Models\element_ligne_budgetaire_sortie;
use App\Models\entite;
use App\Models\entree_speciale;
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
        $query = bon_commandeok::with(['personnels', 'user', 'decaissements'])
            ->where('statuts', 1);

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

        $bons = $query->get()->map(function ($bon) {

            $total = $bon->decaissements->sum('montant');

            $bon->total_decaisse = $total;
            $bon->reste = $bon->montant_total - $total;

            $bon->statut_financement = $bon->reste > 0
                ? 'En cours'
                : 'Réalisé';

            return $bon;
        })->filter(fn($bon) => $bon->reste > 0)->values();

        $personnels = personnel::all();
        return view('decaissements.index', compact('bons','personnels'));
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
        $bon = bon_commandeok::with([
            'budget',
            'ligne_budgetaire_sortie',
            'elements_ligne_budgetaire_sortie',
            'donnee_budgetaire_sortie',
            'donnee_ligne_budgetaire_sortie',
            'annee_academique',
            'entree_speciale',
        ])->findOrFail($id);

        if (!$bon->statuts || !$bon->id_budget || !$bon->id_donnee_ligne_budgetaire_sortie || !$bon->id_annee_academique) {
            return redirect()->route('decaissements.index')
                ->with('error', 'Ce bon doit etre valide et impute par la DAF avant financement.');
        }

        $totalDecaisse = decaissement::where('id_bon_commande', $id)->sum('montant');
        $reste = $bon->montant_total - $totalDecaisse;
        $caissest = $this->caissesAffecteesUtilisateur(auth()->id(), 'decaissement');
        // 🔥 DONNÉES
        $budgets = Budget::all();
        $annees = annee_academique::all();

        $donnees = donnee_ligne_budgetaire_sortie::all();
        $caisses = Transfert_caisse::all();
        $entreesSpeciales = entree_speciale::with(['echeances', 'decaissements'])
            ->whereIn('type_entree', ['dette', 'don','apport'])
            ->where('statut', 'actif')
            ->orderByDesc('date_entree')
            ->get();

        return view('decaissements.create', compact(
            'bon',
            'reste',
            'caissest'
        ));
    }
    public function getSoldeAjax($id)
    {
        if (!$this->utilisateurAutoriseCaisse(auth()->id(), (int) $id, 'decaissement')) {
            return response()->json(['solde' => 0, 'message' => 'Caisse non affectee'], 403);
        }

        $solde = $this->soldeCaisse((int) $id);

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

        $entreesSpeciales = entree_speciale::with('echeances')
            ->where('id_caisse', $id_caisse)
            ->where('statut', '!=', 'annule')
            ->get()
            ->sum(fn($e) => $e->montant_net_encaisse);

        return $entree + $entreesSpeciales - $sortie - $decaisse;
    }
    public function getSoldeCaisse($id_caisse)
    {
        return $this->soldeCaisse((int) $id_caisse);
    }

    private function soldeCaisse(int $idCaisse): float
    {
        $entree = Transfert_caisse::where('id_caisse_arrivee', $idCaisse)
            ->sum('montant_transfert');

        $sortie = Transfert_caisse::where('id_caisse_depart', $idCaisse)
            ->sum('montant_transfert');

        $decaisse = decaissement::where('id_caisse', $idCaisse)
            ->sum('montant');

        $entreesSpeciales = entree_speciale::with('echeances')
            ->where('id_caisse', $idCaisse)
            ->where('statut', '!=', 'annule')
            ->get()
            ->sum(fn($e) => $e->montant_net_encaisse);

        return $entree + $entreesSpeciales - $sortie - $decaisse;
    }

    private function caissesAffecteesUtilisateur(?int $userId, string $operation)
    {
        $droit = $operation === 'encaissement' ? 'peut_encaisser' : 'peut_decaisser';

        return caisse::whereHas('affectations', function ($query) use ($userId, $droit) {
            $query->where('id_user', $userId)
                ->where('actif', true)
                ->where($droit, true)
                ->where(function ($q) {
                    $q->whereNull('date_debut')->orWhereDate('date_debut', '<=', now()->toDateString());
                })
                ->where(function ($q) {
                    $q->whereNull('date_fin')->orWhereDate('date_fin', '>=', now()->toDateString());
                });
        })->orderBy('nom_caisse')->get();
    }

    private function utilisateurAutoriseCaisse(?int $userId, int $idCaisse, string $operation): bool
    {
        $droit = $operation === 'encaissement' ? 'peut_encaisser' : 'peut_decaisser';

        return CaisseUser::where('id_user', $userId)
            ->where('id_caisse', $idCaisse)
            ->where('actif', true)
            ->where($droit, true)
            ->where(function ($q) {
                $q->whereNull('date_debut')->orWhereDate('date_debut', '<=', now()->toDateString());
            })
            ->where(function ($q) {
                $q->whereNull('date_fin')->orWhereDate('date_fin', '>=', now()->toDateString());
            })
            ->exists();
    }

    private function utilisateurAutoriseBanque(?int $userId, int $idBanque, string $operation): bool
    {
        $droit = $operation === 'encaissement' ? 'peut_encaisser' : 'peut_decaisser';

        return BanqueUser::where('id_user', $userId)
            ->where('id_banque', $idBanque)
            ->where('actif', true)
            ->where($droit, true)
            ->where(function ($q) {
                $q->whereNull('date_debut')->orWhereDate('date_debut', '<=', now()->toDateString());
            })
            ->where(function ($q) {
                $q->whereNull('date_fin')->orWhereDate('date_fin', '>=', now()->toDateString());
            })
            ->exists();
    }
    // ENREGISTREMENT

    public function store(Request $request)
    {

        $request->validate([
            'id_bon_commande' => 'required',
            'montant' => 'required|numeric|min:1',
            'motif' => 'required|string|max:255',
            'date_depense' => 'required|date',
            'id_caisse' => 'nullable|integer',
            'id_banque' => 'nullable|integer',
            'id_transfert_caisse' => 'nullable|integer',
        ]);

        $bon = bon_commandeok::findOrFail($request->id_bon_commande);

        if ($bon->statuts != 1) {
            return back()->with('error', 'Bon non valide par tous les niveaux');
        }

        if (!$bon->id_budget || !$bon->id_ligne_budgetaire_sortie || !$bon->id_elements_ligne_budgetaire_sortie || !$bon->id_donnee_budgetaire_sortie || !$bon->id_donnee_ligne_budgetaire_sortie || !$bon->id_annee_academique) {
            return back()->with('error', 'Bon non impute par la DAF');
        }

        // 🔒 Vérification validation
        if (!$bon->validation_pdg && $bon->statuts != 1) {
            return back()->with('error', 'Bon non validé');
        }

        // 🔒 Vérification cohérence budgétaire
        $donnee = donnee_ligne_budgetaire_sortie::where('id', $bon->id_donnee_ligne_budgetaire_sortie)
            ->where('id_budget', $bon->id_budget)
            ->where('id_ligne_budgetaire_sortie', $bon->id_ligne_budgetaire_sortie)
            ->where('id_element_ligne_budgetaire_sortie', $bon->id_elements_ligne_budgetaire_sortie)
            ->where('id_donnee_budgetaire_sortie', $bon->id_donnee_budgetaire_sortie)
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
        if ($bon->id_entree_speciale) {
            $entreeSpeciale = entree_speciale::with(['echeances', 'decaissements'])
                ->whereIn('type_entree', ['dette', 'don','apport'])
                ->where('statut', '!=', 'annule')
                ->find($bon->id_entree_speciale);

            if (!$entreeSpeciale) {
                return back()->with('error', 'Entree speciale invalide');
            }

            $disponibleEntreeSpeciale = $entreeSpeciale->montant_net_encaisse - $entreeSpeciale->decaissements->sum('montant');

            if ($request->montant > $disponibleEntreeSpeciale) {
                return back()->with('error', 'Montant superieur au disponible de cette entree speciale');
            }
        }

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

            if (!$this->utilisateurAutoriseCaisse(auth()->id(), (int) $request->id_caisse, 'decaissement')) {
                return back()->with('error', 'Cette caisse ne vous est pas affectee pour les decaissements.');
            }

            $solde = $this->soldeCaisse((int) $request->id_caisse);

            if ($request->montant > $solde) {
                return back()->with('error', 'Fonds insuffisants');
            }

            $id_caisse = (int) $request->id_caisse;
            $id_transfert_caisse = (int) ($request->id_transfert_caisse ?? 0);

        }

        // ==========================
        // CAS BANQUE
        // ==========================
        else {
            $id_banque = $request->id_banque ?? 0;
            if (!$this->utilisateurAutoriseBanque(auth()->id(), (int) $id_banque, 'decaissement')) {
                return back()->with('error', 'Cette banque ne vous est pas affectee pour les decaissements.');
            }
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
        $decaissement = decaissement::create([
            'id_bon_commande' => $bon->id,
            'id_budget' => $bon->id_budget,
            'id_ligne_budgetaire_sortie' => $bon->id_ligne_budgetaire_sortie,
            'id_elements_ligne_budgetaire_sortie' => $bon->id_elements_ligne_budgetaire_sortie,
            'id_donnee_budgetaire_sortie' => $bon->id_donnee_budgetaire_sortie,
            'id_donnee_ligne_budgetaire_sortie' => $bon->id_donnee_ligne_budgetaire_sortie,
            'id_annee_academique' => $bon->id_annee_academique,

            'id_caisse' => $id_caisse,
            'id_transfert_caisse' => $id_transfert_caisse,
            'id_banque' => $id_banque,
            'id_entree_speciale' => $bon->id_entree_speciale ?? 0,

            'numero_depense' => 'DEP'.time(),
            'motif' => $request->motif,
            'date_depense' => $request->date_depense ?? now(),
            'reste' => $reste,
            'statut_financement' => $statut,
            'montant' => $request->montant,
            'id_user' => auth()->id(),
            'id_personnel' => $bon->id_personnel,
        ]);

        return redirect()->route('decaissements.recu', $decaissement->id)
            ->with('success', 'Décaissement enregistré');
    }

    public function recu(Request $request, $id)
    {
        $decaissement = decaissement::with([
            'bon.entites',
            'bon.personnels',
            'bon.user',
            'bon.budget',
            'bon.ligne_budgetaire_sortie',
            'bon.elements_ligne_budgetaire_sortie',
            'bon.donnee_budgetaire_sortie',
            'bon.donnee_ligne_budgetaire_sortie',
            'bon.annee_academique',
            'caisses',
            'banques',
            'user',
            'personnels',
            'budgets',
            'ligne_budgetaire_sorties',
            'elements_ligne_budgetaire_sorties',
            'donnee_budgetaire_sorties',
            'donnee_ligne_budgetaire_sorties',
            'annee_academiques',
        ])->findOrFail($id);

        $bon = $decaissement->bon;
        $totalDecaisse = decaissement::where('id_bon_commande', $bon->id)->sum('montant');
        $totalAvant = max(0, $totalDecaisse - (float) $decaissement->montant);
        $resteApres = max(0, (float) $bon->montant_total - $totalDecaisse);
        $modePaiement = $decaissement->id_banque
            ? 'Banque - ' . ($decaissement->banques->nom_banque ?? '')
            : 'Caisse - ' . ($decaissement->caisses->nom_caisse ?? '');

        $format = $request->query('format') === 'a5' ? 'a5' : 'a4';
        $paper = $format === 'a5' ? 'a5' : 'a4';
        $orientation = $format === 'a5' ? 'landscape' : 'portrait';
        $autoPrint = $request->boolean('print');

        if ($autoPrint) {
            return view('decaissements.recu', compact(
                'decaissement',
                'bon',
                'totalAvant',
                'totalDecaisse',
                'resteApres',
                'modePaiement',
                'format',
                'autoPrint'
            ));
        }

        $pdf = PDF::loadView('decaissements.recu', compact(
            'decaissement',
            'bon',
            'totalAvant',
            'totalDecaisse',
            'resteApres',
            'modePaiement',
            'format',
            'autoPrint'
        ))->setPaper($paper, $orientation);

        return $pdf->stream('recu_decaissement_'.$format.'_'.$decaissement->numero_depense.'.pdf');
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

    public function destroyDecaissement($bonId, $decaissementId)
    {
        $decaissement = decaissement::where('id_bon_commande', $bonId)
            ->where('id', $decaissementId)
            ->firstOrFail();

        $decaissement->delete();

        return back()->with('success', 'Decaissement supprime');
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
    public function etatRealises(Request $request)
    {
        return $this->etatBonsFinancement($request, 'realises');
    }

    public function etatNonFinances(Request $request)
    {
        return $this->etatBonsFinancement($request, 'non_finances');
    }

    public function etatRealisesPdf(Request $request)
    {
        return $this->etatBonsFinancementPdf($request, 'realises');
    }

    public function etatNonFinancesPdf(Request $request)
    {
        return $this->etatBonsFinancementPdf($request, 'non_finances');
    }

    private function etatBonsFinancement(Request $request, string $type)
    {
        return view('decaissements.etat_bons_financement', [
            'bons' => $this->bonsEtatFinancement($request, $type),
            'type' => $type,
            'title' => $type === 'realises' ? 'Bons valides realises' : 'Bons valides non finances',
            'annees' => annee_academique::orderByDesc('id')->get(),
            'entites' => entite::orderBy('nom_entite')->get(),
        ]);
    }

    private function etatBonsFinancementPdf(Request $request, string $type)
    {
        $pdf = PDF::loadView('decaissements.etat_bons_financement_pdf', [
            'bons' => $this->bonsEtatFinancement($request, $type),
            'type' => $type,
            'title' => $type === 'realises' ? 'Bons valides realises' : 'Bons valides non finances',
        ])->setPaper('a4', 'landscape');

        return $pdf->download(($type === 'realises' ? 'bons_realises' : 'bons_non_finances').'.pdf');
    }

    private function bonsEtatFinancement(Request $request, string $type)
    {
        $bons = bon_commandeok::with([
            'personnels',
            'user',
            'entites',
            'decaissements',
            'element_bon_commandes',
            'budget',
            'annee_academique',
        ])
            ->where('statuts', 1)
            ->when($request->date_debut, fn($q) => $q->whereDate('date_validation', '>=', $request->date_debut))
            ->when($request->date_fin, fn($q) => $q->whereDate('date_validation', '<=', $request->date_fin))
            ->when($request->id_entite, fn($q) => $q->where('id_entite', $request->id_entite))
            ->when($request->id_annee_academique, fn($q) => $q->where('id_annee_academique', $request->id_annee_academique))
            // Vue globale: aucun filtre sur id_user, tous les bons valides sont visibles.
            ->orderByDesc('date_validation')
            ->get()
            ->map(function ($bon) {
                $bon->total_decaisse = $bon->decaissements->sum('montant');
                $bon->reste_financement = max($bon->montant_total - $bon->total_decaisse, 0);

                return $bon;
            });

        return $type === 'realises'
            ? $bons->filter(fn($bon) => $bon->total_decaisse >= $bon->montant_total)->values()
            : $bons->filter(fn($bon) => $bon->reste_financement > 0)->values();
    }

    public function exportPdf()
    {
        $bons = bon_commandeok::with('decaissements')->get();

        $pdf = PDF::loadView('decaissements.pdf', compact('bons'));

        return $pdf->download('decaissements.pdf');
    }
}
