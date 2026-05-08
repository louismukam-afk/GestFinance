<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\annee_academique;
use App\Models\Budget;
use App\Models\donnee_budgetaire_entree;
use App\Models\donnee_ligne_budgetaire_entree;
use App\Models\element_ligne_budgetaire_entree;
use App\Models\entite;
use App\Models\Etudiant;
use App\Models\facture_etudiant;
use App\Models\FactureRattrapageLigne;
use App\Models\ProgrammeSpecialite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FactureRattrapageController extends Controller
{
    public function index(Request $request)
    {
        $query = facture_etudiant::with([
            'etudiants', 'Annee_academique', 'entite', 'budget',
            'ligne_budgetaire_entree', 'element_ligne_budgetaire_entree',
            'donnee_budgetaire_entree', 'donnee_ligne_budgetaire_entree',
            'reglement_etudiants', 'lignes_rattrapage.matiere',
        ])->where('type_facture', 2);

        if ($request->filled('id_etudiant')) {
            $query->where('id_etudiant', $request->id_etudiant);
        }

        if ($request->filled('id_annee_academique')) {
            $query->where('id_annee_academique', $request->id_annee_academique);
        }

        if ($request->filled('id_budget')) {
            $query->where('id_budget', $request->id_budget);
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('date_facture', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('date_facture', '<=', $request->date_fin);
        }

        $factures = $query->orderByDesc('date_facture')->get();

        if ($request->filled('statut_paiement')) {
            $factures = $factures->filter(function ($facture) use ($request) {
                $encaisse = $facture->reglement_etudiants->sum('montant_reglement');
                $total = (float) $facture->montant_total_facture;

                return match ($request->statut_paiement) {
                    'paye' => $encaisse >= $total && $total > 0,
                    'partiel' => $encaisse > 0 && $encaisse < $total,
                    'non_paye' => $encaisse <= 0,
                    default => true,
                };
            })->values();
        }

        return view('Admin.FacturesRattrapage.index', [
            'title' => 'Factures de rattrapage',
            'factures' => $factures,
            'etudiants' => Etudiant::orderBy('nom')->get(),
            'annees' => annee_academique::orderByDesc('created_at')->get(),
            'budgets' => Budget::orderByDesc('created_at')->get(),
            'filters' => $request->all(),
        ]);
    }

    public function create()
    {
        return redirect()
            ->route('factures_rattrapage.index')
            ->with('error', "La facture de rattrapage doit etre creee depuis une facture existante de l'etudiant.");
    }

    public function createFromFacture(facture_etudiant $facture)
    {
        if ((int) $facture->type_facture === 2) {
            return redirect()
                ->route('factures_rattrapage.show', $facture->id)
                ->with('error', 'Cette facture est deja une facture de rattrapage.');
        }

        $facture->load([
            'etudiants', 'Annee_academique', 'entite', 'budget',
            'ligne_budgetaire_entree', 'element_ligne_budgetaire_entree',
            'donnee_budgetaire_entree', 'donnee_ligne_budgetaire_entree',
            'specialites', 'filieres', 'cycles', 'niveaux',
        ]);

        $programmes = ProgrammeSpecialite::with('matiere')
            ->where('id_specialite', $facture->id_specialite)
            ->where('id_cycle', $facture->id_cycle)
            ->where('id_filiere', $facture->id_filiere)
            ->where('id_niveau', $facture->id_niveau)
            ->where('id_annee_academique', $facture->id_annee_academique)
            ->where('id_entite', $facture->id_entite)
            ->orderBy('semestre')
            ->orderBy('id')
            ->get();

        return view('Admin.FacturesRattrapage.create', [
            'title' => 'Nouvelle facture de rattrapage',
            'sourceFacture' => $facture,
            'programmes' => $programmes,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_facture_source' => 'required|integer|exists:facture_etudiants,id',
            'date_facture' => 'required|date',
            'matieres' => 'required|array|min:1',
            'matieres.*.selected' => 'nullable|boolean',
            'matieres.*.id_programme_specialite' => 'required|integer|exists:programme_specialites,id',
            'matieres.*.prix_unitaire' => 'nullable|numeric|min:0',
            'matieres.*.quantite' => 'nullable|integer|min:1',
            'matieres.*.observation' => 'nullable|string',
        ]);

        $data['matieres'] = collect($data['matieres'])
            ->filter(fn($line) => !empty($line['selected']))
            ->values()
            ->all();

        if (empty($data['matieres'])) {
            return back()->withInput()->withErrors([
                'matieres' => 'Selectionne au moins une matiere a rattraper.',
            ]);
        }

        return DB::transaction(function () use ($data) {
            $reference = facture_etudiant::findOrFail($data['id_facture_source']);

            $montantTotal = collect($data['matieres'])->sum(function ($line) {
                return (float)($line['prix_unitaire'] ?? 0) * (int)($line['quantite'] ?? 1);
            });

            $numero = ((int) facture_etudiant::where('id_annee_academique', $reference->id_annee_academique)->max('numero_facture')) + 1;

            $facture = facture_etudiant::create([
                'id_etudiant' => $reference->id_etudiant,
                'id_cycle' => (int)($reference->id_cycle ?? 0),
                'id_filiere' => (int)($reference->id_filiere ?? 0),
                'id_niveau' => (int)($reference->id_niveau ?? 0),
                'id_specialite' => (int)($reference->id_specialite ?? 0),
                'id_scolarite' => (int)($reference->id_scolarite ?? 0),
                'id_frais' => 0,
                'id_tranche_scolarite' => 0,
                'id_budget' => $reference->id_budget,
                'id_ligne_budgetaire_entree' => $reference->id_ligne_budgetaire_entree,
                'id_element_ligne_budgetaire_entree' => $reference->id_element_ligne_budgetaire_entree,
                'id_donnee_budgetaire_entree' => $reference->id_donnee_budgetaire_entree,
                'id_donnee_ligne_budgetaire_entree' => $reference->id_donnee_ligne_budgetaire_entree,
                'numero_facture' => $numero,
                'date_facture' => $data['date_facture'],
                'id_annee_academique' => $reference->id_annee_academique,
                'id_entite' => $reference->id_entite,
                'type_facture' => 2,
                'montant_total_facture' => $montantTotal,
                'id_user' => Auth::id() ?? 0,
            ]);

            foreach ($data['matieres'] as $line) {
                $programme = ProgrammeSpecialite::with('matiere')->findOrFail($line['id_programme_specialite']);

                FactureRattrapageLigne::create([
                    'id_facture_etudiant' => $facture->id,
                    'id_matiere' => $programme->id_matiere,
                    'id_programme_specialite' => $programme->id,
                    'prix_unitaire' => (float)($line['prix_unitaire'] ?? 0),
                    'quantite' => (int)($line['quantite'] ?? 1),
                    'montant' => (float)($line['prix_unitaire'] ?? 0) * (int)($line['quantite'] ?? 1),
                    'observation' => $line['observation'] ?? null,
                    'id_user' => Auth::id() ?? 0,
                ]);
            }

            $this->refreshMontantsPrevisionnelsEntree(
                $facture->id_donnee_budgetaire_entree,
                $facture->id_donnee_ligne_budgetaire_entree
            );

            return redirect()
                ->route('factures_rattrapage.show', $facture->id)
                ->with('success', 'Facture de rattrapage creee avec succes.');
        });
    }

    public function show($id)
    {
        $facture = facture_etudiant::with([
            'etudiants', 'Annee_academique', 'entite', 'budget',
            'ligne_budgetaire_entree', 'element_ligne_budgetaire_entree',
            'donnee_budgetaire_entree', 'donnee_ligne_budgetaire_entree',
            'reglement_etudiants', 'lignes_rattrapage.matiere',
        ])->where('type_facture', 2)->findOrFail($id);

        return view('Admin.FacturesRattrapage.show', [
            'title' => 'Detail facture de rattrapage',
            'facture' => $facture,
            'totalPaye' => $facture->reglement_etudiants->sum('montant_reglement'),
        ]);
    }

    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            $facture = facture_etudiant::where('type_facture', 2)->findOrFail($id);
            $donneeBudgetaireId = $facture->id_donnee_budgetaire_entree;
            $donneeLigneId = $facture->id_donnee_ligne_budgetaire_entree;

            $facture->lignes_rattrapage()->delete();
            $facture->delete();

            $this->refreshMontantsPrevisionnelsEntree($donneeBudgetaireId, $donneeLigneId);

            return redirect()->route('factures_rattrapage.index')->with('success', 'Facture de rattrapage supprimee.');
        });
    }

    private function refreshMontantsPrevisionnelsEntree(?int $donneeBudgetaireId, ?int $donneeLigneId = null): void
    {
        if ($donneeLigneId) {
            donnee_ligne_budgetaire_entree::where('id', $donneeLigneId)->update([
                'montant' => facture_etudiant::where('id_donnee_ligne_budgetaire_entree', $donneeLigneId)->sum('montant_total_facture'),
            ]);
        }

        if ($donneeBudgetaireId) {
            donnee_budgetaire_entree::where('id', $donneeBudgetaireId)->update([
                'montant' => facture_etudiant::where('id_donnee_budgetaire_entree', $donneeBudgetaireId)->sum('montant_total_facture'),
            ]);
        }
    }
}
