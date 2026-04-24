<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\facture_etudiant;
use Illuminate\Http\Request;

class ReportingController extends Controller
{
    public function facturesAvecReglements(Request $r)
    {
        $query = facture_etudiant::with([
            'etudiant',
            'reglements',
            'budget',
            'ligneBudgetaire',
            'elementBudgetaire',
            'donneeBudgetaire'
        ]);

        if ($r->filled('annee')) {
            $query->where('id_annee_academique', $r->annee);
        }

        if ($r->filled('date_debut')) {
            $query->whereDate('date_facture', '>=', $r->date_debut);
        }

        if ($r->filled('date_fin')) {
            $query->whereDate('date_facture', '<=', $r->date_fin);
        }

        $factures = $query->get();

        $grouped = $factures->groupBy([
            'id_specialite',
            'id_ligne_budgetaire_entree',
            'id_user'
        ]);

        return view('Admin.Reporting.factures', compact('grouped'));
    }
    public function atterrissageBudgetaire(Request $r)
    {
        $donnees = DonneeBudgetaireEntree::with([
            'budget',
            'ligneBudgetaireEntree',
            'donneeLigneBudgetaireEntrees.factures.reglements'
        ])->get();

        $result = $donnees->map(function ($d) {
            $factureTotal = $d->donneeLigneBudgetaireEntrees
                ->flatMap->factures
                ->sum('montant_total_facture');

            $reglementTotal = $d->donneeLigneBudgetaireEntrees
                ->flatMap->factures
                ->flatMap->reglements
                ->sum('montant_reglement');

            return [
                'budget' => $d->budget->libelle_ligne_budget,
                'ligne'  => $d->ligneBudgetaireEntree->libelle_ligne_budgetaire_entree,
                'donnee' => $d->donnee_ligne_budgetaire_entree,
                'prevu'  => $d->montant,
                'facture'=> $factureTotal,
                'encaisse'=> $reglementTotal,
                'reste'  => $factureTotal - $reglementTotal,
            ];
        });

        return view('Admin.Reporting.atterrissage', compact('result'));
    }


}
