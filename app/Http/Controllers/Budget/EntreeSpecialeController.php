<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Models\annee_academique;
use App\Models\Budget;
use App\Models\caisse;
use App\Models\entree_speciale;
use App\Models\entree_speciale_echeance;
use App\Models\Transfert_caisse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EntreeSpecialeController extends Controller
{
    private array $types = [
        'dette' => 'Dette',
        'don' => 'Don',
        'apport' => 'Apport en caisse',
    ];

    public function index(Request $request)
    {
        $entrees = $this->filteredQuery($request)
            ->with(['caisse', 'budget', 'annee_utilisation', 'annee_remboursement', 'echeances'])
            ->orderByDesc('date_entree')
            ->get();

        return view('Budget.entrees_speciales.index', $this->viewData([
            'entrees' => $entrees,
            'total' => $entrees->sum(fn($entree) => $entree->montant_net_encaisse),
            'totalBrut' => $entrees->sum('montant'),
            'totalRembourse' => $entrees->sum(fn($entree) => $entree->montant_rembourse),
            'rappels' => $this->echeancesARappeler(),
            'title' => 'Entrees speciales',
        ]));
    }

    public function create()
    {
        return view('Budget.entrees_speciales.create', $this->viewData([
            'entree' => new entree_speciale(['type_entree' => 'dette']),
            'title' => 'Creation entree speciale',
        ]));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        DB::transaction(function () use ($request, $data) {
            $data['id_user'] = Auth::id();
            $data['code_entree'] = $data['code_entree'] ?: $this->generateCode();

            $entree = entree_speciale::create($data);

            $this->syncEcheances($entree, $request);
            $this->syncTransfertApprovisionnement($entree);
        });

        return redirect()
            ->route('entrees_speciales.index')
            ->with('success', 'Entree speciale enregistree avec succes.');
    }

    public function show($id)
    {
        $entree = entree_speciale::with(['caisse', 'budget', 'annee_utilisation', 'annee_remboursement', 'echeances'])
            ->findOrFail($id);

        return view('Budget.entrees_speciales.show', $this->viewData([
            'entree' => $entree,
            'title' => 'Detail entree speciale',
        ]));
    }

    public function edit($id)
    {
        $entree = entree_speciale::with('echeances')->findOrFail($id);

        return view('Budget.entrees_speciales.edit', $this->viewData([
            'entree' => $entree,
            'title' => 'Modification entree speciale',
        ]));
    }

    public function update(Request $request, $id)
    {
        $entree = entree_speciale::findOrFail($id);
        $data = $this->validatedData($request);

        DB::transaction(function () use ($request, $entree, $data) {
            $data['id_user'] = Auth::id();
            $data['code_entree'] = $data['code_entree'] ?: $entree->code_entree;

            $entree->update($data);

            $this->syncEcheances($entree, $request);
            $this->syncTransfertApprovisionnement($entree);
        });

        return redirect()
            ->route('entrees_speciales.show', $entree->id)
            ->with('success', 'Entree speciale modifiee avec succes.');
    }

    public function destroy($id)
    {
        $entree = entree_speciale::findOrFail($id);

        DB::transaction(function () use ($entree) {
            $entree->echeances()->delete();
            $entree->transfert_caisse()->delete();
            $entree->delete();
        });

        return redirect()
            ->route('entrees_speciales.index')
            ->with('success', 'Entree speciale supprimee avec succes.');
    }

    public function rappels()
    {
        return view('Budget.entrees_speciales.rappels', $this->viewData([
            'rappels' => $this->echeancesARappeler(),
            'title' => 'Rappels des echeances',
        ]));
    }

    public function remboursements(Request $request)
    {
        $query = entree_speciale_echeance::with([
            'entree_speciale.budget',
            'entree_speciale.caisse',
            'entree_speciale.annee_utilisation',
            'entree_speciale.annee_remboursement',
            'annee_paiement',
            'caisse_paiement',
        ])->whereHas('entree_speciale', fn($q) => $q->where('type_entree', 'dette'));

        if ($request->filled('date_debut')) {
            $query->whereDate('date_echeance', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('date_echeance', '<=', $request->date_fin);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('id_annee_academique_remboursement')) {
            $query->whereHas('entree_speciale', fn($q) =>
                $q->where('id_annee_academique_remboursement', $request->id_annee_academique_remboursement)
            );
        }

        if ($request->filled('id_annee_academique_utilisation')) {
            $query->whereHas('entree_speciale', fn($q) =>
                $q->where('id_annee_academique_utilisation', $request->id_annee_academique_utilisation)
            );
        }

        if ($request->filled('id_annee_academique_paiement')) {
            $query->where('id_annee_academique_paiement', $request->id_annee_academique_paiement);
        }

        if ($request->filled('id_caisse_paiement')) {
            $query->where('id_caisse_paiement', $request->id_caisse_paiement);
        }

        if ($request->filled('id_budget')) {
            $query->whereHas('entree_speciale', fn($q) => $q->where('id_budget', $request->id_budget));
        }

        if ($request->filled('creancier')) {
            $query->whereHas('entree_speciale', fn($q) =>
                $q->where('nom_tiers', 'like', '%' . $request->creancier . '%')
            );
        }

        $echeances = $query->orderBy('date_echeance')->get();

        return view('Budget.entrees_speciales.remboursements', $this->viewData([
            'echeances' => $echeances,
            'title' => 'Remboursements des dettes',
        ]));
    }

    public function payerEcheance(Request $request, $id)
    {
        $echeance = entree_speciale_echeance::with('entree_speciale.echeances')->findOrFail($id);

        $data = $request->validate([
            'date_paiement' => 'required|date',
            'montant_paye' => 'required|numeric|min:0',
            'id_caisse_paiement' => 'required|integer|min:1',
            'id_annee_academique_paiement' => 'required|integer|min:1',
            'observations' => 'nullable|string',
        ]);

        DB::transaction(function () use ($echeance, $data) {
            $echeance->update([
                'statut' => 'payee',
                'date_paiement' => $data['date_paiement'],
                'montant_paye' => $data['montant_paye'],
                'id_caisse_paiement' => $data['id_caisse_paiement'],
                'id_annee_academique_paiement' => $data['id_annee_academique_paiement'],
                'id_user_paiement' => Auth::id(),
                'observations' => $data['observations'] ?? $echeance->observations,
            ]);

            $this->refreshDetteStatut($echeance->entree_speciale);
        });

        return back()->with('success', 'Remboursement enregistre avec succes.');
    }

    public function annulerPaiementEcheance($id)
    {
        $echeance = entree_speciale_echeance::with('entree_speciale.echeances')->findOrFail($id);

        DB::transaction(function () use ($echeance) {
            $echeance->update([
                'statut' => 'en_attente',
                'date_paiement' => null,
                'montant_paye' => 0,
                'id_caisse_paiement' => 0,
                'id_annee_academique_paiement' => 0,
                'id_user_paiement' => 0,
            ]);

            $this->refreshDetteStatut($echeance->entree_speciale);
        });

        return back()->with('success', 'Paiement de l echeance annule.');
    }

    private function filteredQuery(Request $request)
    {
        return entree_speciale::query()
            ->when($request->filled('type_entree'), fn($q) => $q->where('type_entree', $request->type_entree))
            ->when($request->filled('date_debut'), fn($q) => $q->whereDate('date_entree', '>=', $request->date_debut))
            ->when($request->filled('date_fin'), fn($q) => $q->whereDate('date_entree', '<=', $request->date_fin))
            ->when($request->filled('creancier'), fn($q) => $q->where('nom_tiers', 'like', '%' . $request->creancier . '%'))
            ->when($request->filled('id_annee_academique'), fn($q) => $q->where('id_annee_academique_remboursement', $request->id_annee_academique))
            ->when($request->filled('id_annee_academique_utilisation'), fn($q) => $q->where('id_annee_academique_utilisation', $request->id_annee_academique_utilisation))
            ->when($request->filled('id_budget'), fn($q) => $q->where('id_budget', $request->id_budget))
            ->when($request->filled('id_caisse'), fn($q) => $q->where('id_caisse', $request->id_caisse));
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'type_entree' => 'required|in:dette,don,apport',
            'code_entree' => 'nullable|string|max:100',
            'libelle' => 'required|string|max:255',
            'nom_tiers' => 'required|string|max:255',
            'telephone_tiers' => 'nullable|string|max:100',
            'adresse_tiers' => 'nullable|string|max:255',
            'date_entree' => 'required|date',
            'date_contraction_dette' => 'nullable|required_if:type_entree,dette|date',
            'date_remboursement' => 'nullable|date',
            'remboursement_multiple' => 'nullable|boolean',
            'nombre_echeances' => 'nullable|integer|min:0',
            'montant' => 'required|numeric|min:0',
            'id_caisse' => 'required|integer|min:1',
            'id_budget' => 'required|integer|min:1',
            'id_annee_academique_utilisation' => 'required|integer|min:1',
            'id_annee_academique_remboursement' => 'nullable|required_if:type_entree,dette|integer|min:1',
            'observations' => 'nullable|string',
            'statut' => 'required|in:actif,solde,annule',
        ]);

        $data['id_annee_academique'] = (int) (
            $data['id_annee_academique_remboursement']
            ?? $data['id_annee_academique_utilisation']
        );
        $data['remboursement_multiple'] = (bool) ($data['remboursement_multiple'] ?? false);
        $data['nombre_echeances'] = (int) ($data['nombre_echeances'] ?? 0);

        if ($data['type_entree'] !== 'dette') {
            $data['date_contraction_dette'] = null;
            $data['date_remboursement'] = null;
            $data['remboursement_multiple'] = false;
            $data['nombre_echeances'] = 0;
            $data['id_annee_academique_remboursement'] = 0;
        }

        return $data;
    }

    private function syncEcheances(entree_speciale $entree, Request $request): void
    {
        $entree->echeances()->delete();

        if (!$entree->isDette()) {
            return;
        }

        $echeances = collect($request->input('echeances', []))
            ->filter(fn($echeance) => !empty($echeance['date_echeance']))
            ->values();

        foreach ($echeances as $index => $echeance) {
            entree_speciale_echeance::create([
                'id_entree_speciale' => $entree->id,
                'nom_echeance' => $echeance['nom_echeance'] ?? 'Echeance ' . ($index + 1),
                'date_echeance' => $echeance['date_echeance'],
                'montant' => $echeance['montant'] ?? null,
                'observations' => $echeance['observations'] ?? null,
                'statut' => 'en_attente',
            ]);
        }

        if ($entree->echeances()->count() === 0 && $entree->date_remboursement) {
            entree_speciale_echeance::create([
                'id_entree_speciale' => $entree->id,
                'nom_echeance' => 'Echeance unique',
                'date_echeance' => $entree->date_remboursement,
                'montant' => $entree->montant,
                'statut' => 'en_attente',
            ]);
        }
    }

    private function echeancesARappeler()
    {
        return entree_speciale_echeance::with('entree_speciale.budget', 'entree_speciale.caisse')
            ->where('statut', 'en_attente')
            ->whereDate('date_echeance', '>=', now()->format('Y-m-d'))
            ->whereDate('date_echeance', '<=', now()->addDays(7)->format('Y-m-d'))
            ->orderBy('date_echeance')
            ->get();
    }

    private function refreshDetteStatut(?entree_speciale $entree): void
    {
        if (!$entree || !$entree->isDette() || $entree->statut === 'annule') {
            return;
        }

        $hasEcheanceEnAttente = $entree->echeances()
            ->where('statut', '!=', 'payee')
            ->exists();

        $entree->update([
            'statut' => $hasEcheanceEnAttente ? 'actif' : 'solde',
        ]);
    }

    private function syncTransfertApprovisionnement(entree_speciale $entree): void
    {
        if ($entree->statut === 'annule' || (float) $entree->montant <= 0) {
            $entree->transfert_caisse()->delete();
            return;
        }

        $code = 'ES-' . $entree->id . '-' . $entree->code_entree;
        $soldeAvant = $this->soldeTransfertCaisse($entree->id_caisse, $entree->transfert_caisse?->id);

        Transfert_caisse::updateOrCreate(
            ['id_entree_speciale' => $entree->id],
            [
                'observation' => 'Approvisionnement automatique entree speciale : ' . $entree->libelle,
                'code_transfert' => $code,
                'type_transfert' => 2,
                'sode_caisse' => $soldeAvant + (float) $entree->montant,
                'montant_transfert' => (float) $entree->montant,
                'id_caisse_depart' => 0,
                'id_caisse_arrivee' => $entree->id_caisse,
                'date_transfert' => $entree->date_entree,
                'statut_caisse_transfert' => 1,
                'id_user' => $entree->id_user ?: Auth::id(),
                'id_last_editor' => Auth::id() ?? 0,
            ]
        );
    }

    private function soldeTransfertCaisse(int $caisseId, ?int $ignoreTransfertId = null): float
    {
        $entrants = Transfert_caisse::where('id_caisse_arrivee', $caisseId)
            ->when($ignoreTransfertId, fn($q) => $q->where('id', '<>', $ignoreTransfertId))
            ->sum('montant_transfert');

        $sortants = Transfert_caisse::where('id_caisse_depart', $caisseId)
            ->when($ignoreTransfertId, fn($q) => $q->where('id', '<>', $ignoreTransfertId))
            ->sum('montant_transfert');

        return (float) $entrants - (float) $sortants;
    }

    private function viewData(array $data = []): array
    {
        return $data + [
            'types' => $this->types,
            'budgets' => Budget::orderBy('libelle_ligne_budget')->get(),
            'caisses' => caisse::orderBy('nom_caisse')->get(),
            'annees' => annee_academique::orderBy('nom', 'desc')->get(),
        ];
    }

    private function generateCode(): string
    {
        return 'ES-' . now()->format('YmdHis');
    }
}
