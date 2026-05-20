<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ViewExport;
use App\Http\Controllers\Controller;
use App\Models\annee_academique;
use App\Models\Budget;
use App\Models\entite;
use App\Models\facture_etudiant;
use App\Models\reduction_facture;
use App\Models\specialite;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReductionFactureController extends Controller
{
    public function index(Request $request)
    {
        return view('Admin.ReductionsFactures.index', $this->data($request));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_facture_etudiant' => 'required|integer',
            'montant' => 'required|numeric|min:1',
            'motif' => 'nullable|string|max:255',
            'date_reduction' => 'required|date',
            'observations' => 'nullable|string',
        ]);

        $facture = facture_etudiant::with(['reductions', 'reglement_etudiants'])->findOrFail($validated['id_facture_etudiant']);
        $totalReductions = (float) $facture->reductions->sum('montant');
        $totalPaye = (float) $facture->reglement_etudiants->sum('montant_reglement');
        $resteFacture = (float) $facture->montant_total_facture - $totalReductions;
        $netApresReduction = (float) $facture->montant_total_facture - $totalReductions - (float) $validated['montant'];

        if ($validated['montant'] > $resteFacture) {
            return back()->withInput()->with('error', 'La reduction depasse le reste disponible sur cette facture.');
        }

        if ($netApresReduction < $totalPaye) {
            return back()->withInput()->with('error', 'Reduction impossible : les reglements deja enregistres depasseraient le net a payer.');
        }

        reduction_facture::create([
            'id_facture_etudiant' => $facture->id,
            'id_etudiant' => $facture->id_etudiant,
            'id_entite' => $facture->id_entite,
            'id_specialite' => $facture->id_specialite,
            'id_annee_academique' => $facture->id_annee_academique,
            'id_budget' => $facture->id_budget,
            'montant' => $validated['montant'],
            'motif' => $validated['motif'] ?? null,
            'date_reduction' => $validated['date_reduction'],
            'observations' => $validated['observations'] ?? null,
            'id_user' => auth()->id(),
        ]);

        return back()->with('success', 'Reduction enregistree avec succes.');
    }

    public function update(Request $request, $id)
    {
        $reduction = reduction_facture::findOrFail($id);
        $validated = $request->validate([
            'montant' => 'required|numeric|min:1',
            'motif' => 'nullable|string|max:255',
            'date_reduction' => 'required|date',
            'observations' => 'nullable|string',
        ]);

        $facture = facture_etudiant::with(['reductions', 'reglement_etudiants'])->findOrFail($reduction->id_facture_etudiant);
        $autresReductions = (float) $facture->reductions->where('id', '!=', $reduction->id)->sum('montant');
        $totalPaye = (float) $facture->reglement_etudiants->sum('montant_reglement');
        $resteFacture = (float) $facture->montant_total_facture - $autresReductions;
        $netApresReduction = (float) $facture->montant_total_facture - $autresReductions - (float) $validated['montant'];

        if ($validated['montant'] > $resteFacture) {
            return back()->withInput()->with('error', 'La reduction depasse le reste disponible sur cette facture.');
        }

        if ($netApresReduction < $totalPaye) {
            return back()->withInput()->with('error', 'Reduction impossible : les reglements deja enregistres depasseraient le net a payer.');
        }

        $reduction->update($validated);

        return back()->with('success', 'Reduction modifiee avec succes.');
    }

    public function destroy($id)
    {
        reduction_facture::findOrFail($id)->delete();

        return back()->with('success', 'Reduction supprimee avec succes.');
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(
            new ViewExport('Admin.ReductionsFactures.excel', $this->data($request)),
            'reductions_factures.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $pdf = Pdf::loadView('Admin.ReductionsFactures.pdf', $this->data($request))
            ->setPaper('a4', 'landscape');

        return $pdf->download('reductions_factures.pdf');
    }

    private function data(Request $request): array
    {
        $reductions = $this->query($request)->get();
        $factureSelectionnee = $request->filled('id_facture_etudiant')
            ? facture_etudiant::with([
                'etudiants',
                'entite',
                'cycles',
                'filieres',
                'niveaux',
                'specialites',
                'Annee_academique',
                'budget',
                'ligne_budgetaire_entree',
                'element_ligne_budgetaire_entree',
                'donnee_budgetaire_entree',
                'donnee_ligne_budgetaire_entree',
                'reductions',
            ])->find($request->id_facture_etudiant)
            : null;

        return [
            'reductions' => $reductions,
            'factures' => facture_etudiant::with(['etudiants', 'entite', 'specialites', 'Annee_academique', 'budget', 'reductions'])
                ->when($factureSelectionnee, fn($q) => $q->where('id', $factureSelectionnee->id))
                ->orderByDesc('date_facture')
                ->get(),
            'factureSelectionnee' => $factureSelectionnee,
            'specialites' => specialite::orderBy('nom_specialite')->get(),
            'entites' => entite::orderBy('nom_entite')->get(),
            'annees' => annee_academique::orderBy('nom', 'desc')->get(),
            'budgets' => Budget::orderBy('libelle_ligne_budget')->get(),
            'totalReductions' => $reductions->sum('montant'),
            'dateDebut' => $request->date_debut,
            'dateFin' => $request->date_fin,
        ];
    }

    private function query(Request $request)
    {
        return reduction_facture::with([
            'facture',
            'etudiant',
            'entite',
            'specialite',
            'annee_academique',
            'budget',
            'user',
        ])
            ->when($request->filled('id_specialite'), fn($q) => $q->where('id_specialite', $request->id_specialite))
            ->when($request->filled('id_entite'), fn($q) => $q->where('id_entite', $request->id_entite))
            ->when($request->filled('id_annee_academique'), fn($q) => $q->where('id_annee_academique', $request->id_annee_academique))
            ->when($request->filled('id_budget'), fn($q) => $q->where('id_budget', $request->id_budget))
            ->when($request->filled('id_facture_etudiant'), fn($q) => $q->where('id_facture_etudiant', $request->id_facture_etudiant))
            ->when($request->filled('date_debut'), fn($q) => $q->whereDate('date_reduction', '>=', $request->date_debut))
            ->when($request->filled('date_fin'), fn($q) => $q->whereDate('date_reduction', '<=', $request->date_fin))
            ->orderByDesc('date_reduction');
    }
}
