<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\annee_academique;
use App\Models\bon_commandeok;
use App\Models\Budget;
use App\Models\donnee_ligne_budgetaire_sortie;
use App\Models\entree_speciale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class BonValidationController extends Controller
{
    private array $niveaux = [
        'pdg' => 'PDG',
        'daf' => 'DAF',
        'achats' => 'Achats',
    ];

    public function pdg(Request $request)
    {
        return $this->index($request, 'pdg');
    }

    public function daf(Request $request)
    {
        return $this->index($request, 'daf');
    }

    public function achats(Request $request)
    {
        return $this->index($request, 'achats');
    }

    public function index(Request $request, string $niveau)
    {
        abort_unless(array_key_exists($niveau, $this->niveaux), 404);
        $this->authorizeNiveau($niveau);

        return view('Admin.BonsValidation.index', [
            'bons' => $this->query($request, $niveau)->get(),
            'niveau' => $niveau,
            'niveauLabel' => $this->niveaux[$niveau],
            'budgets' => $niveau === 'daf' ? Budget::orderBy('libelle_ligne_budget')->get() : collect(),
            'annees' => $niveau === 'daf' ? annee_academique::orderByDesc('id')->get() : collect(),
            'entreesSpeciales' => $niveau === 'daf'
                ? entree_speciale::whereIn('type_entree', ['dette', 'don', 'apport'])
                    ->where('statut', 'actif')
                    ->orderByDesc('date_entree')
                    ->get()
                : collect(),
        ]);
    }

    public function valider(Request $request, string $niveau, bon_commandeok $bon)
    {
        abort_unless(array_key_exists($niveau, $this->niveaux), 404);
        $this->authorizeNiveau($niveau);
        abort_if($bon->statut_bon_code === 2, 422, 'Ce bon a deja ete refuse.');

        $validationField = "validation_{$niveau}";
        $refusField = "refus_{$niveau}";
        $motifField = "motif_refus_{$niveau}";
        $dateRefusField = "date_refus_{$niveau}";

        if ($niveau === 'daf') {
            $this->remplirImputationDaf($request, $bon);
        }

        $bon->{$validationField} = 1;
        $bon->{$refusField} = 0;
        $bon->{$motifField} = null;
        $bon->{$dateRefusField} = null;

        $this->updateStatut($bon);

        return back()->with('success', 'Validation '.$this->niveaux[$niveau].' effectuee.');
    }

    public function imputerDaf(Request $request, bon_commandeok $bon)
    {
        $this->authorizeNiveau('daf');
        abort_if($bon->statut_bon_code === 2, 422, 'Ce bon a deja ete refuse.');

        $this->remplirImputationDaf($request, $bon);
        $bon->save();

        return back()->with('success', 'Imputation DAF enregistree pour ce bon.');
    }

    public function refuser(Request $request, string $niveau, bon_commandeok $bon)
    {
        abort_unless(array_key_exists($niveau, $this->niveaux), 404);
        $this->authorizeNiveau($niveau);

        $data = $request->validate([
            'motif_refus' => 'required|string|max:2000',
        ]);

        $validationField = "validation_{$niveau}";
        $refusField = "refus_{$niveau}";
        $motifField = "motif_refus_{$niveau}";
        $dateRefusField = "date_refus_{$niveau}";

        $bon->{$validationField} = 0;
        $bon->{$refusField} = 1;
        $bon->{$motifField} = $data['motif_refus'];
        $bon->{$dateRefusField} = now();
        $bon->statuts = 2;
        $bon->date_validation = null;
        $bon->save();

        return back()->with('success', 'Bon refuse par '.$this->niveaux[$niveau].'.');
    }

    public function exportPdf(Request $request, string $niveau)
    {
        abort_unless(array_key_exists($niveau, $this->niveaux), 404);
        $this->authorizeNiveau($niveau);

        $pdf = Pdf::loadView('Admin.BonsValidation.pdf', [
            'bons' => $this->query($request, $niveau)->get(),
            'niveau' => $niveau,
            'niveauLabel' => $this->niveaux[$niveau],
            'dateDebut' => $request->date_debut,
            'dateFin' => $request->date_fin,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('bons_validation_'.$niveau.'.pdf');
    }

    private function query(Request $request, string $niveau)
    {
        $validationField = "validation_{$niveau}";
        $refusField = "refus_{$niveau}";

        return bon_commandeok::with(['personnels', 'entites', 'user'])
            ->when($request->date_debut, fn($q) => $q->whereDate('date_debut', '>=', $request->date_debut))
            ->when($request->date_fin, fn($q) => $q->whereDate('date_debut', '<=', $request->date_fin))
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('nom_bon_commande', 'like', '%'.$request->search.'%')
                        ->orWhere('description_bon_commande', 'like', '%'.$request->search.'%');
                });
            })
            ->when($request->statut === 'attente', fn($q) => $q->where($validationField, 0)->where($refusField, 0)->where('statuts', '<>', 2))
            ->when($request->statut === 'valide', fn($q) => $q->where($validationField, 1))
            ->when($request->statut === 'refuse', fn($q) => $q->where($refusField, 1))
            ->when(!$request->filled('statut'), fn($q) => $q->where(function ($sub) use ($validationField, $refusField) {
                $sub->where($validationField, 0)
                    ->orWhere($validationField, 1)
                    ->orWhere($refusField, 1);
            }))
            ->orderBy('date_debut', 'desc');
    }

    private function updateStatut(bon_commandeok $bon): void
    {
        if (
            $bon->validation_pdg &&
            $bon->validation_daf &&
            $bon->validation_achats &&
            $bon->validation_emetteur
        ) {
            $bon->statuts = 1;
            $bon->date_validation = $bon->date_validation ?: now();
        } else {
            $bon->statuts = 0;
            $bon->date_validation = null;
        }

        $bon->save();
    }

    private function remplirImputationDaf(Request $request, bon_commandeok $bon): void
    {
        $data = $request->validate([
            'id_budget' => 'required|integer|exists:budgets,id',
            'id_ligne_budgetaire_sortie' => 'required|integer|exists:ligne_budgetaire_sorties,id',
            'id_elements_ligne_budgetaire_sortie' => 'required|integer|exists:element_ligne_budgetaire_sorties,id',
            'id_donnee_budgetaire_sortie' => 'required|integer|exists:donnee_budgetaire_sorties,id',
            'id_donnee_ligne_budgetaire_sortie' => 'required|integer|exists:donnee_ligne_budgetaire_sorties,id',
            'id_annee_academique' => 'required|integer|exists:annee_academiques,id',
            'id_entree_speciale' => 'nullable|integer|exists:entree_speciales,id',
        ]);

        $donnee = donnee_ligne_budgetaire_sortie::where('id', $data['id_donnee_ligne_budgetaire_sortie'])
            ->where('id_budget', $data['id_budget'])
            ->where('id_ligne_budgetaire_sortie', $data['id_ligne_budgetaire_sortie'])
            ->where('id_element_ligne_budgetaire_sortie', $data['id_elements_ligne_budgetaire_sortie'])
            ->where('id_donnee_budgetaire_sortie', $data['id_donnee_budgetaire_sortie'])
            ->first();

        if (!$donnee) {
            abort(422, 'Incoherence budgetaire detectee sur l imputation DAF.');
        }

        $bon->fill([
            'id_budget' => $data['id_budget'],
            'id_ligne_budgetaire_sortie' => $data['id_ligne_budgetaire_sortie'],
            'id_elements_ligne_budgetaire_sortie' => $data['id_elements_ligne_budgetaire_sortie'],
            'id_donnee_budgetaire_sortie' => $data['id_donnee_budgetaire_sortie'],
            'id_donnee_ligne_budgetaire_sortie' => $data['id_donnee_ligne_budgetaire_sortie'],
            'id_annee_academique' => $data['id_annee_academique'],
            'id_entree_speciale' => $data['id_entree_speciale'] ?? null,
        ]);
    }

    private function authorizeNiveau(string $niveau): void
    {
        $routeName = "validation_bons.{$niveau}";

        if (!auth()->user()?->canAccessRoute($routeName)) {
            abort(403, "Acces non autorise pour la validation {$this->niveaux[$niveau]}.");
        }
    }
}
