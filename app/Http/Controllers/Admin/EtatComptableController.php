<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\cycle;
use App\Models\donnee_ligne_budgetaire_entree;
use App\Models\entree_speciale;
use App\Models\facture_etudiant;
use App\Models\filiere;
use App\Models\niveau;
use App\Models\specialite;
use Illuminate\Http\Request;
use App\Exports\EtatBudgetaireExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\annee_academique;
use App\Models\banque;
use App\Models\caisse;
use App\Models\entite;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\FacturesReglementsExport;




class EtatComptableController extends Controller
{

    public function index()
    {
        $title = 'États comptables et budgétaires';

        return view('Admin.Etats.index', compact('title'));
    }

    public function indexBudget()
    {
        // =========================
        // Données pour filtres
        // =========================
        $annees  = annee_academique::orderBy('nom', 'desc')->get();
        $entites = entite::orderBy('nom_entite')->get();
        $caisses = caisse::orderBy('nom_caisse')->get();
        $banques = banque::orderBy('nom_banque')->get();

        $title = 'États budgétaires – Recettes';

        // =========================
        // Vue de pilotage (PAS de calcul)
        // =========================
        return view('Admin.Etats.index_budget', compact(
            'annees',
            'entites',
            'caisses',
            'banques',
            'title'
        ));
    }
    public function atterrissageBudgetaire(Request $request)
    {
        // =========================
        // 🔹 Données pour filtres
        // =========================
        $annees  = annee_academique::orderBy('nom', 'desc')->get();
        $entites = entite::orderBy('nom_entite')->get();
        $caisses = caisse::orderBy('nom_caisse')->get();
        $banques = banque::orderBy('nom_banque')->get();

        // =========================
        // 🔹 Libellés d’en-tête
        // =========================
        $anneeNom = $request->filled('annee')
            ? optional(annee_academique::find($request->annee))->nom
            : null;

        $entiteNom = $request->filled('entite')
            ? optional(entite::find($request->entite))->nom_entite
            : null;

        $caisseNom = $request->filled('caisse')
            ? optional(caisse::find($request->caisse))->nom_caisse
            : null;

        $banqueNom = $request->filled('banque')
            ? optional(banque::find($request->banque))->nom_banque
            : null;

        // =========================
        // 🔹 BASE : TOUTES les données budgétaires
        // =========================
        $donnees = donnee_ligne_budgetaire_entree::with([
            'budget',
            'ligne_budgetaire_entrees',
            'facture_etudiants.reglement_etudiants',
            'facture_etudiants.reductions',
            'facture_etudiants.entite'
        ])->get();

        // =========================
        // 🔹 Construction de l’atterrissage
        // =========================
        $etat = $donnees->map(function ($d) use ($request) {

            // 👉 Filtrage logique (sans exclure la ligne)
            $factures = $d->facture_etudiants;

            if ($request->filled('annee')) {
                $factures = $factures->where('id_annee_academique', $request->annee);
            }

            if ($request->filled('entite')) {
                $factures = $factures->where('id_entite', $request->entite);
            }

            // 🔹 Facturé
            $factureTotal = $factures->sum('montant_total_facture');
            $reductionTotal = $factures->sum(fn($facture) => $facture->montant_reduction);

            // 🔹 Encaissé (éventuellement par caisse)
            $encaisseTotal = $factures
                ->flatMap->reglement_etudiants
                ->when($request->filled('caisse'), fn ($r) =>
                $r->where('id_caisse', $request->caisse)
            )
                ->when($request->filled('banque'), fn ($r) =>
                $r->where('id_banque', $request->banque)
            )
            ->sum('montant_reglement');

        // 🔹 Entité d’affichage
        $entiteAffichage = optional(
            $factures->first()?->entite
        )->nom_entite ?? '—';

        return [
            'entite'   => $entiteAffichage,
            'budget'   => optional($d->budget)->libelle_ligne_budget ?? '—',
            'ligne'    => optional($d->ligne_budgetaire_entrees)
                    ->libelle_ligne_budgetaire_entree ?? '—',
            'donnee'   => $d->donnee_ligne_budgetaire_entree,

            'prevu'    => (float) $d->montant,
            'facture'  => (float) $factureTotal,
            'reduction' => (float) $reductionTotal,
            'encaisse' => (float) $encaisseTotal,
            'reste'    => (float) ($factureTotal - $reductionTotal - $encaisseTotal),
        ];
    });

        $entreesSpeciales = entree_speciale::with(['budget', 'annee_academique', 'echeances'])
            ->where('statut', '!=', 'annule')
            ->when($request->filled('annee'), fn($q) => $q->where('id_annee_academique', $request->annee))
            ->when($request->filled('caisse'), fn($q) => $q->where('id_caisse', $request->caisse))
            ->when($request->filled('banque'), fn($q) => $q->where('id_banque', $request->banque))
            ->when($request->filled('date_debut'), fn($q) => $q->whereDate('date_entree', '>=', $request->date_debut))
            ->when($request->filled('date_fin'), fn($q) => $q->whereDate('date_entree', '<=', $request->date_fin))
            ->get()
            ->map(function ($e) {
                $encaisse = $e->type_entree === 'dette' ? $e->montant : $e->montant_net_encaisse;
                $reste = $e->type_entree === 'dette' ? 0 : $e->montant - $e->montant_net_encaisse;

                return [
                    'entite' => $e->nom_tiers ?: 'Entrees speciales',
                    'budget' => optional($e->budget)->libelle_ligne_budget ?? '-',
                    'ligne' => 'Entree speciale - ' . ucfirst($e->type_entree),
                    'donnee' => $e->libelle,
                    'prevu' => (float) $e->montant,
                    'facture' => 0,
                    'reduction' => 0,
                    'encaisse' => (float) $encaisse,
                    'reste' => (float) $reste,
                ];
            });

        $etat = $etat->concat($entreesSpeciales)->values();

        // =========================
        // 🔹 Groupement final par entité
        // =========================
        $etatGrouped = $etat->groupBy('entite');

        $title = 'Atterrissage budgétaire – Entrées';

        return view('Admin.Etats.atterrissage_budgetaire', compact(
            'etatGrouped',
            'annees',
            'entites',
            'caisses',
            'banques',
            'anneeNom',
            'entiteNom',
            'caisseNom',
            'banqueNom',
            'title'
        ));
    }

    public function atterrissageBudgetairetesto(Request $request)
    {
        $annees  = annee_academique::orderBy('nom', 'desc')->get();
        $entites = entite::orderBy('nom_entite')->get();
        $caisses = caisse::orderBy('nom_caisse')->get();

        $donnees = donnee_ligne_budgetaire_entree::with([
            'budget',
            'ligne_budgetaire_entrees',
            'facture_etudiants.entite',
            'facture_etudiants.reglement_etudiants',
            'facture_etudiants.reductions'
        ])->get();

        $etat = $donnees->map(function ($d) use ($request) {

            $factures = $d->facture_etudiants
                ->when($request->filled('annee'),
                    fn ($c) => $c->where('id_annee_academique', $request->annee))
            ->when($request->filled('entite'),
                fn ($c) => $c->where('id_entite', $request->entite));

        $factureTotal = $factures->sum('montant_total_facture');
        $reductionTotal = $factures->sum(fn($facture) => $facture->montant_reduction);

        $encaisseTotal = $factures
            ->flatMap->reglement_etudiants
            ->when($request->filled('caisse'),
                fn ($c) => $c->where('id_caisse', $request->caisse))
            ->sum('montant_reglement');

        return [
            'entite'   => optional($factures->first()?->entite)->nom_entite ?? '—',
            'budget'   => optional($d->budget)->libelle_ligne_budget ?? '—',
            'ligne'    => optional($d->ligne_budgetaire_entrees)
                ->libelle_ligne_budgetaire_entree ?? '—',
            'donnee'   => $d->donnee_ligne_budgetaire_entree,
            'prevu'    => (float) $d->montant,
            'facture'  => (float) $factureTotal,
            'reduction' => (float) $reductionTotal,
            'encaisse' => (float) $encaisseTotal,
            'reste'    => (float) max($factureTotal - $reductionTotal - $encaisseTotal, 0),
        ];
    });

        // 👉 REGROUPEMENT SI PAS D’ENTITÉ
        $etatGrouped = $request->filled('entite')
            ? $etat
            : $etat->groupBy('entite');

        $entiteNom = $request->filled('entite')
            ? optional(entite::find($request->entite))->nom_entite
            : null;
// =========================
// 🔹 Libellés d’en-tête
// =========================
        $anneeNom = null;
        if ($request->filled('annee')) {
            $anneeNom = optional(
                annee_academique::find($request->annee)
            )->nom;
        }

        $caisseNom = null;
        if ($request->filled('caisse')) {
            $caisseNom = optional(
                caisse::find($request->caisse)
            )->nom_caisse;
        }
        $title = 'Atterrissage budgétaire – Entrées';

        return view('Admin.Etats.atterrissage_budgetaire', compact(
            'etatGrouped',
            'entiteNom',
            'annees',
            'entites',
            'entiteNom',
            'anneeNom',
            'caisseNom',
            'caisses',
            'title'
        ));
    }

    public function atterrissageBudgetaireok(Request $request)
    {
        // =========================
        // 🔹 Données pour filtres
        // =========================
        $annees  = annee_academique::orderBy('nom', 'desc')->get();
        $entites = entite::orderBy('nom_entite')->get();
        $caisses = caisse::orderBy('nom_caisse')->get();

        // =========================
        // 🔹 Charger TOUTES les lignes prévues
        // =========================
        $donnees = donnee_ligne_budgetaire_entree::with([
            'budget',
            'ligne_budgetaire_entrees',
            'facture_etudiants' => function ($q) use ($request) {

                if ($request->filled('annee')) {
                    $q->where('id_annee_academique', $request->annee);
                }

                if ($request->filled('entite')) {
                    $q->where('id_entite', $request->entite);
                }

                if ($request->filled('date_debut')) {
                    $q->whereDate('date_facture', '>=', $request->date_debut);
                }

                if ($request->filled('date_fin')) {
                    $q->whereDate('date_facture', '<=', $request->date_fin);
                }
            },
            'facture_etudiants.reglement_etudiants' => function ($q) use ($request) {

                if ($request->filled('caisse')) {
                    $q->where('id_caisse', $request->caisse);
                }
            },
            'facture_etudiants.reductions'
        ])->get();

        // =========================
        // 🔹 Calculs d’atterrissage
        // =========================
        $etat = $donnees->map(function ($d) {

            $factureTotal = $d->facture_etudiants->sum('montant_total_facture');
            $reductionTotal = $d->facture_etudiants->sum(fn($facture) => $facture->montant_reduction);

            $encaisseTotal = $d->facture_etudiants
                ->flatMap->reglement_etudiants
                ->sum('montant_reglement');

            return [
                'budget'   => optional($d->budget)->libelle_ligne_budget ?? '—',
                'ligne'    => optional($d->ligne_budgetaire_entrees)->libelle_ligne_budgetaire_entree ?? '—',
                'donnee'   => $d->donnee_ligne_budgetaire_entree,
                'prevu'    => (float) $d->montant,
                'facture'  => (float) $factureTotal,   // 0 si aucune facture
                'reduction' => (float) $reductionTotal,
                'encaisse' => (float) $encaisseTotal,  // 0 si aucun règlement
                'reste'    => (float) max($factureTotal - $reductionTotal - $encaisseTotal, 0),
            ];
        });

        $title = 'Atterrissage budgétaire – Entrées';

        return view('Admin.Etats.atterrissage_budgetaire', compact(
            'etat',
            'annees',
            'entites',
            'caisses',
            'title'
        ));
    }

    public function atterrissageBudgetairetest(Request $request)
    {
        // =========================
        // 🔹 Données pour filtres
        // =========================
        $annees  = annee_academique::orderBy('nom', 'desc')->get();
        $entites = entite::orderBy('nom_entite')->get();
        $caisses = caisse::orderBy('nom_caisse')->get();

        // =========================
        // 🔹 Construction de la requête
        // =========================
        $query = donnee_ligne_budgetaire_entree::with([
            'budget',
            'ligne_budgetaire_entrees',
            'facture_etudiants.reglement_etudiants',
            'facture_etudiants.reductions'
        ]);

        // 👉 Filtre : Année académique
        if ($request->filled('annee')) {
            $query->whereHas('facture_etudiants', function ($q) use ($request) {
                $q->where('id_annee_academique', $request->annee);
            });
        }

        // 👉 Filtre : Entité
        if ($request->filled('entite')) {
            $query->whereHas('facture_etudiants', function ($q) use ($request) {
                $q->where('id_entite', $request->entite);
            });
        }

        // 👉 Filtre : Caisse (via règlements)
        if ($request->filled('caisse')) {
            $query->whereHas('facture_etudiants.reglement_etudiants', function ($q) use ($request) {
                $q->where('id_caisse', $request->caisse);
            });
        }

        // 👉 Filtre : période (dates de facture)
        if ($request->filled('date_debut')) {
            $query->whereHas('facture_etudiants', function ($q) use ($request) {
                $q->whereDate('date_facture', '>=', $request->date_debut);
            });
        }

        if ($request->filled('date_fin')) {
            $query->whereHas('facture_etudiants', function ($q) use ($request) {
                $q->whereDate('date_facture', '<=', $request->date_fin);
            });
        }

        // =========================
        // 🔹 Exécution + agrégation
        // =========================
        $etat = $query->get()->map(function ($d) {

            $factureTotal = $d->facture_etudiants
                ->sum('montant_total_facture');
            $reductionTotal = $d->facture_etudiants
                ->sum(fn($facture) => $facture->montant_reduction);

            $encaisseTotal = $d->facture_etudiants
                ->flatMap->reglement_etudiants
                ->sum('montant_reglement');

            return [
                'budget'   => optional($d->budget)->libelle_ligne_budget ?? '—',
                'ligne'    => optional($d->ligne_budgetaire_entrees)
                        ->libelle_ligne_budgetaire_entree ?? '—',
                'donnee'   => $d->donnee_ligne_budgetaire_entree,
                'prevu'    => (float) $d->montant,
                'facture'  => (float) $factureTotal,
                'reduction' => (float) $reductionTotal,
                'encaisse' => (float) $encaisseTotal,
                'reste'    => (float) ($factureTotal - $reductionTotal - $encaisseTotal),
            ];
        });

        // =========================
        // 🔹 Titre & vue
        // =========================
        $title = 'Atterrissage budgétaire – Entrées';

        return view('Admin.Etats.atterrissage_budgetaire', compact(
            'etat',
            'annees',
            'entites',
            'caisses',
            'title'
        ));
    }





    public function facturesReglements(Request $request)
    {

        // 🔹 Données pour filtres
        $annees  = \App\Models\annee_academique::orderBy('nom', 'desc')->get();
        $entites = \App\Models\entite::orderBy('nom_entite')->get();
        $caisses = \App\Models\caisse::orderBy('nom_caisse')->get();


        $specialites = specialite::orderBy('nom_specialite')->get();
        $niveaux     = niveau::orderBy('nom_niveau')->get();
        $cycles      = cycle::orderBy('nom_cycle')->get();
        $filieres    = filiere::orderBy('nom_filiere')->get();
        $query = facture_etudiant::with([
            'etudiants',
            'specialites',
            'budget',
            'ligne_budgetaire_entree',
            'element_ligne_budgetaire_entree',
            'donnee_ligne_budgetaire_entree',
            'reglement_etudiants.caisse',
            'reglement_etudiants.user',
            'entite',
            'user'
        ]);

        if ($request->filled('annee')) {
            $query->where('id_annee_academique', $request->annee);
        }

        if ($request->filled('entite')) {
            $query->where('id_entite', $request->entite);
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('date_facture', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('date_facture', '<=', $request->date_fin);
        }

        // 🔹 Filtre par caisse (via règlements)
        if ($request->filled('caisse')) {
            $query->whereHas('reglement_etudiants', function ($q) use ($request) {
                $q->where('id_caisse', $request->caisse);
            });
        }

        // 🔹 Filtre spécialité
        if ($request->filled('specialite')) {
            $query->where('id_specialite', $request->specialite);
        }

// 🔹 Filtre niveau (via spécialité)
        if ($request->filled('niveau')) {
            $query->whereHas('specialites', function ($q) use ($request) {
                $q->where('id_niveau', $request->niveau);
            });
        }

// 🔹 Filtre cycle (via spécialité)
        if ($request->filled('cycle')) {
            $query->whereHas('specialites', function ($q) use ($request) {
                $q->where('id_cycle', $request->cycle);
            });
        }

// 🔹 Filtre filière (via spécialité)
        if ($request->filled('filiere')) {
            $query->whereHas('specialites', function ($q) use ($request) {
                $q->where('id_filiere', $request->filiere);
            });
        }


        $factures = $query->get();

        // Groupement comptable
        $grouped = $factures->groupBy([
            fn ($f) => optional($f->specialites)->nom_specialite ?? '—',
            fn ($f) => optional($f->ligne_budgetaire_entree)->libelle_ligne_budgetaire_entree ?? '—',
            fn ($f) => optional($f->user)->name ?? '—',
        ]);

        return view('Admin.Etats.factures_reglements', compact('grouped', 'annees',
            'entites',
            'caisses','specialites',
            'niveaux',
            'cycles',
            'filieres'));
    }



    public function exportFacturesReglementsPdf(Request $request)
    {
        $query = facture_etudiant::with([
            'etudiants',
            'specialites',
            'budget',
            'ligne_budgetaire_entree',
            'element_ligne_budgetaire_entree',
            'donnee_ligne_budgetaire_entree',
            'reglement_etudiants.caisse',
            'reglement_etudiants.user',
            'entite',
            'user'
        ]);

        if ($request->filled('annee')) {
            $query->where('id_annee_academique', $request->annee);
        }

        if ($request->filled('entite')) {
            $query->where('id_entite', $request->entite);
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('date_facture', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('date_facture', '<=', $request->date_fin);
        }

        if ($request->filled('caisse')) {
            $query->whereHas('reglement_etudiants', function ($q) use ($request) {
                $q->where('id_caisse', $request->caisse);
            });
        }

        if ($request->filled('specialite')) {
            $query->where('id_specialite', $request->specialite);
        }

        if ($request->filled('niveau')) {
            $query->whereHas('specialites', function ($q) use ($request) {
                $q->where('id_niveau', $request->niveau);
            });
        }

        if ($request->filled('cycle')) {
            $query->whereHas('specialites', function ($q) use ($request) {
                $q->where('id_cycle', $request->cycle);
            });
        }

        if ($request->filled('filiere')) {
            $query->whereHas('specialites', function ($q) use ($request) {
                $q->where('id_filiere', $request->filiere);
            });
        }

        $factures = $query->get();

        // Même groupement que la vue HTML
        $grouped = $factures->groupBy([
            fn ($f) => optional($f->specialites)->nom_specialite ?? '—',
            fn ($f) => optional($f->ligne_budgetaire_entree)->libelle_ligne_budgetaire_entree ?? '—',
            fn ($f) => optional($f->user)->name ?? '—',
        ]);

        $pdf = Pdf::loadView(
            'Admin.Etats.factures_reglements_pdf',
            compact('grouped', 'request')
        );

        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('etat_factures_reglements.pdf');
    }




    public function exportFacturesReglementsExcel(Request $request)
    {
        $query = facture_etudiant::with([
            'etudiants',
            'specialites',
            'ligne_budgetaire_entree',
            'reglement_etudiants'
        ]);

        // filtres identiques
        if ($request->filled('annee')) {
            $query->where('id_annee_academique', $request->annee);
        }

        if ($request->filled('entite')) {
            $query->where('id_entite', $request->entite);
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('date_facture', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('date_facture', '<=', $request->date_fin);
        }

        if ($request->filled('specialite')) {
            $query->where('id_specialite', $request->specialite);
        }

        if ($request->filled('caisse')) {
            $query->whereHas('reglement_etudiants', function ($q) use ($request) {
                $q->where('id_caisse', $request->caisse);
            });
        }

        $factures = $query->get();

        return Excel::download(
            new FacturesReglementsExport($factures),
            'etat_factures_reglements.xlsx'
        );
    }

    public function exportSituationEtudiantPdf(Request $request)
    {
        $factures = facture_etudiant::with(['reglement_etudiants', 'reductions', 'specialites'])
            ->where('id_etudiant', $request->etudiant)
            ->when(
                $request->annee,
                fn ($q) => $q->where('id_annee_academique', $request->annee)
            )
            ->when(
                $request->specialite,
                fn ($q) => $q->where('id_specialite', $request->specialite)
            )
            ->when(
                $request->niveau,
                fn ($q) => $q->whereHas('specialites', fn ($s) => $s->where('id_niveau', $request->niveau))
            )
            ->get();

    $result = $factures->map(function ($f) {
        $encaisse = $f->reglement_etudiants->sum('montant_reglement');
        $reduction = $f->montant_reduction;

        return [
            'facture'  => $f->numero_facture,
            'date'     => $f->date_facture,
            'montant'  => $f->montant_total_facture,
            'reduction' => $reduction,
            'encaisse' => $encaisse,
            'reste'    => $f->montant_total_facture - $reduction - $encaisse,
        ];
    });

    $pdf = Pdf::loadView(
        'Admin.Etats.situation_etudiant_pdf',
        compact('result')
    )->setPaper('a4', 'portrait');

    return $pdf->download('situation_etudiant.pdf');
}
    public function situationEtudiant(Request $request)
    {
        // =========================
        // Données filtres
        // =========================
        $etudiants = \App\Models\Etudiant::orderBy('nom')->get();
        $annees    = \App\Models\annee_academique::orderBy('nom', 'desc')->get();
        $specialites = \App\Models\specialite::orderBy('nom_specialite')->get();
        $niveaux   = \App\Models\niveau::orderBy('nom_niveau')->get();
        $filieres  = \App\Models\filiere::orderBy('nom_filiere')->get();
        $cycles    = \App\Models\cycle::orderBy('nom_cycle')->get();
        $budgets   = \App\Models\Budget::orderBy('libelle_ligne_budget')->get();
        $lignes    = \App\Models\ligne_budgetaire_Entree::orderBy('libelle_ligne_budgetaire_entree')->get();

        $result = collect();

        // =========================
        // Requête conditionnelle
        // =========================
        if ($request->filled('etudiant')) {

            $query = \App\Models\facture_etudiant::with([
                'reglement_etudiants',
                'reductions',
                'specialites',
                'ligne_budgetaire_entree',
                'budget'
            ])->where('id_etudiant', $request->etudiant);

            if ($request->filled('annee')) {
                $query->where('id_annee_academique', $request->annee);
            }

            if ($request->filled('specialite')) {
                $query->where('id_specialite', $request->specialite);
            }

            if ($request->filled('niveau')) {
                $query->whereHas('specialites', fn ($q) =>
                $q->where('id_niveau', $request->niveau)
            );
        }

            if ($request->filled('cycle')) {
                $query->whereHas('specialites', fn ($q) =>
                $q->where('id_cycle', $request->cycle)
            );
        }

            if ($request->filled('filiere')) {
                $query->whereHas('specialites', fn ($q) =>
                $q->where('id_filiere', $request->filiere)
            );
        }

            if ($request->filled('budget')) {
                $query->where('id_budget', $request->budget);
            }

            if ($request->filled('ligne_budgetaire')) {
                $query->where('id_ligne_budgetaire_entree', $request->ligne_budgetaire);
            }

            // =========================
            // Transformation résultat
            // =========================
            $result = $query->get()->map(function ($f) {
                $encaisse = $f->reglement_etudiants->sum('montant_reglement');
                $reduction = $f->montant_reduction;

                return [
                    'facture'  => $f->numero_facture,
                    'date'     => $f->date_facture,
                    'montant'  => $f->montant_total_facture,
                    'reduction' => $reduction,
                    'encaisse' => $encaisse,
                    'reste'    => $f->montant_total_facture - $reduction - $encaisse,
                ];
            });
        }

        return view('Admin.Etats.situation_etudiant', compact(
            'etudiants',
            'annees',
            'specialites',
            'niveaux',
            'filieres',
            'cycles',
            'budgets',
            'lignes',
            'result'
        ));
    }

    public function situationEtudiant1(Request $request)
    {
        $factures = facture_etudiant::with('reglement_etudiants')
            ->where('id_etudiant', $request->etudiant)
            ->when($request->annee, fn($q) => $q->where('id_annee_academique', $request->annee))->get();

    $result = $factures->map(function ($f) {
        $encaisse = $f->reglement_etudiants->sum('montant_reglement');

        return [
            'facture'  => $f->numero_facture,
            'date'     => $f->date_facture,
            'montant'  => $f->montant_total_facture,
            'encaisse' => $encaisse,
            'reste'    => $f->montant_total_facture - $encaisse,
        ];
    });

    return view('Admin.Etats.situation_etudiant', compact('result'));
}
    public function atterrissageBudgetaire1(Request $request)
    {
        $donnees = donnee_ligne_budgetaire_entree::with([
            'budget',
            'ligne_budgetaire_entrees',
            'facture_etudiants.reglement_etudiants',
            'facture_etudiants.reductions'
        ])->get();

        $etat = $donnees->map(function ($d) {
            $facture = $d->facture_etudiants->sum('montant_total_facture');
            $reduction = $d->facture_etudiants->sum(fn($facture) => $facture->montant_reduction);

            $encaisse = $d->facture_etudiants
                ->flatMap->reglement_etudiants
                ->sum('montant_reglement');

            return [
                'budget'  => $d->budget->libelle_ligne_budget ?? '—',
                'ligne'   => $d->ligne_budgetaire_entrees->libelle_ligne_budgetaire_entree ?? '—',
                'donnee'  => $d->donnee_ligne_budgetaire_entree,
                'prevu'   => $d->montant,
                'facture' => $facture,
                'reduction' => $reduction,
                'encaisse'=> $encaisse,
                'reste'   => $facture - $reduction - $encaisse,
            ];
        });

        return view('Admin.Etats.atterrissage_budgetaire', compact('etat'));
    }
    protected function buildEtatBudgetaire(array $filters)
    {
        $query = \App\Models\donnee_ligne_budgetaire_entree::with([
            'budget',
            'ligne_budgetaire_entrees',
            'facture_etudiants.reglement_etudiants',
            'facture_etudiants.reductions'
        ]);

        if (!empty($filters['annee'])) {
            $query->whereHas('facture_etudiants', function ($q) use ($filters) {
                $q->where('id_annee_academique', $filters['annee']);
            });
        }

        if (!empty($filters['entite'])) {
            $query->whereHas('facture_etudiants', function ($q) use ($filters) {
                $q->where('id_entite', $filters['entite']);
            });
        }

        if (!empty($filters['caisse'])) {
            $query->whereHas('facture_etudiants.reglement_etudiants', function ($q) use ($filters) {
                $q->where('id_caisse', $filters['caisse']);
            });
        }

        if (!empty($filters['banque'])) {
            $query->whereHas('facture_etudiants.reglement_etudiants', function ($q) use ($filters) {
                $q->where('id_banque', $filters['banque']);
            });
        }

        return $query->get()->map(function ($d) use ($filters) {
            $facture = $d->facture_etudiants->sum('montant_total_facture');
            $reduction = $d->facture_etudiants->sum(fn($facture) => $facture->montant_reduction);

            $encaisse = $d->facture_etudiants
                ->flatMap->reglement_etudiants
                ->when(!empty($filters['caisse']), fn ($r) => $r->where('id_caisse', $filters['caisse']))
                ->when(!empty($filters['banque']), fn ($r) => $r->where('id_banque', $filters['banque']))
                ->sum('montant_reglement');

            return [
                'budget'  => optional($d->budget)->libelle_ligne_budget ?? '—',
                'ligne'   => optional($d->ligne_budgetaire_entrees)->libelle_ligne_budgetaire_entree ?? '—',
                'donnee'  => $d->donnee_ligne_budgetaire_entree,
                'prevu'   => (float) $d->montant,
                'facture' => (float) $facture,
                'reduction' => (float) $reduction,
                'encaisse'=> (float) $encaisse,
                'reste'   => (float) ($facture - $reduction - $encaisse),
            ];
        })->concat(
            entree_speciale::with(['budget', 'echeances'])
                ->where('statut', '!=', 'annule')
                ->when(!empty($filters['annee']), fn($q) => $q->where('id_annee_academique', $filters['annee']))
                ->when(!empty($filters['caisse']), fn($q) => $q->where('id_caisse', $filters['caisse']))
                ->when(!empty($filters['banque']), fn($q) => $q->where('id_banque', $filters['banque']))
                ->get()
                ->map(function ($e) {
                    $encaisse = $e->type_entree === 'dette' ? $e->montant : $e->montant_net_encaisse;
                    $reste = $e->type_entree === 'dette' ? 0 : $e->montant - $e->montant_net_encaisse;

                    return [
                        'entite' => $e->nom_tiers ?: 'Entrees speciales',
                        'budget' => optional($e->budget)->libelle_ligne_budget ?? '-',
                        'ligne' => 'Entree speciale - ' . ucfirst($e->type_entree),
                        'donnee' => $e->libelle,
                        'prevu' => (float) $e->montant,
                        'facture' => 0,
                        'reduction' => 0,
                        'encaisse'=> (float) $encaisse,
                        'reste' => (float) $reste,
                    ];
                })
        )->values();
    }


    public function exportEtatBudgetaireExcel(Request $request)
    {
        $etat = $this->buildEtatBudgetaire($request->all());

        return Excel::download(
            new EtatBudgetaireExport($etat),
            'etat_budgetaire_entrees.xlsx'
        );
    }

    public function exportEtatBudgetairePdf(Request $request)
    {
        // 1️⃣ Données PLATES
        $etat = $this->buildEtatBudgetaire($request->all());

        // 2️⃣ Groupement IDENTIQUE à l’écran
        $etatGrouped = $etat->groupBy(function ($e) {
            return $e['entite'] ?? '—';
        });

        // 3️⃣ Contexte (entité / année / caisse)
        $anneeNom  = $request->filled('annee')
            ? optional(annee_academique::find($request->annee))->nom
            : null;

        $entiteNom = $request->filled('entite')
            ? optional(entite::find($request->entite))->nom_entite
            : null;

        $caisseNom = $request->filled('caisse')
            ? optional(caisse::find($request->caisse))->nom_caisse
            : null;

        $banqueNom = $request->filled('banque')
            ? optional(banque::find($request->banque))->nom_banque
            : null;

        $pdf = PDF::loadView(
            'Admin.Etats.etat_budgetaire_entrees',
            compact(
                'etatGrouped',
                'anneeNom',
                'entiteNom',
                'caisseNom',
                'banqueNom'
            )
        )
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'margin-top'    => 10,
                'margin-bottom' => 10,
                'margin-left'   => 10,
                'margin-right'  => 10,
                'defaultFont'   => 'DejaVu Sans',
            ]);


        return $pdf->download('etat_budgetaire_entrees.pdf');
    }



    public function exportEtatBudgetairePdf1(Request $request)
    {
        $etat = $this->buildEtatBudgetaire($request->all());

        $pdf = PDF::loadView(
            'Admin.Etats.atterrissage_budgetaire_pdf',
            compact('etat')
        )->setPaper('a4', 'landscape');

        return $pdf->download('etat_budgetaire_entrees.pdf');
    }


}
