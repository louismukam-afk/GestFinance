<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ViewExport;
use App\Http\Controllers\Controller;
use App\Models\annee_academique;
use App\Models\budget;
use App\Models\cycle;
use App\Models\donnee_budgetaire_entree;
use App\Models\donnee_ligne_budgetaire_entree;
use App\Models\element_ligne_budgetaire_entree;
use App\Models\entite;
use App\Models\facture_etudiant;
use App\Models\filiere;
use App\Models\ligne_budgetaire_Entree;
use App\Models\niveau;
use App\Models\scolarite;
use App\Models\specialite;
use App\Models\tranche_scolarite;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class EtatEtudiantScolariteController extends Controller
{
    public function index()
    {
        return view('Admin.Etudiant.etat_scolarite.index', $this->filters());
    }

    public function data(Request $request)
    {
        $data = $this->buildData($request);

        return view('Admin.Etudiant.etat_scolarite.table', $data);
    }

    public function exportPdf(Request $request)
    {
        $data = array_merge($this->buildData($request), [
            'dateDebut' => $request->date_debut,
            'dateFin' => $request->date_fin,
        ]);

        $pdf = Pdf::loadView('Admin.Etudiant.etat_scolarite.pdf', $data)
            ->setPaper('a3', 'landscape');

        return $pdf->download('etat_scolarite_etudiants.pdf');
    }

    public function exportExcel(Request $request)
    {
        $data = array_merge($this->buildData($request), [
            'dateDebut' => $request->date_debut,
            'dateFin' => $request->date_fin,
        ]);

        return Excel::download(
            new ViewExport('Admin.Etudiant.etat_scolarite.pdf', $data),
            'etat_scolarite_etudiants.xlsx'
        );
    }

    public function lignesBudgetaires(budget $budget)
    {
        return response()->json(
            ligne_budgetaire_Entree::whereHas('donnee_ligne_budgetaire_entrees', fn($q) => $q->where('id_budget', $budget->id))
                ->orderBy('libelle_ligne_budgetaire_entree')
                ->get(['id', 'libelle_ligne_budgetaire_entree'])
        );
    }

    public function elementsBudgetaires(ligne_budgetaire_Entree $ligne)
    {
        return response()->json(
            element_ligne_budgetaire_entree::where('id_ligne_budgetaire_entree', $ligne->id)
                ->orderBy('libelle_elements_ligne_budgetaire_entree')
                ->get(['id', 'libelle_elements_ligne_budgetaire_entree'])
        );
    }

    public function donneesBudgetaires(element_ligne_budgetaire_entree $element)
    {
        return response()->json(
            donnee_ligne_budgetaire_entree::where('id_element_ligne_budgetaire_entree', $element->id)
                ->orderBy('donnee_ligne_budgetaire_entree')
                ->get(['id', 'donnee_ligne_budgetaire_entree'])
        );
    }

    public function donneesBudgetParLigne(ligne_budgetaire_Entree $ligne)
    {
        return response()->json(
            donnee_budgetaire_entree::where('id_ligne_budgetaire_entree', $ligne->id)
                ->orderBy('donnee_ligne_budgetaire_entree')
                ->get(['id', 'donnee_ligne_budgetaire_entree'])
        );
    }

    public function scolarites(Request $request)
    {
        return response()->json(
            scolarite::when($request->id_cycle, fn($q) => $q->where('id_cycle', $request->id_cycle))
                ->when($request->id_filiere, fn($q) => $q->where('id_filiere', $request->id_filiere))
                ->when($request->id_niveau, fn($q) => $q->where('id_niveau', $request->id_niveau))
                ->when($request->id_specialite, fn($q) => $q->where('id_specialite', $request->id_specialite))
                ->get(['id', 'montant_total', 'inscription', 'type_scolarite'])
        );
    }

    public function tranches(scolarite $scolarite)
    {
        return response()->json(
            tranche_scolarite::where('id_scolarite', $scolarite->id)
                ->orderBy('date_limite')
                ->get(['id', 'nom_tranche', 'montant_tranche'])
        );
    }

    private function filters(): array
    {
        return [
            'annees' => annee_academique::orderBy('nom', 'desc')->get(),
            'entites' => entite::orderBy('nom_entite')->get(),
            'cycles' => cycle::orderBy('nom_cycle')->get(),
            'filieres' => filiere::orderBy('nom_filiere')->get(),
            'niveaux' => niveau::orderBy('nom_niveau')->get(),
            'specialites' => specialite::orderBy('nom_specialite')->get(),
            'budgets' => budget::orderBy('libelle_ligne_budget')->get(),
            'scolarites' => scolarite::with(['cycles', 'filiere', 'niveaux', 'specialites'])->get(),
            'tranches' => tranche_scolarite::orderBy('nom_tranche')->get(),
        ];
    }

    private function buildData(Request $request): array
    {
        $typeRapport = $request->type_rapport ?? 'inscrits';
        $factures = $this->facturesQuery($request)->get();

        $rows = $factures
            ->groupBy('id_etudiant')
            ->map(function ($studentFactures) use ($typeRapport, $request) {
                $first = $studentFactures->first();
                $factureTotal = $studentFactures->sum('montant_total_facture');
                $reglementTotal = $studentFactures->sum(fn($facture) => $facture->reglement_etudiants->sum('montant_reglement'));
                $reste = $factureTotal - $reglementTotal;

                $statutPaiement = $factureTotal > 0 && $reglementTotal >= $factureTotal ? 'Payé' : 'Non payé';

                return [
                    'etudiant' => $first->etudiants,
                    'matricule' => $first->etudiants->matricule ?? '',
                    'cycle' => $first->cycles->nom_cycle ?? '',
                    'filiere' => $first->filieres->nom_filiere ?? '',
                    'niveau' => $first->niveaux->nom_niveau ?? '',
                    'specialite' => $first->specialites->nom_specialite ?? '',
                    'annee' => $first->Annee_academique->nom ?? '',
                    'entite' => $first->entite->nom_entite ?? '',
                    'budget' => $first->budget->libelle_ligne_budget ?? '',
                    'ligne' => $first->ligne_budgetaire_entree->libelle_ligne_budgetaire_entree ?? '',
                    'element' => $first->element_ligne_budgetaire_entree->libelle_elements_ligne_budgetaire_entree ?? '',
                    'donnee_budgetaire' => $first->donnee_budgetaire_entree->donnee_ligne_budgetaire_entree ?? '',
                    'donnee_ligne' => $first->donnee_ligne_budgetaire_entree->donnee_ligne_budgetaire_entree ?? '',
                    'tranche' => $first->tranche_scolarites->nom_tranche ?? '',
                    'facture' => $factureTotal,
                    'paye' => $reglementTotal,
                    'reste' => $reste,
                    'statut_paiement' => $statutPaiement,
                ];
            })
            ->filter(function ($row) use ($typeRapport) {
                return match ($typeRapport) {
                    'tranche_paye', 'scolarite_payee' => $row['statut_paiement'] === 'Payé',
                    'tranche_non_paye', 'scolarite_non_payee' => $row['statut_paiement'] !== 'Payé',
                    default => true,
                };
            })
            ->values();

        return [
            'rows' => $rows,
            'typeRapport' => $typeRapport,
            'totalFacture' => $rows->sum('facture'),
            'totalPaye' => $rows->sum('paye'),
            'totalReste' => $rows->sum('reste'),
        ];
    }

    private function facturesQuery(Request $request)
    {
        return facture_etudiant::with([
            'etudiants',
            'cycles',
            'niveaux',
            'filieres',
            'specialites',
            'scolarites',
            'tranche_scolarites',
            'Annee_academique',
            'entite',
            'budget',
            'ligne_budgetaire_entree',
            'element_ligne_budgetaire_entree',
            'donnee_budgetaire_entree',
            'donnee_ligne_budgetaire_entree',
            'reglement_etudiants' => function ($q) use ($request) {
                $q->when($request->date_debut, fn($r) => $r->whereDate('date_reglement', '>=', $request->date_debut))
                    ->when($request->date_fin, fn($r) => $r->whereDate('date_reglement', '<=', $request->date_fin));
            },
        ])
            ->when($request->date_debut, fn($q) => $q->whereDate('date_facture', '>=', $request->date_debut))
            ->when($request->date_fin, fn($q) => $q->whereDate('date_facture', '<=', $request->date_fin))
            ->when($request->id_annee_academique, fn($q) => $q->where('id_annee_academique', $request->id_annee_academique))
            ->when($request->id_entite, fn($q) => $q->where('id_entite', $request->id_entite))
            ->when($request->id_cycle, fn($q) => $q->where('id_cycle', $request->id_cycle))
            ->when($request->id_filiere, fn($q) => $q->where('id_filiere', $request->id_filiere))
            ->when($request->id_niveau, fn($q) => $q->where('id_niveau', $request->id_niveau))
            ->when($request->id_specialite, fn($q) => $q->where('id_specialite', $request->id_specialite))
            ->when($request->id_scolarite, fn($q) => $q->where('id_scolarite', $request->id_scolarite))
            ->when($request->id_tranche_scolarite, fn($q) => $q->where('id_tranche_scolarite', $request->id_tranche_scolarite))
            ->when($request->id_budget, fn($q) => $q->where('id_budget', $request->id_budget))
            ->when($request->id_ligne_budgetaire_entree, fn($q) => $q->where('id_ligne_budgetaire_entree', $request->id_ligne_budgetaire_entree))
            ->when($request->id_element_ligne_budgetaire_entree, fn($q) => $q->where('id_element_ligne_budgetaire_entree', $request->id_element_ligne_budgetaire_entree))
            ->when($request->id_donnee_budgetaire_entree, fn($q) => $q->where('id_donnee_budgetaire_entree', $request->id_donnee_budgetaire_entree))
            ->when($request->id_donnee_ligne_budgetaire_entree, fn($q) => $q->where('id_donnee_ligne_budgetaire_entree', $request->id_donnee_ligne_budgetaire_entree))
            ->orderBy('date_facture', 'desc');
    }
}
