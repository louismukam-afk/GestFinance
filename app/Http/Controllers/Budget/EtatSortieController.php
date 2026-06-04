<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Models\annee_academique;
use App\Models\banque;
use App\Models\BanqueUser;
use App\Models\Budget;
use App\Models\CaisseUser;
use App\Models\donnee_budgetaire_entree;
use App\Models\donnee_budgetaire_sortie;
use App\Models\donnee_ligne_budgetaire_entree;
use App\Models\entite;
use App\Models\facture_etudiant;
use Illuminate\Http\Request;
use App\Models\bon_commandeok;
use App\Models\caisse;
use App\Models\decaissement;
use App\Models\donnee_ligne_budgetaire_sortie;
use App\Models\entree_speciale;
use App\Models\entree_speciale_echeance;
use App\Models\Transfert_caisse;
use App\Models\User;
use App\Models\personnel;
use App\Models\reglement_etudiant;
use App\Models\retour_caisse;


use App\Exports\ViewExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class EtatSortieController extends Controller
{
    // =========================
    // 🏠 INDEX
    // =========================
    public function index()
    {
        return view('Admin.Etats.sorties.index');
    }

    // =========================
    // 📊 PILOTAGE
    // =========================
    public function pilotage(Request $request)
    {
        $query = decaissement::with(['bon','caisses','banques','user','personnels']);
        $caisses = caisse::orderBy('nom_caisse')->get();
        $banques = banque::orderBy('nom_banque')->get();

        if ($request->date_debut) {
            $query->whereDate('date_depense','>=',$request->date_debut);
        }

        if ($request->date_fin) {
            $query->whereDate('date_depense','<=',$request->date_fin);
        }

        if ($request->filled('id_caisse')) {
            $query->where('id_caisse', $request->id_caisse);
        }

        if ($request->filled('id_banque')) {
            $query->where('id_banque', $request->id_banque);
        }

        $data = $query->get();

        return view('Admin.Etats.sorties.pilotage', compact('data', 'caisses', 'banques'));
    }

    // =========================
    // 📉 ATTERRISSAGE
    // =========================

    public function atterrissage(Request $request)
    {
        $dateDebut = $request->date_debut;
        $dateFin = $request->date_fin ?? now();
        $idBudget = $request->id_budget;

        // ==========================
        // 🔥 FILTRE DE BASE DECaissements
        // ==========================
        $queryDecaissement = decaissement::with([
            'bon.entites'
        ])
            ->when($request->date_debut, fn($q) =>
        $q->whereDate('date_depense','>=',$request->date_debut)
    )
    ->whereDate('date_depense','<=',$dateFin);

    if ($request->id_annee_academique) {
        $queryDecaissement->where('id_annee_academique', $request->id_annee_academique);
    }

    if ($idBudget) {
        $queryDecaissement->where('id_budget', $idBudget);
    }

    if ($request->id_entite) {
        $queryDecaissement->whereHas('bon', function($q) use ($request){
            $q->where('id_entite', $request->id_entite);
        });
    }

    $decaissements = $queryDecaissement->get();

    $remboursementsDettes = entree_speciale_echeance::with('entree_speciale.budget')
        ->where('statut', 'payee')
        ->when($request->date_debut, fn($q) => $q->whereDate('date_paiement', '>=', $request->date_debut))
        ->whereDate('date_paiement', '<=', $dateFin)
        ->when($idBudget, fn($q) => $q->whereHas('entree_speciale', fn($d) => $d->where('id_budget', $idBudget)))
        ->when($request->id_annee_academique, fn($q) => $q->where('id_annee_academique_paiement', $request->id_annee_academique))
        ->get();

    // ==========================
    // 🔥 SOLDE GLOBAL CAISSE
    // ==========================
    $soldeGlobal = $this->buildDisponibiliteCaissesData($request)['totalApresTransfert'];

    // ==========================
    // 🔥 DONNÉES BUDGÉTAIRES
    // ==========================
    $donnees = donnee_ligne_budgetaire_sortie::with([
        'budgets',
        'ligne_budgetaire_sortie'
    ])
        ->when($idBudget, fn($q) => $q->where('id_budget', $idBudget))
        ->get();

    // ==========================
    // 🔥 CONSTRUCTION ETAT
    // ==========================
    $etat = $donnees->map(function ($d) use ($decaissements, $soldeGlobal) {

        // 🔥 Filtrer les décaissements liés à cette donnée
        $depenses = $decaissements
            ->where('id_donnee_ligne_budgetaire_sortie', $d->id);

        $depenseTotal = $depenses->sum('montant');

        // 🔥 ENTITÉ CORRIGÉE (via bon)
        $entite = $depenses
            ->pluck('bon.entites.nom_entite')
            ->filter()
            ->unique()
            ->implode(', ') ?: 'Non défini';

        return [
            'entite' => $entite,

            'budget' => $d->budgets->libelle_ligne_budget ?? '',
            'ligne'  => $d->ligne_budgetaire_sortie->libelle_ligne_budgetaire_sortie ?? '',
            'donnee' => $d->donnee_ligne_budgetaire_sortie,

            'prevu'   => $d->montant,
            'depense' => $depenseTotal,
            'reste'   => $d->montant - $depenseTotal,

            // 🔥 DISPONIBILITÉ CAISSE
            'solde'   => $soldeGlobal,
        ];
    });

    $entreesSpecialesUtilisees = entree_speciale::with(['budget', 'decaissements', 'echeances'])
        ->whereIn('type_entree', ['dette', 'don', 'apport'])
        ->where('statut', '!=', 'annule')
        ->when($idBudget, fn($q) => $q->where('id_budget', $idBudget))
        ->when($request->id_annee_academique, fn($q) => $q->where('id_annee_academique', $request->id_annee_academique))
        ->get()
        ->map(function ($entreeSpeciale) use ($dateDebut, $dateFin, $soldeGlobal) {
            $depenses = $entreeSpeciale->decaissements
                ->when($dateDebut, fn($c) => $c->where('date_depense', '>=', $dateDebut))
                ->where('date_depense', '<=', $dateFin);

            $depenseTotal = $depenses->sum('montant');
            $type = ucfirst($entreeSpeciale->type_entree);

            return [
                'entite' => $entreeSpeciale->nom_tiers ?: 'Entrees speciales',
                'budget' => optional($entreeSpeciale->budget)->libelle_ligne_budget ?? '',
                'ligne' => 'Utilisation entree speciale - ' . $type,
                'donnee' => $entreeSpeciale->libelle,
                'prevu' => $entreeSpeciale->montant,
                'depense' => $depenseTotal,
                'reste' => $entreeSpeciale->montant_net_encaisse - $depenseTotal,
                'solde' => $soldeGlobal,
            ];
        });

    $remboursementsRows = $remboursementsDettes
        ->groupBy('id_entree_speciale')
        ->map(function ($echeances) use ($soldeGlobal, $dateFin) {
            $dette = $echeances->first()->entree_speciale;
            $montantDette = (float) optional($dette)->montant;
            $montantRembourse = (float) $echeances->sum('montant_paye');
            $totalRembourseAuDateFin = entree_speciale_echeance::where('id_entree_speciale', optional($dette)->id)
                ->where('statut', 'payee')
                ->whereDate('date_paiement', '<=', $dateFin)
                ->sum('montant_paye');

            return [
                'entite' => optional($dette)->nom_tiers ?: 'Dettes remboursees',
                'budget' => optional(optional($dette)->budget)->libelle_ligne_budget ?? '',
                'ligne' => 'Remboursement dette',
                'donnee' => 'Remboursement dette - ' . (optional($dette)->libelle ?? 'Dette'),
                'prevu' => $montantDette,
                'depense' => $montantRembourse,
                'reste' => max($montantDette - (float) $totalRembourseAuDateFin, 0),
                'solde' => $soldeGlobal,
            ];
        })
        ->values();

    $etat = $etat->concat($entreesSpecialesUtilisees)->concat($remboursementsRows)->values();

    // ==========================
    // 🔥 GROUP BY ENTITE
    // ==========================
    $etatGrouped = $etat->groupBy('entite');

    // ==========================
    // 🔥 DONNÉES FILTRES (pour vue)
    // ==========================
    $entites = entite::all();
    $annees = annee_academique::all();
    $budgets = Budget::orderBy('libelle_ligne_budget')->get();

    return view('Admin.Etats.sorties.atterrissage', compact(
        'etat',
        'etatGrouped',
        'soldeGlobal',
        'entites',
        'annees',
        'budgets',
        'dateDebut',
        'dateFin'
    ));
}

    private function buildEtatCaisseData(Request $request, bool $currentUserOnly = false): array
    {
        $dateDebut = $request->date_debut;
        $dateFin = $request->date_fin ?? now()->format('Y-m-d');
        $idCaisse = $request->id_caisse;
        $idBanque = $request->id_banque;
        $idUser = $currentUserOnly ? auth()->id() : $request->id_user;
        $idBudget = $request->id_budget;
        $idEntite = $request->id_entite;
        $idAnnee = $request->id_annee_academique;
        $allowedCaisseIds = $idUser ? $this->caissesAffecteesIds((int) $idUser) : null;
        $allowedBanqueIds = $idUser ? $this->banquesAffecteesIds((int) $idUser) : null;

        $reglements = reglement_etudiant::with([
            'caisse',
            'user',
            'budget',
            'entite',
            'annee_academique',
            'ligne_budgetaire_entree',
            'element_ligne_budgetaire_entree',
            'donnee_ligne_budgetaire_entree',
            'etudiants',
        ])
            ->whereHas('facture_etudiants')
            ->when($dateDebut, fn($q) => $q->whereDate('date_reglement', '>=', $dateDebut))
            ->whereDate('date_reglement', '<=', $dateFin)
            ->where('id_caisse', '>', 0)
            ->when($allowedCaisseIds, fn($q) => $q->whereIn('id_caisse', $allowedCaisseIds))
            ->when($idCaisse, fn($q) => $q->where('id_caisse', $idCaisse))
            ->when($idBanque, fn($q) => $q->whereRaw('1 = 0'))
            ->when($idUser, fn($q) => $q->where('id_user', $idUser))
            ->when($idBudget, fn($q) => $q->where('id_budget', $idBudget))
            ->when($idEntite, fn($q) => $q->where('id_entite', $idEntite))
            ->when($idAnnee, fn($q) => $q->where('id_annee_academique', $idAnnee))
            ->get()
            ->map(fn($r) => [
                'caisse' => $r->caisse->nom_caisse ?? 'Non defini',
                'date' => $r->date_reglement,
                'type' => 'Entree',
                'operation' => 'Reglement etudiant',
                'numero' => $r->numero_reglement,
                'motif' => $r->motif_reglement ?? '',
                'tiers' => $r->etudiants->nom ?? '',
                'budget' => $r->budget->libelle_ligne_budget ?? '',
                'ligne' => $r->ligne_budgetaire_entree->libelle_ligne_budgetaire_entree ?? '',
                'element' => $r->element_ligne_budgetaire_entree->libelle_elements_ligne_budgetaire_entree ?? '',
                'donnee' => $r->donnee_ligne_budgetaire_entree->donnee_ligne_budgetaire_entree ?? '',
                'entite' => $r->entite->nom_entite ?? '',
                'annee' => $r->annee_academique->nom ?? '',
                'utilisateur' => $r->user->name ?? '',
                'caisse_id' => (int) $r->id_caisse,
                'compte_type' => 'caisse',
                'entree' => (float) $r->montant_reglement,
                'retour' => 0,
                'sortie' => 0,
            ]);

        $reglementsBanque = reglement_etudiant::with([
            'banque',
            'user',
            'budget',
            'entite',
            'annee_academique',
            'ligne_budgetaire_entree',
            'element_ligne_budgetaire_entree',
            'donnee_ligne_budgetaire_entree',
            'etudiants',
        ])
            ->whereHas('facture_etudiants')
            ->when($dateDebut, fn($q) => $q->whereDate('date_reglement', '>=', $dateDebut))
            ->whereDate('date_reglement', '<=', $dateFin)
            ->where('id_banque', '>', 0)
            ->when($allowedBanqueIds, fn($q) => $q->whereIn('id_banque', $allowedBanqueIds))
            ->when($idBanque, fn($q) => $q->where('id_banque', $idBanque))
            ->when($idCaisse, fn($q) => $q->whereRaw('1 = 0'))
            ->when($idUser, fn($q) => $q->where('id_user', $idUser))
            ->when($idBudget, fn($q) => $q->where('id_budget', $idBudget))
            ->when($idEntite, fn($q) => $q->where('id_entite', $idEntite))
            ->when($idAnnee, fn($q) => $q->where('id_annee_academique', $idAnnee))
            ->get()
            ->map(fn($r) => [
                'caisse' => 'Banque - ' . ($r->banque->nom_banque ?? 'Non definie'),
                'date' => $r->date_reglement,
                'type' => 'Entree',
                'operation' => 'Reglement etudiant banque',
                'numero' => $r->numero_reglement,
                'motif' => $r->motif_reglement ?? '',
                'tiers' => $r->etudiants->nom ?? '',
                'budget' => $r->budget->libelle_ligne_budget ?? '',
                'ligne' => $r->ligne_budgetaire_entree->libelle_ligne_budgetaire_entree ?? '',
                'element' => $r->element_ligne_budgetaire_entree->libelle_elements_ligne_budgetaire_entree ?? '',
                'donnee' => $r->donnee_ligne_budgetaire_entree->donnee_ligne_budgetaire_entree ?? '',
                'entite' => $r->entite->nom_entite ?? '',
                'annee' => $r->annee_academique->nom ?? '',
                'utilisateur' => $r->user->name ?? '',
                'caisse_id' => (int) $r->id_banque,
                'compte_type' => 'banque',
                'entree' => (float) $r->montant_reglement,
                'retour' => 0,
                'sortie' => 0,
            ]);

        $entreesSpeciales = entree_speciale::with(['caisse', 'budget', 'annee_academique', 'user', 'echeances'])
            ->where('statut', '!=', 'annule')
            ->when($dateDebut, fn($q) => $q->whereDate('date_entree', '>=', $dateDebut))
            ->whereDate('date_entree', '<=', $dateFin)
            ->where('id_caisse', '>', 0)
            ->when($allowedCaisseIds, fn($q) => $q->whereIn('id_caisse', $allowedCaisseIds))
            ->when($idCaisse, fn($q) => $q->where('id_caisse', $idCaisse))
            ->when($idBanque, fn($q) => $q->whereRaw('1 = 0'))
            ->when($idUser, fn($q) => $q->where('id_user', $idUser))
            ->when($idBudget, fn($q) => $q->where('id_budget', $idBudget))
            ->when($idAnnee, fn($q) => $q->where('id_annee_academique', $idAnnee))
            ->get()
            ->map(fn($e) => [
                'caisse' => $e->caisse->nom_caisse ?? 'Non defini',
                'date' => $e->date_entree,
                'type' => 'Entree',
                'operation' => 'Entree speciale - ' . ucfirst($e->type_entree),
                'numero' => $e->code_entree,
                'motif' => $e->libelle,
                'budget' => $e->budget->libelle_ligne_budget ?? '',
                'ligne' => 'Hors facture',
                'element' => '',
                'donnee' => $e->nom_tiers,
                'entite' => '',
                'annee' => $e->annee_academique->nom ?? '',
                'utilisateur' => $e->user->name ?? '',
                'tiers' => $e->nom_tiers ?? '',
                'caisse_id' => (int) $e->id_caisse,
                'compte_type' => 'caisse',
                'entree' => (float) $e->montant,
                'retour' => 0,
                'sortie' => 0,
            ]);

        $entreesSpecialesBanque = entree_speciale::with(['banque', 'budget', 'annee_academique', 'user', 'echeances'])
            ->where('statut', '!=', 'annule')
            ->when($dateDebut, fn($q) => $q->whereDate('date_entree', '>=', $dateDebut))
            ->whereDate('date_entree', '<=', $dateFin)
            ->where('id_banque', '>', 0)
            ->when($allowedBanqueIds, fn($q) => $q->whereIn('id_banque', $allowedBanqueIds))
            ->when($idBanque, fn($q) => $q->where('id_banque', $idBanque))
            ->when($idCaisse, fn($q) => $q->whereRaw('1 = 0'))
            ->when($idUser, fn($q) => $q->where('id_user', $idUser))
            ->when($idBudget, fn($q) => $q->where('id_budget', $idBudget))
            ->when($idAnnee, fn($q) => $q->where('id_annee_academique', $idAnnee))
            ->get()
            ->map(fn($e) => [
                'caisse' => 'Banque - ' . ($e->banque->nom_banque ?? 'Non definie'),
                'date' => $e->date_entree,
                'type' => 'Entree',
                'operation' => 'Entree speciale banque - ' . ucfirst($e->type_entree),
                'numero' => $e->code_entree,
                'motif' => $e->libelle,
                'budget' => $e->budget->libelle_ligne_budget ?? '',
                'ligne' => 'Hors facture',
                'element' => '',
                'donnee' => $e->nom_tiers,
                'entite' => '',
                'annee' => $e->annee_academique->nom ?? '',
                'utilisateur' => $e->user->name ?? '',
                'tiers' => $e->nom_tiers ?? '',
                'caisse_id' => (int) $e->id_banque,
                'compte_type' => 'banque',
                'entree' => (float) $e->montant,
                'retour' => 0,
                'sortie' => 0,
            ]);

        $decaissements = decaissement::with([
            'caisses',
            'user',
            'budgets',
            'bon.entites',
            'annee_academiques',
            'ligne_budgetaire_sorties',
            'elements_ligne_budgetaire_sorties',
            'donnee_ligne_budgetaire_sorties',
        ])
            ->when($dateDebut, fn($q) => $q->whereDate('date_depense', '>=', $dateDebut))
            ->whereDate('date_depense', '<=', $dateFin)
            ->where('id_caisse', '>', 0)
            ->when($allowedCaisseIds, fn($q) => $q->whereIn('id_caisse', $allowedCaisseIds))
            ->when($idCaisse, fn($q) => $q->where('id_caisse', $idCaisse))
            ->when($idBanque, fn($q) => $q->whereRaw('1 = 0'))
            ->when($idUser, fn($q) => $q->where('id_user', $idUser))
            ->when($idBudget, fn($q) => $q->where('id_budget', $idBudget))
            ->when($idEntite, fn($q) => $q->whereHas('bon', fn($b) => $b->where('id_entite', $idEntite)))
            ->when($idAnnee, fn($q) => $q->where('id_annee_academique', $idAnnee))
            ->get()
            ->map(fn($d) => [
                'caisse' => $d->caisses->nom_caisse ?? 'Non defini',
                'date' => $d->date_depense,
                'type' => 'Sortie',
                'operation' => 'Decaissement',
                'numero' => $d->numero_depense,
                'motif' => $d->motif ?? '',
                'budget' => $d->budgets->libelle_ligne_budget ?? '',
                'ligne' => $d->ligne_budgetaire_sorties->libelle_ligne_budgetaire_sortie ?? '',
                'element' => $d->elements_ligne_budgetaire_sorties->libelle_elements_ligne_budgetaire_sortie ?? '',
                'donnee' => $d->donnee_ligne_budgetaire_sorties->donnee_ligne_budgetaire_sortie ?? '',
                'entite' => optional(optional($d->bon)->entites)->nom_entite ?? '',
                'annee' => $d->annee_academiques->nom ?? '',
                'utilisateur' => $d->user->name ?? '',
                'tiers' => optional($d->bon)->nom_bon_commande ?? '',
                'caisse_id' => (int) $d->id_caisse,
                'compte_type' => 'caisse',
                'entree' => 0,
                'retour' => 0,
                'sortie' => (float) $d->montant,
            ]);

        $decaissementsBanque = decaissement::with([
            'banques',
            'user',
            'budgets',
            'bon.entites',
            'annee_academiques',
            'ligne_budgetaire_sorties',
            'elements_ligne_budgetaire_sorties',
            'donnee_ligne_budgetaire_sorties',
        ])
            ->when($dateDebut, fn($q) => $q->whereDate('date_depense', '>=', $dateDebut))
            ->whereDate('date_depense', '<=', $dateFin)
            ->where('id_banque', '>', 0)
            ->when($allowedBanqueIds, fn($q) => $q->whereIn('id_banque', $allowedBanqueIds))
            ->when($idBanque, fn($q) => $q->where('id_banque', $idBanque))
            ->when($idCaisse, fn($q) => $q->whereRaw('1 = 0'))
            ->when($idUser, fn($q) => $q->where('id_user', $idUser))
            ->when($idBudget, fn($q) => $q->where('id_budget', $idBudget))
            ->when($idEntite, fn($q) => $q->whereHas('bon', fn($b) => $b->where('id_entite', $idEntite)))
            ->when($idAnnee, fn($q) => $q->where('id_annee_academique', $idAnnee))
            ->get()
            ->map(fn($d) => [
                'caisse' => 'Banque - ' . ($d->banques->nom_banque ?? 'Non definie'),
                'date' => $d->date_depense,
                'type' => 'Sortie',
                'operation' => 'Decaissement banque',
                'numero' => $d->numero_depense,
                'motif' => $d->motif ?? '',
                'budget' => $d->budgets->libelle_ligne_budget ?? '',
                'ligne' => $d->ligne_budgetaire_sorties->libelle_ligne_budgetaire_sortie ?? '',
                'element' => $d->elements_ligne_budgetaire_sorties->libelle_elements_ligne_budgetaire_sortie ?? '',
                'donnee' => $d->donnee_ligne_budgetaire_sorties->donnee_ligne_budgetaire_sortie ?? '',
                'entite' => optional(optional($d->bon)->entites)->nom_entite ?? '',
                'annee' => $d->annee_academiques->nom ?? '',
                'utilisateur' => $d->user->name ?? '',
                'tiers' => optional($d->bon)->nom_bon_commande ?? '',
                'caisse_id' => (int) $d->id_banque,
                'compte_type' => 'banque',
                'entree' => 0,
                'retour' => 0,
                'sortie' => (float) $d->montant,
            ]);

        $remboursementsDettes = entree_speciale_echeance::with([
            'caisse_paiement',
            'user_paiement',
            'annee_paiement',
            'entree_speciale.budget',
        ])
            ->where('statut', 'payee')
            ->when($dateDebut, fn($q) => $q->whereDate('date_paiement', '>=', $dateDebut))
            ->whereDate('date_paiement', '<=', $dateFin)
            ->where('id_caisse_paiement', '>', 0)
            ->when($allowedCaisseIds, fn($q) => $q->whereIn('id_caisse_paiement', $allowedCaisseIds))
            ->when($idCaisse, fn($q) => $q->where('id_caisse_paiement', $idCaisse))
            ->when($idBanque, fn($q) => $q->whereRaw('1 = 0'))
            ->when($idUser, fn($q) => $q->where('id_user_paiement', $idUser))
            ->when($idBudget, fn($q) => $q->whereHas('entree_speciale', fn($e) => $e->where('id_budget', $idBudget)))
            ->when($idAnnee, fn($q) => $q->where('id_annee_academique_paiement', $idAnnee))
            ->get()
            ->map(fn($r) => [
                'caisse' => $r->caisse_paiement->nom_caisse ?? 'Non defini',
                'date' => $r->date_paiement,
                'type' => 'Sortie',
                'operation' => 'Remboursement dette',
                'numero' => 'REM-DET-' . $r->id,
                'motif' => optional($r->entree_speciale)->libelle ?? $r->nom_echeance,
                'budget' => optional(optional($r->entree_speciale)->budget)->libelle_ligne_budget ?? '',
                'ligne' => 'Dette',
                'element' => '',
                'donnee' => $r->nom_echeance,
                'entite' => '',
                'annee' => $r->annee_paiement->nom ?? '',
                'utilisateur' => $r->user_paiement->name ?? '',
                'tiers' => optional($r->entree_speciale)->nom_tiers ?? '',
                'caisse_id' => (int) $r->id_caisse_paiement,
                'compte_type' => 'caisse',
                'entree' => 0,
                'retour' => 0,
                'sortie' => (float) $r->montant_paye,
            ]);

        $transfertsEntrants = Transfert_caisse::with(['caisseArrivee', 'caisseDepart', 'banqueDepart', 'banqueArrivee', 'user'])
            ->where('type_transfert', '!=', 2)
            ->when($dateDebut, fn($q) => $q->whereDate('date_transfert', '>=', $dateDebut))
            ->whereDate('date_transfert', '<=', $dateFin)
            ->when($allowedCaisseIds, fn($q) => $q->whereIn('id_caisse_arrivee', $allowedCaisseIds))
            ->when($idCaisse, fn($q) => $q->where('id_caisse_arrivee', $idCaisse))
            ->when($idBanque, fn($q) => $q->whereRaw('1 = 0'))
            ->when($idUser && ! $currentUserOnly, fn($q) => $q->where('id_user', $idUser))
            ->get()
            ->map(fn($t) => [
                'caisse' => $t->caisseArrivee->nom_caisse ?? 'Non defini',
                'date' => $t->date_transfert,
                'type' => 'Entree',
                'operation' => 'Transfert entrant',
                'numero' => $t->code_transfert,
                'motif' => $t->observation ?? '',
                'budget' => '',
                'ligne' => '',
                'element' => '',
                'donnee' => '',
                'entite' => '',
                'annee' => '',
                'utilisateur' => $t->user->name ?? '',
                'tiers' => $t->caisseDepart->nom_caisse ?? ($t->banqueDepart->nom_banque ?? ''),
                'caisse_id' => (int) $t->id_caisse_arrivee,
                'compte_type' => 'caisse',
                'entree' => (float) $t->montant_transfert,
                'retour' => 0,
                'sortie' => 0,
            ]);

        $transfertsSortants = Transfert_caisse::with(['caisseArrivee', 'caisseDepart', 'banqueDepart', 'banqueArrivee', 'user'])
            ->where('type_transfert', '!=', 2)
            ->when($dateDebut, fn($q) => $q->whereDate('date_transfert', '>=', $dateDebut))
            ->whereDate('date_transfert', '<=', $dateFin)
            ->when($allowedCaisseIds, fn($q) => $q->whereIn('id_caisse_depart', $allowedCaisseIds))
            ->when($idCaisse, fn($q) => $q->where('id_caisse_depart', $idCaisse))
            ->when($idBanque, fn($q) => $q->whereRaw('1 = 0'))
            ->when($idUser && ! $currentUserOnly, fn($q) => $q->where('id_user', $idUser))
            ->get()
            ->map(fn($t) => [
                'caisse' => $t->caisseDepart->nom_caisse ?? 'Non defini',
                'date' => $t->date_transfert,
                'type' => 'Sortie',
                'operation' => 'Transfert sortant',
                'numero' => $t->code_transfert,
                'motif' => $t->observation ?? '',
                'budget' => '',
                'ligne' => '',
                'element' => '',
                'donnee' => '',
                'entite' => '',
                'annee' => '',
                'utilisateur' => $t->user->name ?? '',
                'tiers' => $t->caisseArrivee->nom_caisse ?? ($t->banqueArrivee->nom_banque ?? ''),
                'caisse_id' => (int) $t->id_caisse_depart,
                'compte_type' => 'caisse',
                'entree' => 0,
                'retour' => 0,
                'sortie' => (float) $t->montant_transfert,
            ]);

        $transfertsBanquesEntrants = Transfert_caisse::with(['caisseDepart', 'banqueArrivee', 'user'])
            ->where('type_transfert', '!=', 2)
            ->where('id_banque_arrivee', '>', 0)
            ->when($dateDebut, fn($q) => $q->whereDate('date_transfert', '>=', $dateDebut))
            ->whereDate('date_transfert', '<=', $dateFin)
            ->when($allowedBanqueIds, fn($q) => $q->whereIn('id_banque_arrivee', $allowedBanqueIds))
            ->when($idBanque, fn($q) => $q->where('id_banque_arrivee', $idBanque))
            ->when($idCaisse, fn($q) => $q->whereRaw('1 = 0'))
            ->when($idUser && ! $currentUserOnly, fn($q) => $q->where('id_user', $idUser))
            ->get()
            ->map(fn($t) => [
                'caisse' => 'Banque - ' . ($t->banqueArrivee->nom_banque ?? 'Non definie'),
                'date' => $t->date_transfert,
                'type' => 'Entree',
                'operation' => 'Transfert entrant banque',
                'numero' => $t->code_transfert,
                'motif' => $t->observation ?? '',
                'budget' => '',
                'ligne' => '',
                'element' => '',
                'donnee' => '',
                'entite' => '',
                'annee' => '',
                'utilisateur' => $t->user->name ?? '',
                'tiers' => $t->caisseDepart->nom_caisse ?? '',
                'caisse_id' => (int) $t->id_banque_arrivee,
                'compte_type' => 'banque',
                'entree' => (float) $t->montant_transfert,
                'retour' => 0,
                'sortie' => 0,
            ]);

        $transfertsBanquesSortants = Transfert_caisse::with(['caisseArrivee', 'banqueDepart', 'user'])
            ->where('type_transfert', '!=', 2)
            ->where('id_banque_depart', '>', 0)
            ->when($dateDebut, fn($q) => $q->whereDate('date_transfert', '>=', $dateDebut))
            ->whereDate('date_transfert', '<=', $dateFin)
            ->when($allowedBanqueIds, fn($q) => $q->whereIn('id_banque_depart', $allowedBanqueIds))
            ->when($idBanque, fn($q) => $q->where('id_banque_depart', $idBanque))
            ->when($idCaisse, fn($q) => $q->whereRaw('1 = 0'))
            ->when($idUser && ! $currentUserOnly, fn($q) => $q->where('id_user', $idUser))
            ->get()
            ->map(fn($t) => [
                'caisse' => 'Banque - ' . ($t->banqueDepart->nom_banque ?? 'Non definie'),
                'date' => $t->date_transfert,
                'type' => 'Sortie',
                'operation' => 'Transfert sortant banque',
                'numero' => $t->code_transfert,
                'motif' => $t->observation ?? '',
                'budget' => '',
                'ligne' => '',
                'element' => '',
                'donnee' => '',
                'entite' => '',
                'annee' => '',
                'utilisateur' => $t->user->name ?? '',
                'tiers' => $t->caisseArrivee->nom_caisse ?? '',
                'caisse_id' => (int) $t->id_banque_depart,
                'compte_type' => 'banque',
                'entree' => 0,
                'retour' => 0,
                'sortie' => (float) $t->montant_transfert,
            ]);

        $retoursCaisse = retour_caisse::with([
            'caisse',
            'user',
            'budget',
            'ligne_budgetaire_sortie',
            'element_ligne_budgetaire_sortie',
            'donnee_ligne_budgetaire_sortie',
            'annee_academique',
        ])
            ->when($dateDebut, fn($q) => $q->whereDate('date_retour', '>=', $dateDebut))
            ->whereDate('date_retour', '<=', $dateFin)
            ->where('id_caisse', '>', 0)
            ->when($allowedCaisseIds, fn($q) => $q->whereIn('id_caisse', $allowedCaisseIds))
            ->when($idCaisse, fn($q) => $q->where('id_caisse', $idCaisse))
            ->when($idBanque, fn($q) => $q->whereRaw('1 = 0'))
            ->when($idUser, fn($q) => $q->where('id_user', $idUser))
            ->when($idBudget, fn($q) => $q->where('id_budget', $idBudget))
            ->when($idEntite, fn($q) => $q->whereHas('bon', fn($b) => $b->where('id_entite', $idEntite)))
            ->when($idAnnee, fn($q) => $q->where('id_annee_academique', $idAnnee))
            ->get()
            ->map(fn($r) => [
                'caisse' => $r->caisse->nom_caisse ?? 'Non defini',
                'date' => $r->date_retour,
                'type' => 'Entree',
                'operation' => 'Retour en caisse',
                'numero' => $r->numero_retour,
                'motif' => $r->motif ?? '',
                'budget' => $r->budget->libelle_ligne_budget ?? '',
                'ligne' => $r->ligne_budgetaire_sortie->libelle_ligne_budgetaire_sortie ?? '',
                'element' => $r->element_ligne_budgetaire_sortie->libelle_elements_ligne_budgetaire_sortie ?? '',
                'donnee' => $r->donnee_ligne_budgetaire_sortie->donnee_ligne_budgetaire_sortie ?? '',
                'entite' => optional(optional($r->bon)->entites)->nom_entite ?? '',
                'annee' => $r->annee_academique->nom ?? '',
                'utilisateur' => $r->user->name ?? '',
                'tiers' => '',
                'caisse_id' => (int) $r->id_caisse,
                'compte_type' => 'caisse',
                'entree' => 0,
                'retour' => (float) $r->montant,
                'sortie' => 0,
            ]);

        $operations = $reglements
            ->concat($reglementsBanque)
            ->concat($entreesSpeciales)
            ->concat($entreesSpecialesBanque)
            ->concat($decaissements)
            ->concat($decaissementsBanque)
            ->concat($remboursementsDettes)
            ->concat($transfertsEntrants)
            ->concat($transfertsSortants)
            ->concat($transfertsBanquesEntrants)
            ->concat($transfertsBanquesSortants)
            ->concat($retoursCaisse)
            ->sortBy('date')
            ->values();

        $caissesDisponibles = $allowedCaisseIds
            ? caisse::whereIn('id', $allowedCaisseIds)->orderBy('nom_caisse')->get()
            : caisse::orderBy('nom_caisse')->get();
        $banquesDisponibles = $allowedBanqueIds
            ? banque::whereIn('id', $allowedBanqueIds)->orderBy('nom_banque')->get()
            : banque::orderBy('nom_banque')->get();

        $caisseSummaries = $operations
            ->groupBy('caisse')
            ->map(function ($lignes, $nomCaisse) use ($dateDebut, $dateFin) {
                $id = (int) ($lignes->first()['caisse_id'] ?? 0);
                $compteType = $lignes->first()['compte_type'] ?? 'caisse';
                $soldeInitial = $id && $dateDebut
                    ? ($compteType === 'banque'
                        ? $this->soldeBanqueAlaDate($id, \Carbon\Carbon::parse($dateDebut)->subDay()->toDateString())
                        : $this->soldeCaisseAlaDate($id, \Carbon\Carbon::parse($dateDebut)->subDay()->toDateString()))
                    : 0;
                $entrees = (float) $lignes->sum('entree') + (float) $lignes->sum('retour');
                $sorties = (float) $lignes->sum('sortie');

                return [
                    'caisse' => $nomCaisse,
                    'solde_initial' => $soldeInitial,
                    'entrees_periode' => $entrees,
                    'sorties_periode' => $sorties,
                    'solde_final' => $soldeInitial + $entrees - $sorties,
                ];
            })
            ->values();

        return [
            'operations' => $operations,
            'operationsGrouped' => $operations->groupBy('caisse'),
            'caisseSummaries' => $caisseSummaries,
            'totalEntrees' => $operations->sum('entree'),
            'totalRetours' => $operations->sum('retour'),
            'totalSorties' => $operations->sum('sortie'),
            'solde' => $operations->sum('entree') + $operations->sum('retour') - $operations->sum('sortie'),
            'caisses' => $caissesDisponibles,
            'banques' => $banquesDisponibles,
            'users' => User::orderBy('name')->get(),
            'budgets' => Budget::orderBy('libelle_ligne_budget')->get(),
            'entites' => entite::orderBy('nom_entite')->get(),
            'annees' => annee_academique::orderBy('nom', 'desc')->get(),
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin,
            'currentUserOnly' => $currentUserOnly,
            'userName' => $currentUserOnly ? optional(auth()->user())->name : null,
        ];
    }

    public function etatCaisse(Request $request)
    {
        return view('Admin.Etats.sorties.etat_caisse', $this->buildEtatCaisseData($request));
    }

    private function caissesAffecteesIds(int $userId)
    {
        return CaisseUser::where('id_user', $userId)
            ->where('actif', true)
            ->where(function ($q) {
                $q->whereNull('date_debut')->orWhereDate('date_debut', '<=', now()->toDateString());
            })
            ->where(function ($q) {
                $q->whereNull('date_fin')->orWhereDate('date_fin', '>=', now()->toDateString());
            })
            ->pluck('id_caisse')
            ->unique()
            ->values();
    }

    private function banquesAffecteesIds(int $userId)
    {
        return BanqueUser::where('id_user', $userId)
            ->where('actif', true)
            ->where(function ($q) {
                $q->whereNull('date_debut')->orWhereDate('date_debut', '<=', now()->toDateString());
            })
            ->where(function ($q) {
                $q->whereNull('date_fin')->orWhereDate('date_fin', '>=', now()->toDateString());
            })
            ->pluck('id_banque')
            ->unique()
            ->values();
    }

    private function soldeCaisseAlaDate(int $idCaisse, string $dateFin): float
    {
        $reglements = reglement_etudiant::where('id_caisse', $idCaisse)
            ->whereHas('facture_etudiants')
            ->whereDate('date_reglement', '<=', $dateFin)
            ->sum('montant_reglement');
        $entreesSpeciales = entree_speciale::where('id_caisse', $idCaisse)
            ->where('statut', '!=', 'annule')
            ->whereDate('date_entree', '<=', $dateFin)
            ->sum('montant');
        $retours = retour_caisse::where('id_caisse', $idCaisse)
            ->whereDate('date_retour', '<=', $dateFin)
            ->sum('montant');
        $transfertsEntrants = Transfert_caisse::where('id_caisse_arrivee', $idCaisse)
            ->where('type_transfert', '!=', 2)
            ->whereDate('date_transfert', '<=', $dateFin)
            ->sum('montant_transfert');
        $transfertsSortants = Transfert_caisse::where('id_caisse_depart', $idCaisse)
            ->where('type_transfert', '!=', 2)
            ->whereDate('date_transfert', '<=', $dateFin)
            ->sum('montant_transfert');
        $decaissements = decaissement::where('id_caisse', $idCaisse)
            ->whereDate('date_depense', '<=', $dateFin)
            ->sum('montant');
        $remboursementsDettes = entree_speciale_echeance::where('id_caisse_paiement', $idCaisse)
            ->where('statut', 'payee')
            ->whereDate('date_paiement', '<=', $dateFin)
            ->sum('montant_paye');

        return (float) ($reglements + $entreesSpeciales + $retours + $transfertsEntrants - $transfertsSortants - $decaissements - $remboursementsDettes);
    }

    private function soldeBanqueAlaDate(int $idBanque, string $dateFin): float
    {
        $reglements = reglement_etudiant::where('id_banque', $idBanque)
            ->whereHas('facture_etudiants')
            ->whereDate('date_reglement', '<=', $dateFin)
            ->sum('montant_reglement');
        $entreesSpeciales = entree_speciale::where('id_banque', $idBanque)
            ->where('statut', '!=', 'annule')
            ->whereDate('date_entree', '<=', $dateFin)
            ->sum('montant');
        $decaissements = decaissement::where('id_banque', $idBanque)
            ->whereDate('date_depense', '<=', $dateFin)
            ->sum('montant');
        $transfertsEntrants = Transfert_caisse::where('id_banque_arrivee', $idBanque)
            ->where('type_transfert', '!=', 2)
            ->whereDate('date_transfert', '<=', $dateFin)
            ->sum('montant_transfert');
        $transfertsSortants = Transfert_caisse::where('id_banque_depart', $idBanque)
            ->where('type_transfert', '!=', 2)
            ->whereDate('date_transfert', '<=', $dateFin)
            ->sum('montant_transfert');

        return (float) ($reglements + $entreesSpeciales + $transfertsEntrants - $transfertsSortants - $decaissements);
    }

    public function monEtatCaisse(Request $request)
    {
        return view('Admin.Etats.sorties.etat_caisse', $this->buildEtatCaisseData($request, true));
    }

    public function exportEtatCaissePdf(Request $request)
    {
        $data = $this->buildEtatCaisseData($request);

        $pdf = Pdf::loadView('Admin.Etats.sorties.etat_caisse_pdf', $data)
            ->setPaper('a3', 'landscape');

        return $pdf->download('etat_caisses.pdf');
    }

    public function exportEtatCaisseExcel(Request $request)
    {
        return Excel::download(
            new ViewExport('Admin.Etats.sorties.etat_caisse_pdf', $this->buildEtatCaisseData($request)),
            'etat_caisses.xlsx'
        );
    }

    public function exportMonEtatCaissePdf(Request $request)
    {
        $data = $this->buildEtatCaisseData($request, true);

        $pdf = Pdf::loadView('Admin.Etats.sorties.etat_caisse_pdf', $data)
            ->setPaper('a3', 'landscape');

        return $pdf->download('mon_etat_caisse.pdf');
    }

    public function exportMonEtatCaisseExcel(Request $request)
    {
        return Excel::download(
            new ViewExport('Admin.Etats.sorties.etat_caisse_pdf', $this->buildEtatCaisseData($request, true)),
            'mon_etat_caisse.xlsx'
        );
    }

    private function buildDisponibiliteCaissesData(Request $request): array
    {
        $dateFin = $request->date_fin ?? now()->format('Y-m-d');

        $caisses = caisse::orderBy('nom_caisse')->get()->map(function ($caisse) use ($dateFin) {
            $entreesReglements = reglement_etudiant::where('id_caisse', $caisse->id)
                ->whereHas('facture_etudiants')
                ->whereDate('date_reglement', '<=', $dateFin)
                ->sum('montant_reglement');

            $entreesRetours = retour_caisse::where('id_caisse', $caisse->id)
                ->whereDate('date_retour', '<=', $dateFin)
                ->sum('montant');

            $entreesSpeciales = entree_speciale::with('echeances')
                ->where('id_caisse', $caisse->id)
                ->where('statut', '!=', 'annule')
                ->whereDate('date_entree', '<=', $dateFin)
                ->sum('montant');

            $sortiesDecaissements = decaissement::where('id_caisse', $caisse->id)
                ->whereDate('date_depense', '<=', $dateFin)
                ->sum('montant');

            $remboursementsDettes = entree_speciale_echeance::where('id_caisse_paiement', $caisse->id)
                ->where('statut', 'payee')
                ->whereDate('date_paiement', '<=', $dateFin)
                ->sum('montant_paye');

            $transfertsEntrants = Transfert_caisse::where('id_caisse_arrivee', $caisse->id)
                ->where('type_transfert', '!=', 2)
                ->whereDate('date_transfert', '<=', $dateFin)
                ->sum('montant_transfert');

            $transfertsSortants = Transfert_caisse::where('id_caisse_depart', $caisse->id)
                ->where('type_transfert', '!=', 2)
                ->whereDate('date_transfert', '<=', $dateFin)
                ->sum('montant_transfert');

            $soldeAvantTransfert = $entreesReglements + $entreesRetours + $entreesSpeciales - $sortiesDecaissements - $remboursementsDettes;
            $soldeApresTransfert = $soldeAvantTransfert + $transfertsEntrants - $transfertsSortants;

            return [
                'caisse' => $caisse,
                'entrees_reglements' => $entreesReglements,
                'entrees_retours' => $entreesRetours,
                'entrees_speciales' => $entreesSpeciales,
                'sorties_decaissements' => $sortiesDecaissements,
                'remboursements_dettes' => $remboursementsDettes,
                'transferts_entrants' => $transfertsEntrants,
                'transferts_sortants' => $transfertsSortants,
                'solde_avant_transfert' => $soldeAvantTransfert,
                'solde_apres_transfert' => $soldeApresTransfert,
            ];
        });

        $banques = banque::orderBy('nom_banque')->get()->map(function ($banque) use ($dateFin) {
            $entreesReglements = reglement_etudiant::where('id_banque', $banque->id)
                ->whereHas('facture_etudiants')
                ->whereDate('date_reglement', '<=', $dateFin)
                ->sum('montant_reglement');

            $entreesSpeciales = entree_speciale::with('echeances')
                ->where('id_banque', $banque->id)
                ->where('statut', '!=', 'annule')
                ->whereDate('date_entree', '<=', $dateFin)
                ->sum('montant');

            $sortiesDecaissements = decaissement::where('id_banque', $banque->id)
                ->whereDate('date_depense', '<=', $dateFin)
                ->sum('montant');

            $transfertsEntrants = Transfert_caisse::where('id_banque_arrivee', $banque->id)
                ->where('type_transfert', '!=', 2)
                ->whereDate('date_transfert', '<=', $dateFin)
                ->sum('montant_transfert');

            $transfertsSortants = Transfert_caisse::where('id_banque_depart', $banque->id)
                ->where('type_transfert', '!=', 2)
                ->whereDate('date_transfert', '<=', $dateFin)
                ->sum('montant_transfert');

            $soldeAvantTransfert = $entreesReglements + $entreesSpeciales - $sortiesDecaissements;
            $soldeApresTransfert = $soldeAvantTransfert + $transfertsEntrants - $transfertsSortants;

            return [
                'caisse' => $banque,
                'nom_compte' => $banque->nom_banque,
                'type_compte' => 'Banque',
                'entrees_reglements' => $entreesReglements,
                'entrees_retours' => 0,
                'entrees_speciales' => $entreesSpeciales,
                'sorties_decaissements' => $sortiesDecaissements,
                'remboursements_dettes' => 0,
                'transferts_entrants' => $transfertsEntrants,
                'transferts_sortants' => $transfertsSortants,
                'solde_avant_transfert' => $soldeAvantTransfert,
                'solde_apres_transfert' => $soldeApresTransfert,
            ];
        });

        $comptes = $caisses->map(function ($ligne) {
            $ligne['nom_compte'] = $ligne['caisse']->nom_caisse;
            $ligne['type_compte'] = 'Caisse ' . ($ligne['caisse']->type_caisse ?? '');
            return $ligne;
        })->concat($banques)->values();

        return [
            'caisses' => $comptes,
            'dateFin' => $dateFin,
            'totalAvantTransfert' => $comptes->sum('solde_avant_transfert'),
            'totalApresTransfert' => $comptes->sum('solde_apres_transfert'),
        ];
    }

    public function disponibiliteCaisses(Request $request)
    {
        return view('Admin.Etats.sorties.disponibilite_caisses', $this->buildDisponibiliteCaissesData($request));
    }

    public function exportDisponibiliteCaissesPdf(Request $request)
    {
        $pdf = Pdf::loadView(
            'Admin.Etats.sorties.disponibilite_caisses_pdf',
            $this->buildDisponibiliteCaissesData($request)
        )->setPaper('a3', 'landscape');

        return $pdf->download('disponibilite_caisses.pdf');
    }

    public function etatGlobaltableauvide(Request $request)
    {
        $dateDebut = $request->date_debut;
        $dateFin   = $request->date_fin ?? now();

        $idBudget  = $request->id_budget;
        $idAnnee   = $request->id_annee_academique;

        // =========================
        // 💰 DISPONIBILITÉ CAISSE
        // =========================
        $entree = Transfert_caisse::whereDate('date_transfert','<=',$dateFin)->sum('montant_transfert');
        $sortie = decaissement::whereDate('date_depense','<=',$dateFin)->sum('montant');

        $disponibilite = $entree - $sortie;

        // =========================
        // 🔵 ENTRÉES
        // =========================
        $entrees = donnee_budgetaire_entree::with([
            'ligne_budgetaire_entrees',
            'donnee_ligne_budgetaire_entrees.element_ligne_budgetaire_entrees',
            'donnee_ligne_budgetaire_entrees.facture_etudiants.reglement_etudiants',
            'donnee_ligne_budgetaire_entrees.facture_etudiants.entite'
        ])
            ->when($idBudget, fn($q)=>$q->where('id_budget',$idBudget))
    ->get()
        ->groupBy(fn($d)=>optional($d->budgets)->libelle_ligne_budget)
    ->map(function($budgetGroup) use ($dateDebut,$dateFin,$idAnnee){

        return $budgetGroup->groupBy(fn($d)=>
            optional($d->ligne_budgetaire_entrees)->libelle_ligne_budgetaire_entree
        )->map(function($ligneGroup) use ($dateDebut,$dateFin,$idAnnee){

            return $ligneGroup->map(function($donnee){

                return $donnee->donnee_ligne_budgetaire_entrees
                    ->groupBy(fn($dl)=>
                        optional($dl->element_ligne_budgetaire_entrees)->libelle_elements_ligne_budgetaire_entree
                    )
                    ->map(function($elements){

                    $prevu = 0;
                    $encaisse = 0;

                    foreach($elements as $dl){

                        foreach($dl->facture_etudiants as $f){

                            $prevu += $f->montant_total_facture;

                            $encaisse += $f->reglement_etudiants->sum('montant_reglement');
                        }
                    }

                    return [
                        'element' => optional($elements->first()->element_ligne_budgetaire_entrees)->libelle_elements_ligne_budgetaire_entree,
                        'prevu'   => $prevu,
                        'realise' => $encaisse,
                        'reste'   => $prevu - $encaisse
                    ];
                });
            });
        });
    });

    // =========================
    // 🔴 SORTIES
    // =========================
    $sorties = donnee_budgetaire_sortie::with([
        'ligne_budgetaire_sortie',
        'donnee_ligne_budgetaire_sorties.element_ligne_budgetaire_sorties',
        'donnee_ligne_budgetaire_sorties.decaissements'
    ])
        ->when($idBudget, fn($q)=>$q->where('id_budget',$idBudget))
    ->get()
        ->groupBy(fn($d)=>optional($d->budgets)->libelle_ligne_budget)
    ->map(function($budgetGroup) use ($dateDebut,$dateFin,$idAnnee){

        return $budgetGroup->groupBy(fn($d)=>
            optional($d->ligne_budgetaire_sortie)->libelle_ligne_budgetaire_sortie
        )->map(function($ligneGroup) use ($dateDebut,$dateFin,$idAnnee){

            return $ligneGroup->map(function($donnee){

                return $donnee->donnee_ligne_budgetaire_sorties
                    ->groupBy(fn($dl)=>
                        optional($dl->element_ligne_budgetaire_sorties)->libelle_elements_ligne_budgetaire_sortie
                    )
                    ->map(function($elements){

                    $prevu = 0;
                    $depense = 0;

                    foreach($elements as $dl){

                        $prevu += $dl->montant;

                        $depense += $dl->decaissements->sum('montant');
                    }

                    return [
                        'element' => optional($elements->first()->element_ligne_budgetaire_sorties)->libelle_elements_ligne_budgetaire_sortie,
                        'prevu'   => $prevu,
                        'realise' => $depense,
                        'reste'   => $prevu - $depense
                    ];
                });
            });
        });
    });

    // =========================
    // 📊 DEFICIT
    // =========================
    $resteRecouvrer = collect($entrees)->flatten(4)->sum('reste');
    $resteSortie    = collect($sorties)->flatten(4)->sum('reste');

    $deficit = $disponibilite + $resteRecouvrer - $resteSortie;

    $budgets = Budget::all();
    $annees  = annee_academique::all();

    return view('Admin.Etats.sorties.global', compact(
        'entrees','sorties','disponibilite','deficit','budgets','annees'
    ));
}

    public function etatGlobal(Request $request)
    {
        $dateDebut = $request->date_debut ?? '2000-01-01';
        $dateFin   = $request->date_fin ?? now();

        $idAnnee  = $request->id_annee_academique;
        $idEntite = $request->id_entite;
        $idBudget = $request->id_budget;
        $idLigneEntree = $request->id_ligne_entree ?? $request->id_ligne;
        $idLigneSortie = $request->id_ligne_sortie ?? $request->id_ligne;

        // =========================
        // 💰 DISPONIBILITÉ CAISSE
        // =========================
        $disponibilite = $this->buildDisponibiliteCaissesData($request)['totalApresTransfert'];

        // =========================
        // 🔵 ENTRÉES
        // =========================
        $donneesEntrees = donnee_ligne_budgetaire_entree::with([
            'budget',
            'ligne_budgetaire_entrees',
            'element_ligne_budgetaire_entrees',
            'facture_etudiants.reglement_etudiants',
            'facture_etudiants.reductions',
            'facture_etudiants.entite'
        ])
            ->when($idBudget, fn($q) => $q->where('id_budget', $idBudget))
            ->when($idLigneEntree, fn($q) => $q->where('id_ligne_budgetaire_entree', $idLigneEntree))
            ->get();

        /*$entrees = $donneesEntrees->map(function ($d) use ($dateDebut,$dateFin,$idAnnee,$idEntite,$idLigne) {

            $factures = $d->facture_etudiants
                ->when($idAnnee, fn($c)=>$c->where('id_annee_academique',$idAnnee))
            ->when($idEntite, fn($c)=>$c->where('id_entite',$idEntite))
            ->when($idLigne, fn($c)=>$c->where('id_ligne_budgetaire_entree',$idLigne))
            ->whereBetween('date_facture',[$dateDebut,$dateFin]);*/
        $entrees = $donneesEntrees->map(function ($d) use ($dateDebut, $dateFin, $idAnnee, $idEntite, $idBudget, $idLigneEntree) {

            $factures = $d->facture_etudiants
                ->when($idAnnee, fn($c)=>$c->where('id_annee_academique',$idAnnee))
            ->when($idEntite, fn($c)=>$c->where('id_entite',$idEntite))
            ->when($idBudget, fn($c)=>$c->where('id_budget',$idBudget))
            ->when($idLigneEntree, fn($c)=>$c->where('id_ligne_budgetaire_entree',$idLigneEntree))
            ->whereBetween('date_facture',[$dateDebut,$dateFin]);


        $facture  = $factures->sum('montant_total_facture');
        $reduction = $factures->sum(fn($facture) => $facture->montant_reduction);

        $encaisse = $factures->flatMap->reglement_etudiants
            ->where('date_reglement', '>=', $dateDebut)
            ->where('date_reglement', '<=', $dateFin)
            ->sum('montant_reglement');

        $entite = optional($factures->first()?->entite)->nom_entite ?? '—';

        return [
            'entite'  => $entite,
            'budget'  => optional($d->budget)->libelle_ligne_budget,
            'ligne'   => optional($d->ligne_budgetaire_entrees)->libelle_ligne_budgetaire_entree,
            'element' => optional($d->element_ligne_budgetaire_entrees)->libelle_elements_ligne_budgetaire_entree,
            'donnee'  => $d->donnee_ligne_budgetaire_entree,

            'prevu'    => $d->montant,
            'facture'  => $facture,
            'reduction' => $reduction,
            'encaisse' => $encaisse,
            'reste'    => $facture - $reduction - $encaisse
        ];

    });

        $entreesSpeciales = entree_speciale::with(['budget', 'echeances'])
            ->where('statut', '!=', 'annule')
            ->when($idAnnee, fn($q) => $q->where('id_annee_academique', $idAnnee))
            ->when($idBudget, fn($q) => $q->where('id_budget', $idBudget))
            ->whereBetween('date_entree', [$dateDebut, $dateFin])
            ->get()
            ->map(function ($e) {
                $encaisse = $e->type_entree === 'dette' ? $e->montant : $e->montant_net_encaisse;
                $reste = $e->type_entree === 'dette' ? 0 : $e->montant - $e->montant_net_encaisse;

                return [
                    'entite' => $e->nom_tiers ?: 'Entrees speciales',
                    'budget' => optional($e->budget)->libelle_ligne_budget,
                    'ligne' => 'Entree speciale - ' . ucfirst($e->type_entree),
                    'element' => '',
                    'donnee' => $e->libelle,
                    'prevu' => $e->montant,
                    'facture' => 0,
                    'reduction' => 0,
                    'encaisse' => $encaisse,
                    'reste' => $reste,
                ];
            });

        $entrees = $entrees->concat($entreesSpeciales)->values();

        // =========================
        // 🔴 SORTIES
        // =========================
        $donneesSorties = donnee_ligne_budgetaire_sortie::with([
            'budgets',
            'ligne_budgetaire_sortie',
            'element_ligne_budgetaire_sorties',
            'decaissements.bon.entites'
        ])
            ->when($idBudget, fn($q) => $q->where('id_budget', $idBudget))
            ->when($idLigneSortie, fn($q) => $q->where('id_ligne_budgetaire_sortie', $idLigneSortie))
            ->get();

        /*$sorties = $donneesSorties->map(function ($d) use ($dateDebut,$dateFin,$idAnnee,$idEntite,$idLigne) {

            $decaissements = $d->decaissements
                ->when($idAnnee, fn($c)=>$c->where('id_annee_academique',$idAnnee))
            ->when($idLigne, fn($c)=>$c->where('id_ligne_budgetaire_sortie',$idLigne))
            ->whereBetween('date_depense',[$dateDebut,$dateFin]);*/
            $sorties = $donneesSorties->map(function ($d) use ($dateDebut, $dateFin, $idAnnee, $idEntite, $idBudget, $idLigneSortie) {

                $decaissements = $d->decaissements
                    ->when($idAnnee, fn($c)=>$c->where('id_annee_academique',$idAnnee))
            ->when($idEntite, function ($c) use ($idEntite) {
                return $c->filter(function ($decaissement) use ($idEntite) {
                    return optional(optional($decaissement->bon)->entites)->id == $idEntite;
                });
            })
            ->when($idBudget, fn($c)=>$c->where('id_budget',$idBudget))
            ->when($idLigneSortie, fn($c)=>$c->where('id_ligne_budgetaire_sortie',$idLigneSortie))
            ->whereBetween('date_depense',[$dateDebut,$dateFin]);

        $depense = $decaissements->sum('montant');

        $entite = optional(
                optional($decaissements->first())->bon
            )->entites->nom_entite ?? '—';

        return [
            'entite'  => $entite,
            'budget'  => optional($d->budgets)->libelle_ligne_budget,
            'ligne'   => optional($d->ligne_budgetaire_sortie)->libelle_ligne_budgetaire_sortie,
            'element' => optional($d->element_ligne_budgetaire_sorties)->libelle_elements_ligne_budgetaire_sortie,
            'donnee'  => $d->donnee_ligne_budgetaire_sortie,

            'prevu'   => $d->montant,
            'depense' => $depense,
            'reste'   => $d->montant - $depense
        ];
    });

        $entreesSpecialesSorties = entree_speciale::with(['budget', 'decaissements', 'echeances'])
            ->whereIn('type_entree', ['dette', 'don', 'apport'])
            ->where('statut', '!=', 'annule')
            ->when($idAnnee, fn($q) => $q->where('id_annee_academique', $idAnnee))
            ->when($idBudget, fn($q) => $q->where('id_budget', $idBudget))
            ->whereBetween('date_entree', [$dateDebut, $dateFin])
            ->get()
            ->map(function ($entreeSpeciale) use ($dateDebut, $dateFin) {
                $depenses = $entreeSpeciale->decaissements
                    ->whereBetween('date_depense', [$dateDebut, $dateFin]);
                $depenseTotal = $depenses->sum('montant');
                $type = ucfirst($entreeSpeciale->type_entree);

                return [
                    'entite' => $entreeSpeciale->nom_tiers ?: 'Entrees speciales',
                    'budget' => optional($entreeSpeciale->budget)->libelle_ligne_budget,
                    'ligne' => 'Utilisation entree speciale - ' . $type,
                    'element' => '',
                    'donnee' => $entreeSpeciale->libelle,
                    'prevu' => $entreeSpeciale->montant,
                    'depense' => $depenseTotal,
                    'reste' => $entreeSpeciale->montant_net_encaisse - $depenseTotal,
                ];
            });

        $remboursementsSorties = entree_speciale_echeance::with('entree_speciale.budget')
            ->where('statut', 'payee')
            ->whereBetween('date_paiement', [$dateDebut, $dateFin])
            ->when($idAnnee, fn($q) => $q->where('id_annee_academique_paiement', $idAnnee))
            ->when($idBudget, fn($q) => $q->whereHas('entree_speciale', fn($d) => $d->where('id_budget', $idBudget)))
            ->get()
            ->groupBy('id_entree_speciale')
            ->map(function ($echeances) use ($dateFin) {
                $dette = $echeances->first()->entree_speciale;
                $montantDette = (float) optional($dette)->montant;
                $montantRembourse = (float) $echeances->sum('montant_paye');
                $totalRembourseAuDateFin = entree_speciale_echeance::where('id_entree_speciale', optional($dette)->id)
                    ->where('statut', 'payee')
                    ->whereDate('date_paiement', '<=', $dateFin)
                    ->sum('montant_paye');

                return [
                    'entite' => optional($dette)->nom_tiers ?: 'Dettes remboursees',
                    'budget' => optional(optional($dette)->budget)->libelle_ligne_budget,
                    'ligne' => 'Remboursement dette',
                    'element' => '',
                    'donnee' => 'Remboursement dette - ' . (optional($dette)->libelle ?? 'Dette'),
                    'prevu' => $montantDette,
                    'depense' => $montantRembourse,
                    'reste' => max($montantDette - (float) $totalRembourseAuDateFin, 0),
                ];
            })
            ->values();

        $sorties = $sorties->concat($entreesSpecialesSorties)->concat($remboursementsSorties)->values();
        //dd($entrees,$sorties);

        // =========================
        // GROUP BY ENTITÉ
        // =========================
        $entreesGrouped = $entrees->groupBy('entite');
        $sortiesGrouped = $sorties->groupBy('entite');

        // =========================
        // DÉFICIT
        // =========================
        $resteEntrees = $entrees->sum('reste');
        $resteSorties = $sorties->sum('reste');

        $deficit = $disponibilite + $resteEntrees - $resteSorties;
        $annees  = annee_academique::orderBy('nom','desc')->get();
        $entites = entite::orderBy('nom_entite')->get();
        $budgets = Budget::orderBy('libelle_ligne_budget')->get();

        return view('Admin.Etats.sorties.global', compact(
            'entreesGrouped',
            'sortiesGrouped',
            'disponibilite',
            'deficit',
            'annees',
            'entites',
            'budgets' // 🔥 IMPORTANT
        ));
    }


    public function exportGlobalPdf(Request $request)
    {
        // 🔁 On appelle ta fonction existante
        $data = $this->etatGlobal($request)->getData();
        $data['dateDebut'] = $request->date_debut ?? '2000-01-01';
        $data['dateFin'] = $request->date_fin ?? now()->format('Y-m-d');

        $entreesGrouped = $data['entreesGrouped'];
        $sortiesGrouped = $data['sortiesGrouped'];
        $disponibilite  = $data['disponibilite'];
        $deficit        = $data['deficit'];

        // 🔥 PDF
        $pdf = Pdf::loadView('Admin.Etats.sorties.global_pdf', $data)
            ->setPaper('a3', 'landscape');

        return $pdf->download('etat_global_budgetaire.pdf');
    }

    public function exportGlobalExcel(Request $request)
    {
        $data = $this->etatGlobal($request)->getData();
        $data['dateDebut'] = $request->date_debut ?? '2000-01-01';
        $data['dateFin'] = $request->date_fin ?? now()->format('Y-m-d');

        return Excel::download(
            new ViewExport('Admin.Etats.sorties.global_pdf', $data),
            'etat_global_budgetaire.xlsx'
        );
    }
    public function etatGlobaldejapassable(Request $request)
    {
        $dateDebut = $request->date_debut ?? '2000-01-01';
        $dateFin   = $request->date_fin ?? now();

        // =========================
        // 💰 DISPONIBILITÉ CAISSE
        // =========================
        $entreeCaisse = Transfert_caisse::whereDate('date_transfert','<=',$dateFin)
            ->sum('montant_transfert');

        $sortieCaisse = decaissement::whereDate('date_depense','<=',$dateFin)
            ->sum('montant');

        $disponibilite = $entreeCaisse - $sortieCaisse;

        // =========================
        // 🔵 ENTRÉES
        // =========================
        $donneesEntrees = donnee_ligne_budgetaire_entree::with([
            'budget',
            'ligne_budgetaire_entrees',
            'facture_etudiants.reglement_etudiants',
            'facture_etudiants.entite'
        ])->get();

        $entrees = $donneesEntrees->map(function ($d) use ($request, $dateDebut, $dateFin) {

            $factures = $d->facture_etudiants
                ->whereBetween('date_facture', [$dateDebut, $dateFin]);

            $facture = $factures->sum('montant_total_facture');

            $encaisse = $factures->flatMap->reglement_etudiants
                ->sum('montant_reglement');

            $entite = optional($factures->first()?->entite)->nom_entite ?? '—';

        return [
            'type'    => 'entree',
            'entite'  => $entite,
            'budget'  => optional($d->budget)->libelle_ligne_budget,
            'ligne'   => optional($d->ligne_budgetaire_entrees)->libelle_ligne_budgetaire_entree,
            'donnee'  => $d->donnee_ligne_budgetaire_entree,
            'prevu'   => $d->montant,
            'facture' => $facture,
            'encaisse'=> $encaisse,
            'reste'   => $facture - $encaisse
        ];
    });

        // =========================
        // 🔴 SORTIES
        // =========================
        $donneesSorties = donnee_ligne_budgetaire_sortie::with([
            'budgets',
            'ligne_budgetaire_sortie',
            'decaissements.bon.entites'
        ])->get();

        $sorties = $donneesSorties->map(function ($d) use ($dateDebut, $dateFin) {

            $decaissements = $d->decaissements
                ->whereBetween('date_depense', [$dateDebut, $dateFin]);

            $depense = $decaissements->sum('montant');

            $entite = optional(
                    optional($decaissements->first())->bon
                )->entites->nom_entite ?? '—';

            return [
                'type'    => 'sortie',
                'entite'  => $entite,
                'budget'  => optional($d->budgets)->libelle_ligne_budget,
                'ligne'   => optional($d->ligne_budgetaire_sortie)->libelle_ligne_budgetaire_sortie,
                'donnee'  => $d->donnee_ligne_budgetaire_sortie,
                'prevu'   => $d->montant,
                'facture' => $depense,
                'encaisse'=> 0,
                'reste'   => $d->montant - $depense
            ];
        });

        // =========================
        // 🔄 FUSION
        // =========================
        $global = $entrees->merge($sorties);

        // =========================
        // 📊 GROUP BY ENTITÉ
        // =========================
        $etatGrouped = $global->groupBy('entite');

        // =========================
        // ⚠️ DÉFICIT
        // =========================
        $resteEntrees = $entrees->sum('reste');
        $resteSorties = $sorties->sum('reste');

        $deficit = $disponibilite + $resteEntrees - $resteSorties;

        return view('Admin.Etats.sorties.global', compact(
            'etatGrouped',
            'disponibilite',
            'deficit'
        ));
    }
    public function etatGlobalfin(Request $request)
    {
        $dateDebut = $request->date_debut;
        $dateFin   = $request->date_fin ?? now();

        $idBudget  = $request->id_budget;
        $idAnnee   = $request->id_annee_academique;

        // =========================
        // 💰 DISPONIBILITÉ CAISSE
        // =========================
        $entreeCaisse = Transfert_caisse::when($dateFin, fn($q) =>
        $q->whereDate('date_transfert', '<=', $dateFin)
    )->sum('montant_transfert');

    $sortieCaisse = decaissement::when($dateFin, fn($q) =>
        $q->whereDate('date_depense', '<=', $dateFin)
    )->sum('montant');

    $disponibilite = $entreeCaisse - $sortieCaisse;

    // =========================
    // 🔵 ENTRÉES STRUCTURÉES
    // =========================
    $entrees = donnee_ligne_budgetaire_entree::with([
        'budget',
        'ligne_budgetaire_entrees',
        'element_ligne_budgetaire_entrees',
        'facture_etudiants.reglement_etudiants',
        'facture_etudiants.entite'
    ])
        ->when($idBudget, fn($q) => $q->where('id_budget', $idBudget))
    ->get()
        ->groupBy(function ($d) {
            return optional(
                    optional($d->facture_etudiants->first())->entite
                )->nom_entite ?? 'Non défini';
        })
        ->map(function ($group) use ($dateDebut, $dateFin, $idAnnee) {

            return $group
                ->groupBy(fn($d) => optional($d->ligne_budgetaire_entrees)->libelle_ligne_budgetaire_entree)
            ->map(function ($ligneGroup) use ($dateDebut, $dateFin, $idAnnee) {

                return $ligneGroup
                    ->groupBy(fn($d) => optional($d->element_ligne_budgetaire_entrees)->libelle_elements_ligne_budgetaire_entree)
                    ->map(function ($elements) use ($dateDebut, $dateFin, $idAnnee) {

                    $prevu = 0;
                    $encaisse = 0;

                    foreach ($elements as $d) {

                        $factures = $d->facture_etudiants
                            ->when($idAnnee, fn($c) => $c->where('id_annee_academique', $idAnnee))
                                ->when($dateDebut, fn($c) => $c->where('date_facture','>=',$dateDebut))
                                ->when($dateFin, fn($c) => $c->where('date_facture','<=',$dateFin));

                            $prevu += $factures->sum('montant_total_facture');

                            $encaisse += $factures->sum(fn($f) =>
                                $f->reglement_etudiants->sum('montant_reglement')
                            );
                        }

                    return [
                        'budget'   => optional($elements->first()->budget)->libelle_ligne_budget,
                        'ligne'    => optional($elements->first()->ligne_budgetaire_entrees)->libelle_ligne_budgetaire_entree,
                        'element'  => optional($elements->first()->element_ligne_budgetaire_entrees)->libelle_elements_ligne_budgetaire_entree,
                        'prevu'    => $prevu,
                        'realise'  => $encaisse,
                        'reste'    => $prevu - $encaisse,
                    ];
                });
            });
    });

    // =========================
    // 🔴 SORTIES STRUCTURÉES
    // =========================
    $sorties = donnee_ligne_budgetaire_sortie::with([
        'budgets',
        'ligne_budgetaire_sortie',
        'element_ligne_budgetaire_sorties',
        'decaissements.bon.entites'
    ])
        ->when($idBudget, fn($q) => $q->where('id_budget', $idBudget))
    ->get()
        ->groupBy(function ($d) {
            return optional(
                    optional($d->decaissements->first())->bon
                )->entites->nom_entite ?? 'Non défini';
        })
        ->map(function ($group) use ($dateDebut, $dateFin, $idAnnee) {

            return $group
                ->groupBy(fn($d) => optional($d->ligne_budgetaire_sortie)->libelle_ligne_budgetaire_sortie)
            ->map(function ($ligneGroup) use ($dateDebut, $dateFin, $idAnnee) {

                return $ligneGroup
                    ->groupBy(fn($d) => optional($d->element_ligne_budgetaire_sorties)->libelle_elements_ligne_budgetaire_sortie)
                    ->map(function ($elements) use ($dateDebut, $dateFin, $idAnnee) {

                    $prevu = 0;
                    $depense = 0;

                    foreach ($elements as $d) {

                        $decaissements = $d->decaissements
                            ->when($idAnnee, fn($c) => $c->where('id_annee_academique', $idAnnee))
                                ->when($dateDebut, fn($c) => $c->where('date_depense','>=',$dateDebut))
                                ->when($dateFin, fn($c) => $c->where('date_depense','<=',$dateFin));

                            $prevu += $d->montant;
                            $depense += $decaissements->sum('montant');
                        }

                    return [
                        'budget'  => optional($elements->first()->budgets)->libelle_ligne_budget,
                        'ligne'   => optional($elements->first()->ligne_budgetaire_sortie)->libelle_ligne_budgetaire_sortie,
                        'element' => optional($elements->first()->element_ligne_budgetaire_sorties)->libelle_elements_ligne_budgetaire_sortie,
                        'prevu'   => $prevu,
                        'realise' => $depense,
                        'reste'   => $prevu - $depense,
                    ];
                });
            });
    });

    // =========================
    // 📊 INDICATEUR GLOBAL
    // =========================
    $resteRecouvrer = collect($entrees)->flatten(2)->sum('reste');
    $resteDecaisser = collect($sorties)->flatten(2)->sum('reste');

    $deficit = $disponibilite + $resteRecouvrer - $resteDecaisser;
$budgets = \App\Models\Budget::all();
$annees  = \App\Models\annee_academique::all();
    return view('Admin.Etats.sorties.global', compact(
        'entrees',
        'sorties',
        'disponibilite',
        'deficit',
        'budgets',
        'annees'
    ));
}

    public function etatGlobaldonneunpeu(Request $request)
    {
        $dateDebut = $request->date_debut;
        $dateFin   = $request->date_fin ?? now();

        $idBudget  = $request->id_budget;
        $idAnnee   = $request->id_annee_academique;

        // =========================
        // 💰 SOLDE CAISSE
        // =========================
        $entree = Transfert_caisse::when($dateFin, fn($q) =>
        $q->whereDate('date_transfert', '<=', $dateFin)
    )->sum('montant_transfert');

    $sortie = decaissement::when($dateFin, fn($q) =>
        $q->whereDate('date_depense', '<=', $dateFin)
    )->sum('montant');

    $soldeGlobal = $entree - $sortie;

    // =========================
    // 🔵 ENTRÉES
    // =========================
    $donneesEntrees = donnee_ligne_budgetaire_entree::with([
        'budget',
        'ligne_budgetaire_entrees',
        'element_ligne_budgetaire_entrees',
        'facture_etudiants.reglement_etudiants',
        'facture_etudiants.entite'
    ])
        ->when($idBudget, fn($q) => $q->where('id_budget', $idBudget))
    ->get();

    $entreesGrouped = $donneesEntrees->groupBy(function ($d) {
        return optional(
                optional($d->facture_etudiants->first())->entite
            )->nom_entite ?? 'Non défini';
    })->map(function ($group) use ($dateDebut, $dateFin, $idAnnee) {

        return $group
            ->groupBy(fn($d) => optional($d->ligne_budgetaire_entrees)->libelle_ligne_budgetaire_entree)
            ->map(function ($ligneGroup) use ($dateDebut, $dateFin, $idAnnee) {

            return $ligneGroup
                ->groupBy(fn($d) => optional($d->element_ligne_budgetaire_entrees)->libelle_elements_ligne_budgetaire_entree)
                    ->map(function ($elementGroup) use ($dateDebut, $dateFin, $idAnnee) {

                $prevu = 0;
                $encaisse = 0;

                foreach ($elementGroup as $d) {

                    $factures = $d->facture_etudiants
                        ->when($idAnnee, fn($c) => $c->where('id_annee_academique', $idAnnee))
                                ->when($dateDebut, fn($c) => $c->where('date_facture','>=',$dateDebut))
                                ->when($dateFin, fn($c) => $c->where('date_facture','<=',$dateFin));

                            $prevu += $factures->sum('montant_total_facture');

                            $encaisse += $factures->sum(fn($f) =>
                                $f->reglement_etudiants->sum('montant_reglement')
                            );
                        }

                return [
                    'budget'   => optional($elementGroup->first()->budget)->libelle_ligne_budget,
                    'ligne'    => optional($elementGroup->first()->ligne_budgetaire_entrees)->libelle_ligne_budgetaire_entree,
                    'element'  => optional($elementGroup->first()->element_ligne_budgetaire_entrees)->libelle_elements_ligne_budgetaire_entree,
                    'prevu'    => $prevu,
                    'encaisse' => $encaisse,
                    'reste'    => $prevu - $encaisse,
                ];
            });
            });
    });

    // =========================
    // 🔴 SORTIES
    // =========================
    $donneesSorties = donnee_ligne_budgetaire_sortie::with([
        'budgets',
        'ligne_budgetaire_sortie',
        'element_ligne_budgetaire_sorties',
        'decaissements.bon.entites'
    ])
        ->when($idBudget, fn($q) => $q->where('id_budget', $idBudget))
    ->get();

    $sortiesGrouped = $donneesSorties->groupBy(function ($d) {
        return optional(
                optional($d->decaissements->first())->bon
            )->entites->nom_entite ?? 'Non défini';
    })->map(function ($group) use ($dateDebut, $dateFin, $idAnnee) {

        return $group
            ->groupBy(fn($d) => optional($d->ligne_budgetaire_sortie)->libelle_ligne_budgetaire_sortie)
            ->map(function ($ligneGroup) use ($dateDebut, $dateFin, $idAnnee) {

            return $ligneGroup
                ->groupBy(fn($d) => optional($d->element_ligne_budgetaire_sorties)->libelle_elements_ligne_budgetaire_sortie)
                    ->map(function ($elementGroup) use ($dateDebut, $dateFin, $idAnnee) {

                $prevu = 0;
                $depense = 0;

                foreach ($elementGroup as $d) {

                    $decaissements = $d->decaissements
                        ->when($idAnnee, fn($c) => $c->where('id_annee_academique', $idAnnee))
                                ->when($dateDebut, fn($c) => $c->where('date_depense','>=',$dateDebut))
                                ->when($dateFin, fn($c) => $c->where('date_depense','<=',$dateFin));

                            $prevu += $d->montant;
                            $depense += $decaissements->sum('montant');
                        }

                return [
                    'budget'  => optional($elementGroup->first()->budgets)->libelle_ligne_budget,
                    'ligne'   => optional($elementGroup->first()->ligne_budgetaire_sortie)->libelle_ligne_budgetaire_sortie,
                    'element' => optional($elementGroup->first()->element_ligne_budgetaire_sorties)->libelle_elements_ligne_budgetaire_sortie,
                    'prevu'   => $prevu,
                    'depense' => $depense,
                    'reste'   => $prevu - $depense,
                ];
            });
            });
    });

    return view('Admin.Etats.sorties.global', compact(
        'entreesGrouped',
        'sortiesGrouped',
        'soldeGlobal'
    ));
}
    public function etatGlobalvalideok(Request $request)
    {
        $dateDebut = $request->date_debut;
        $dateFin   = $request->date_fin ?? now();

        $idBudget  = $request->id_budget;
        $idAnnee   = $request->id_annee_academique;

        // =========================
        // 💰 SOLDE CAISSE
        // =========================
        $entree = Transfert_caisse::when($dateFin, fn($q) =>
        $q->whereDate('date_transfert', '<=', $dateFin)
    )->sum('montant_transfert');

    $sortie = decaissement::when($dateFin, fn($q) =>
        $q->whereDate('date_depense', '<=', $dateFin)
    )->sum('montant');

    $soldeGlobal = $entree - $sortie;

    // =========================
    // 🔵 ENTRÉES (TOUTES LES LIGNES)
    // =========================
    $donneesEntrees = \App\Models\donnee_ligne_budgetaire_entree::with([
        'budget',
        'ligne_budgetaire_entrees',
        'facture_etudiants.entite',
        'facture_etudiants.reglement_etudiants'
    ])
        ->when($idBudget, fn($q) => $q->where('id_budget', $idBudget))
        ->get();
$entreesGrouped = $donneesEntrees->groupBy(function ($d) {

    $entite = optional(
            optional($d->facture_etudiants->first())->entite
        )->nom_entite ?? 'Non défini';

    return $entite;

})->map(function ($group) use ($dateDebut, $dateFin, $idAnnee) {

    return $group->map(function ($d) use ($dateDebut, $dateFin, $idAnnee) {

        $factures = $d->facture_etudiants
            ->when($idAnnee, fn($c) => $c->where('id_annee_academique', $idAnnee))
            ->when($dateDebut, fn($c) => $c->where('date_facture','>=',$dateDebut))
            ->when($dateFin, fn($c) => $c->where('date_facture','<=',$dateFin));

        $prevu = $factures->sum('montant_total_facture');

        $encaisse = $factures->sum(fn($f) =>
            $f->reglement_etudiants->sum('montant_reglement')
        );

        return [
            'budget'   => optional($d->budget)->libelle_ligne_budget,
            'ligne'    => optional($d->ligne_budgetaire_entrees)->libelle_ligne_budgetaire_entree, // ✅ CORRIGÉ
            'prevu'    => $prevu,
            'encaisse' => $encaisse,
            'reste'    => $prevu - $encaisse,
        ];
    });
});
   /* $entreesGrouped = $donneesEntrees->groupBy(fn($d) =>
        optional($d->entite)->nom_entite ?? 'Non défini'
    )->map(function ($group) use ($dateDebut, $dateFin, $idAnnee) {

        return $group->map(function ($d) use ($dateDebut, $dateFin, $idAnnee) {

            $factures = $d->facture_etudiants
                ->when($idAnnee, fn($c) => $c->where('id_annee_academique', $idAnnee))
                ->when($dateDebut, fn($c) => $c->where('date_facture','>=',$dateDebut))
                ->when($dateFin, fn($c) => $c->where('date_facture','<=',$dateFin));

            $prevu = $factures->sum('montant_total_facture');

            $encaisse = $factures->sum(function ($f) {
                return $f->reglement_etudiants->sum('montant_reglement');
            });

            return [
                'budget'   => optional($d->budget)->libelle_ligne_budget,
                'ligne'    => optional($d->ligne_budgetaire_entree)->libelle_ligne_budgetaire_entree,
                'prevu'    => $prevu,
                'encaisse' => $encaisse,
                'reste'    => $prevu - $encaisse,
            ];
        });
    });*/

    // =========================
    // 🔴 SORTIES (TOUTES LES LIGNES)
    // =========================
    $donneesSorties = \App\Models\donnee_ligne_budgetaire_sortie::with([
        'budgets',
        'ligne_budgetaire_sortie',
        'decaissements.bon.entites'
    ])
        ->when($idBudget, fn($q) => $q->where('id_budget', $idBudget))
        ->get();

   $sortiesGrouped = $donneesSorties->groupBy(function ($d) {

       $entite = optional(
               optional($d->decaissements->first())->bon
           )->entites->nom_entite ?? 'Non défini';

       return $entite;

   })->map(function ($group) use ($dateDebut, $dateFin, $idAnnee) {

       return $group->map(function ($d) use ($dateDebut, $dateFin, $idAnnee) {

           $decaissements = $d->decaissements
               ->when($idAnnee, fn($c) => $c->where('id_annee_academique', $idAnnee))
            ->when($dateDebut, fn($c) => $c->where('date_depense','>=',$dateDebut))
            ->when($dateFin, fn($c) => $c->where('date_depense','<=',$dateFin));

        // ✅ CORRECTION ICI
        $entite = optional(
                optional($d->decaissements->first())->bon
            )->entites->nom_entite ?? 'Non défini';

        $depense = $decaissements->sum('montant');

        return [
            'budget'  => optional($d->budgets)->libelle_ligne_budget,
            'ligne'   => optional($d->ligne_budgetaire_sortie)->libelle_ligne_budgetaire_sortie,
            'prevu'   => $d->montant,
            'depense' => $depense,
            'reste'   => $d->montant - $depense,
            'entite'  => $entite,
        ];
    });
   });

    $budgets = Budget::all();
    $annees  = annee_academique::all();

    return view('Admin.Etats.sorties.global', [
        'entreesGrouped' => $entreesGrouped,
        'sortiesGrouped' => $sortiesGrouped,
        'soldeGlobal' => $soldeGlobal,
        'budgets' => $budgets,
        'annees' => $annees,

    ]);
}
    public function etatGlobalvok(Request $request)
    {
        $dateDebut = $request->date_debut;
        $dateFin   = $request->date_fin ?? now();

        $idBudget  = $request->id_budget;
        $idAnnee   = $request->id_annee_academique;

        // =========================
        // 💰 SOLDE CAISSE
        // =========================
        $entree = Transfert_caisse::when($dateFin, fn($q) =>
        $q->whereDate('date_transfert', '<=', $dateFin)
    )->sum('montant_transfert');

    $sortie = decaissement::when($dateFin, fn($q) =>
        $q->whereDate('date_depense', '<=', $dateFin)
    )->sum('montant');

    $soldeGlobal = $entree - $sortie;

    // =========================
    // 🔵 ENTRÉES
    // =========================
    $factures = facture_etudiant::with([
        'budget',
        'ligne_budgetaire_entree',
        'reglement_etudiants',
        'entite'
    ])
        ->when($idBudget, fn($q) => $q->where('id_budget', $idBudget))
    ->when($idAnnee, fn($q) => $q->where('id_annee_academique', $idAnnee))
    ->when($dateDebut, fn($q) => $q->whereDate('date_facture', '>=', $dateDebut))
    ->when($dateFin, fn($q) => $q->whereDate('date_facture', '<=', $dateFin))
    ->get();

    $entrees = $factures->groupBy(fn($f) =>
        optional($f->entite)->nom_entite ?? 'Non défini'
    )->map(function ($group) {

        return $group->groupBy(fn($f) =>
            optional($f->ligne_budgetaire_entree)->libelle_ligne_budgetaire_entree
        )->map(function ($ligneGroup) {

            $first = $ligneGroup->first();

            return [
                'budget'   => optional($first->budget)->libelle_ligne_budget,
                'ligne'    => optional($first->ligne_budgetaire_entree)->libelle_ligne_budgetaire_entree,
                'prevu'    => $ligneGroup->sum('montant_total_facture'),
                'encaisse' => $ligneGroup->sum(fn($f) =>
                    $f->reglement_etudiants->sum('montant_reglement')
                ),
                'reste'    => $ligneGroup->sum('montant_total_facture')
            - $ligneGroup->sum(fn($f) =>
                                    $f->reglement_etudiants->sum('montant_reglement')
                                ),
            ];
        });
    });

    // =========================
    // 🔴 SORTIES (CORRIGÉ)
    // =========================
    $decaissements = decaissement::with([
        'bon.entites',
        'ligne_budgetaire_sorties',
        'budgets'
    ])
        ->when($idBudget, fn($q) => $q->where('id_budget', $idBudget))
    ->when($idAnnee, fn($q) => $q->where('id_annee_academique', $idAnnee))
    ->when($dateDebut, fn($q) => $q->whereDate('date_depense','>=',$dateDebut))
    ->when($dateFin, fn($q) => $q->whereDate('date_depense','<=',$dateFin))
    ->get();

    $sorties = $decaissements->groupBy(fn($d) =>
        optional($d->bon->entites)->nom_entite ?? 'Non défini'
    )->map(function ($group) {

        return $group->groupBy(fn($d) =>
            optional($d->ligne_budgetaire_sorties)->libelle_ligne_budgetaire_sortie
        )->map(function ($ligneGroup) {

            $first = $ligneGroup->first();

            return [
                'budget'  => optional($first->budgets)->libelle_ligne_budget,
                'ligne'   => optional($first->ligne_budgetaire_sorties)->libelle_ligne_budgetaire_sortie,
                'depense' => $ligneGroup->sum('montant'),
                // 🔥 AJOUT IMPORTANT
                'prevu'   => $ligneGroup->sum('montant'),
                'reste'   => 0,

            ];
        });
    });

    // =========================
    // 📦 DATA
    // =========================
    $budgets = Budget::all();
    $annees  = annee_academique::all();
return view('Admin.Etats.sorties.global', [
    'entreesGrouped' => $entrees,
    'sortiesGrouped' => $sorties,
    'soldeGlobal' => $soldeGlobal,
    'budgets' => $budgets,
    'annees' => $annees,

]);
   /* return view('Admin.Etats.sorties.global', compact(
        'entrees',
        'sorties',
        'soldeGlobal',
        'budgets',
        'annees'
    ));*/
}
    public function etatGlobal11(Request $request)
    {
        // =========================
        // 🔎 PARAMÈTRES (NON OBLIGATOIRES)
        // =========================
        $dateDebut = $request->date_debut;
        $dateFin   = $request->date_fin ?? now();

        $idBudget  = $request->id_budget;
        $idAnnee   = $request->id_annee_academique;

        // =========================
        // 💰 SOLDE CAISSE GLOBAL
        // =========================
        $entree = Transfert_caisse::when($dateFin, fn($q) =>
        $q->whereDate('date_transfert', '<=', $dateFin)
    )->sum('montant_transfert');

    $sortie = decaissement::when($dateFin, fn($q) =>
        $q->whereDate('date_depense', '<=', $dateFin)
    )->sum('montant');

    $soldeGlobal = $entree - $sortie;

    // =========================
    // 🔵 ENTRÉES (FACTURES + RÈGLEMENTS)
    // =========================
    //$factures = facture_etudiant::with(['etudiants','budget','ligneBudgetaire','reglements'])
$factures = facture_etudiant::with([
    'etudiants',
    'budget',
    'ligne_budgetaire_entree',
    'reglement_etudiants',
    'entite'
])
        ->when($idBudget, fn($q) =>
            $q->where('id_budget', $idBudget)
        )

        ->when($idAnnee, fn($q) =>
            $q->where('id_annee_academique', $idAnnee)
        )

        ->when($dateDebut, fn($q) =>
            $q->whereDate('date_facture', '>=', $dateDebut)
        )

        ->when($dateFin, fn($q) =>
            $q->whereDate('date_facture', '<=', $dateFin)
        )

        ->get();

    $entreesGrouped = $factures->groupBy(function ($f) {
        return optional($f->etudiants->entites)->nom_entite ?? 'Non défini';
    })->map(function ($group) {

        return $group->map(function ($f) {

            $encaisse = $f->reglement_etudiants->sum('montant_reglement');
            return [
                'budget'  => optional($f->budget)->libelle_ligne_budget,
                'ligne'   => optional($f->ligneBudgetaire)->libelle_ligne_budgetaire_entree,
                'prevu'   => $f->montant_total_facture,
                'encaisse'=> $encaisse,
                'reste'   => $f->montant_total_facture - $encaisse,
            ];
        });

    });
$entrees = $factures->groupBy(function ($f) {
    return optional($f->entite)->nom_entite ?? 'Non défini';
})->map(function ($group) {

    return $group->map(function ($f) {

        $encaisse = $f->reglement_etudiants->sum('montant_reglement');

        return [
            'entite'  => optional($f->entite)->nom_entite ?? 'Non défini',
            'budget'  => optional($f->budget)->libelle_ligne_budget,
            'ligne'   => optional($f->ligne_budgetaire_entree)->libelle_ligne_budgetaire_entree,

            'prevu'   => $f->montant_total_facture,
            'encaisse'=> $encaisse,
            'reste'   => $f->montant_total_facture - $encaisse,
        ];
    });

});
    // =========================
    // 🔴 SORTIES (BUDGET)
    // =========================
    $donnees = donnee_ligne_budgetaire_sortie::with([
        'budgets',
        'ligne_budgetaire_sortie'
    ])

        ->when($idBudget, fn($q) =>
        $q->where('id_budget', $idBudget)
    )

    ->get();

    $sortiesGrouped = $donnees->map(function ($d) use ($request, $dateDebut, $dateFin, $idAnnee,$idBudget) {

        // 🔥 ENTITÉ via BON → DÉCAISSEMENT
        $entiteNom = decaissement::where('id_donnee_ligne_budgetaire_sortie', $d->id)
            ->with('bon.entites')
            ->first();

        $entite = 'Non défini';

        if ($entiteNom && $entiteNom->bon && $entiteNom->bon->entites) {
            $entite = $entiteNom->bon->entites->nom_entite;
        }
        $decaissements = decaissement::with(['bon.entites','ligne_budgetaire_sorties','budgets'])

            ->when($idBudget, fn($q) =>
        $q->where('id_budget', $idBudget)
    )

    ->when($idAnnee, fn($q) =>
        $q->where('id_annee_academique', $idAnnee)
    )

    ->when($dateDebut, fn($q) =>
        $q->whereDate('date_depense','>=',$dateDebut)
    )

    ->when($dateFin, fn($q) =>
        $q->whereDate('date_depense','<=',$dateFin)
    )

    ->get();

        $sorties = $decaissements->groupBy(function ($d) {
            return optional($d->bon->entites)->nom_entite ?? 'Non défini';
        })->map(function ($group) {

            return $group->groupBy(function ($d) {
                return optional($d->ligne_budgetaire_sorties)->libelle_ligne_budgetaire_sortie;
            })->map(function ($ligneGroup) {

                $first = $ligneGroup->first();

                return [
                    'budget'  => optional($first->budgets)->libelle_ligne_budget,
                    'ligne'   => optional($first->ligne_budgetaire_sorties)->libelle_ligne_budgetaire_sortie,

                    'depense' => $ligneGroup->sum('montant'),
                ];
            });

        });
        $entite = optional(optional($entiteNom?->bon)->entites)->nom_entite ?? 'Non défini';
        // 🔥 DÉPENSE
        $depense = decaissement::where('id_donnee_ligne_budgetaire_sortie', $d->id)

            ->when($idAnnee, fn($q) =>
                $q->where('id_annee_academique', $idAnnee)
            )

            ->when($dateDebut, fn($q) =>
                $q->whereDate('date_depense','>=',$dateDebut)
            )

            ->when($dateFin, fn($q) =>
                $q->whereDate('date_depense','<=',$dateFin)
            )

            ->sum('montant');

        return [
            'entite' => $entite,
            'budget' => optional($d->budgets)->libelle_ligne_budget,
            'ligne'  => optional($d->ligne_budgetaire_sortie)->libelle_ligne_budgetaire_sortie,

            'prevu'   => $d->montant,
            'depense' => $depense,
            'reste'   => $d->montant - $depense,
            'sorties'   => $sorties

        ];

    })->groupBy('entite');

    // =========================
    // 📦 DONNÉES POUR LA VUE
    // =========================
    $budgets = Budget::all();
    $annees  = annee_academique::all();

    return view('Admin.Etats.sorties.global', compact(
        'entreesGrouped',
        'sortiesGrouped',
        'soldeGlobal',
        'budgets',
        'annees',
        'entrees',
        'sorties',


    ));
}
    public function atterrissage1(Request $request)
    {
        $dateFin = $request->date_fin ?? now();

        // 🔥 SOLDE CAISSES
        $entree = Transfert_caisse::whereDate('date_transfert','<=',$dateFin)
            ->sum('montant_transfert');

        $sortie = decaissement::whereDate('date_depense','<=',$dateFin)
            ->sum('montant');

        $soldeGlobal = $entree - $sortie;

        // 🔥 DONNÉES
        $donnees = donnee_ligne_budgetaire_sortie::with([
            'budgets',
            'ligne_budgetaire_sortie'
        ])->get();

        $etat = $donnees->map(function ($d) use ($request, $dateFin, $soldeGlobal) {

            $depense = decaissement::where('id_donnee_ligne_budgetaire_sortie', $d->id)
                ->when($request->date_debut, fn($q) =>
                    $q->whereDate('date_depense','>=',$request->date_debut)
                )
                ->whereDate('date_depense','<=',$dateFin)
                ->sum('montant');

            return [
                'budget' => $d->budgets->libelle_ligne_budget ?? '',
                'ligne'  => $d->ligne_budgetaire_sortie->libelle_ligne_budgetaire_sortie ?? '',
                'donnee' => $d->donnee_ligne_budgetaire_sortie,

                'prevu'   => $d->montant,
                'depense' => $depense,
                'reste'   => $d->montant - $depense,
                'solde'   => $soldeGlobal,
            ];
        });

        return view('Admin.Etats.sorties.atterrissage', compact('etat','soldeGlobal'));
    }

    // =========================
    // 📄 DÉCAISSEMENTS
    // =========================
    public function decaissements(Request $request)
    {
        $query = decaissement::with(['caisses','user','personnels']);

        if ($request->date_debut) {
            $query->whereDate('date_depense','>=',$request->date_debut);
        }

        if ($request->date_fin) {
            $query->whereDate('date_depense','<=',$request->date_fin);
        }

        if ($request->id_caisse) {
            $query->where('id_caisse',$request->id_caisse);
        }

        if ($request->id_user) {
            $query->where('id_user',$request->id_user);
        }

        if ($request->id_personnel) {
            $query->where('id_personnel',$request->id_personnel);
        }

        $decaissements = $query->get();

        $total = $decaissements->sum('montant');

        $caisses = caisse::where('type_caisse',1)->get();
        $users = User::all();
        $personnels = personnel::all();

        return view('Admin.Etats.sorties.decaissements', compact(
            'decaissements','total','caisses','users','personnels'
        ));
    }

    // =========================
    // 📌 SITUATION BON
    // =========================
    public function bon($id)
    {
        $bon = bon_commandeok::with('personnels')->findOrFail($id);

        $decaissements = decaissement::where('id_bon_commande',$id)->get();

        $total = $decaissements->sum('montant');
        $reste = $bon->montant_total - $total;

        return view('Admin.Etats.sorties.bon', compact(
            'bon','decaissements','total','reste'
        ));
    }

    // =========================
    // 📄 PDF
    // =========================
    public function exportPdf(Request $request)
    {
        $data = $this->atterrissage($request)->getData();
        $data['dateDebut'] = $request->date_debut ?? 'Début';
        $data['dateFin'] = $request->date_fin ?? now()->format('Y-m-d');

        $pdf = Pdf::loadView('Admin.Etats.sorties.pdf', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download('etat_sorties.pdf');
    }

    // =========================
    // 📊 EXCEL
    // =========================
    public function exportExcel(Request $request)
    {
        return Excel::download(new \App\Exports\EtatSortieExport($request), 'etat_sorties.xlsx');
    }
}
