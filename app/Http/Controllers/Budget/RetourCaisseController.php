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
use App\Models\ligne_budgetaire_sortie;
use App\Models\retour_caisse;
use App\Models\Transfert_caisse;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RetourCaisseController extends Controller
{
private function filters()
{
    $centralCaisses = caisse::where('type_caisse', 2)
        ->orWhere('nom_caisse', 'like', '%central%')
        ->orderBy('nom_caisse')
        ->get();

    return [
        'bons' => bon_commandeok::orderBy('nom_bon_commande')->get(),

        'decaissements' => decaissement::with(['bon'])
            ->orderBy('created_at', 'desc')
            ->get(),

        'caisses' => $centralCaisses->isNotEmpty()
            ? $centralCaisses
            : caisse::orderBy('nom_caisse')->get(),

        'budgets' => budget::orderBy('libelle_ligne_budget')->get(),

        'annees' => annee_academique::orderBy('nom', 'desc')->get(),

        'users' => User::orderBy('name')->get(),
    ];
   
}
private function filters1()
{
    $centralCaisses = caisse::where('type_caisse', 2)
        ->orWhere('nom_caisse', 'like', '%central%')
        ->orderBy('nom_caisse')
        ->get();

    return [
        'bons' => bon_commandeok::orderBy('nom_bon_commande')->get(),
        'caisses' => $centralCaisses->isNotEmpty()
            ? $centralCaisses
            : caisse::orderBy('nom_caisse')->get(),

        'annees' => annee_academique::orderBy('nom', 'desc')->get(),
        'users' => User::orderBy('name')->get(),
    ];
}
  
public function getDecaissements($bon)
{
    $decaissements = decaissement::where('id_bon_commande', $bon)
        ->select('id', 'motif', 'montant')
        ->get();

    return response()->json($decaissements);
}
public function getDecaissementDetailsras($id)
{
    
    try {
        $decaissement = decaissement::findOrFail($id);

        return response()->json([
            'test' => 'OK',
            'id' => $decaissement->id,
            'montant' => $decaissement->montant,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
        ], 500);
    }
}
public function getDecaissementDetails($id)
{
    $decaissement = decaissement::findOrFail($id);

    $totalRetour = retour_caisse::where('id_decaissement', $decaissement->id)
        ->sum('montant');

    $budget = budget::find($decaissement->id_budget);

    $ligne = ligne_budgetaire_sortie::find($decaissement->id_ligne_budgetaire_sortie);

    $element = element_ligne_budgetaire_sortie::find($decaissement->id_elements_ligne_budgetaire_sortie);

    $donneeLigne = donnee_ligne_budgetaire_sortie::find($decaissement->id_donnee_ligne_budgetaire_sortie);

    $donneeBudgetaire = donnee_budgetaire_sortie::find($decaissement->id_donnee_budgetaire_sortie);

    $annee = annee_academique::find($decaissement->id_annee_academique);

    return response()->json([
        'id_budget' => $decaissement->id_budget,
        'id_ligne_budgetaire_sortie' => $decaissement->id_ligne_budgetaire_sortie,
        'id_elements_ligne_budgetaire_sortie' => $decaissement->id_elements_ligne_budgetaire_sortie,
        'id_donnee_ligne_budgetaire_sortie' => $decaissement->id_donnee_ligne_budgetaire_sortie,
        'id_donnee_budgetaire_sortie' => $decaissement->id_donnee_budgetaire_sortie,
        'id_annee_academique' => $decaissement->id_annee_academique,

        'budget_libelle' => optional($budget)->libelle_ligne_budget,
        'ligne_libelle' => optional($ligne)->libelle_ligne_budgetaire_sortie,
        'element_libelle' => optional($element)->libelle_elements_ligne_budgetaire_sortie,
        'donnee_ligne_libelle' => optional($donneeLigne)->donnee_ligne_budgetaire_sortie,
        'donnee_budgetaire_libelle' => optional($donneeBudgetaire)->donnee_ligne_budgetaire_sortie,
        'annee_libelle' => optional($annee)->nom,

        'montant_decaisse' => $decaissement->montant,
        'montant_retourne' => $totalRetour,
        'reste' => $decaissement->montant,
    ]);
}
private function query(Request $request, bool $currentUserOnly = false)
{
    return retour_caisse::with([
        'bon',
        'caisse',
        'decaissement',
        'budget',
        'ligne_budgetaire_sortie',
        'element_ligne_budgetaire_sortie',
        'donnee_ligne_budgetaire_sortie',
        'annee_academique',
        'user',
    ])
        ->when($request->date_debut, fn($q) => $q->whereDate('date_retour', '>=', $request->date_debut))
        ->when($request->date_fin, fn($q) => $q->whereDate('date_retour', '<=', $request->date_fin))
        ->when($request->id_user && !$currentUserOnly, fn($q) => $q->where('id_user', $request->id_user))
        ->when($currentUserOnly, fn($q) => $q->where('id_user', auth()->id()))
        ->when($request->id_caisse, fn($q) => $q->where('id_caisse', $request->id_caisse))
        ->when($request->id_budget, fn($q) => $q->where('id_budget', $request->id_budget))
        ->when($request->id_bon_commande, fn($q) => $q->where('id_bon_commande', $request->id_bon_commande))
        ->when($request->id_decaissement, fn($q) => $q->where('id_decaissement', $request->id_decaissement))
        ->when($request->id_annee_academique, fn($q) => $q->where('id_annee_academique', $request->id_annee_academique))
        ->orderBy('date_retour', 'desc');
}
private function query1(Request $request, bool $currentUserOnly = false)
{
    return retour_caisse::with([
        'bon',
        'caisse',
        'decaissement',
        'budget',
        'ligne_budgetaire_sortie',
        'element_ligne_budgetaire_sortie',
        'donnee_ligne_budgetaire_sortie',
        'annee_academique',
        'user',
    ])
        ->when($request->date_debut, fn($q) => $q->whereDate('date_retour', '>=', $request->date_debut))
        ->when($request->date_fin, fn($q) => $q->whereDate('date_retour', '<=', $request->date_fin))
        ->when($request->id_user && !$currentUserOnly, fn($q) => $q->where('id_user', $request->id_user))
        ->when($currentUserOnly, fn($q) => $q->where('id_user', auth()->id()))
        ->when($request->id_caisse, fn($q) => $q->where('id_caisse', $request->id_caisse))
        ->when($request->id_budget, fn($q) => $q->where('id_budget', $request->id_budget))
        ->when($request->id_bon_commande, fn($q) => $q->where('id_bon_commande', $request->id_bon_commande))
        ->when($request->id_annee_academique, fn($q) => $q->where('id_annee_academique', $request->id_annee_academique))
        ->orderBy('date_retour', 'desc');
}

    
    public function create()
    {
        return view('Admin.Etats.sorties.retours_caisse.create', $this->filters());
    }
public function store(Request $request)
{
    $data = $request->validate([
        'id_decaissement' => 'required|integer|exists:decaissements,id',
        'id_bon_commande' => 'required|integer',
        'id_caisse' => 'required|integer',
        'date_retour' => 'required|date',
        'montant' => 'required|numeric|min:1',
        'motif' => 'nullable|string|max:255',
    ]);

    DB::transaction(function () use ($data) {
        $decaissement = decaissement::lockForUpdate()->findOrFail($data['id_decaissement']);

        if ($data['montant'] > $decaissement->montant) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'montant' => 'Le montant retourne ne peut pas depasser le reste du decaissement.'
            ]);
        }

        $data['id_user'] = auth()->id();
        $data['numero_retour'] = 'RET-' . now()->format('YmdHis');

        $data['id_budget'] = $decaissement->id_budget;
        $data['id_ligne_budgetaire_sortie'] = $decaissement->id_ligne_budgetaire_sortie;
        $data['id_elements_ligne_budgetaire_sortie'] = $decaissement->id_elements_ligne_budgetaire_sortie;
        $data['id_donnee_ligne_budgetaire_sortie'] = $decaissement->id_donnee_ligne_budgetaire_sortie;
        $data['id_donnee_budgetaire_sortie'] = $decaissement->id_donnee_budgetaire_sortie;
        $data['id_annee_academique'] = $decaissement->id_annee_academique;

        retour_caisse::create($data);

        $decaissement->montant = $decaissement->montant - $data['montant'];
        $decaissement->reste = ($decaissement->reste ?? 0) + $data['montant'];
        $decaissement->save();

        if ($decaissement->id_transfert_caisse) {
            $transfert = Transfert_caisse::lockForUpdate()->find($decaissement->id_transfert_caisse);

            if ($transfert) {
                $transfert->sode_caisse = ($transfert->sode_caisse ?? 0) + $data['montant'];
                $transfert->save();
            }
        }
    });

    return redirect()
        ->route('retour_caisses.index')
        ->with('success', 'Retour en caisse enregistré avec succès.');
}
    public function store1(Request $request)
    {
        $data = $request->validate([
            'id_bon_commande' => 'required|integer',
            'id_caisse' => 'required|integer',
            'id_budget' => 'required|integer',
            'id_ligne_budgetaire_sortie' => 'required|integer',
            'id_elements_ligne_budgetaire_sortie' => 'nullable|integer',
            'id_donnee_ligne_budgetaire_sortie' => 'required|integer',
            'id_donnee_budgetaire_sortie' => 'nullable|integer',
            'id_annee_academique' => 'required|integer',
            'date_retour' => 'required|date',
            'montant' => 'required|numeric|min:1',
            'motif' => 'nullable|string|max:255',
        ]);

        $data['id_user'] = auth()->id();
        $data['numero_retour'] = 'RET-' . now()->format('YmdHis');

        $totalDecaisse = decaissement::where('id_bon_commande', $data['id_bon_commande'])
            ->where('id_budget', $data['id_budget'])
            ->where('id_donnee_ligne_budgetaire_sortie', $data['id_donnee_ligne_budgetaire_sortie'])
            ->sum('montant');
            dd($data,$totalDecaisse);

        $totalRetour = retour_caisse::where('id_bon_commande', $data['id_bon_commande'])
            ->where('id_budget', $data['id_budget'])
            ->where('id_donnee_ligne_budgetaire_sortie', $data['id_donnee_ligne_budgetaire_sortie'])
            ->sum('montant');

        if (($totalRetour + $data['montant']) > $totalDecaisse) {
            return back()
                ->withInput()
                ->withErrors(['montant' => 'Le montant retourne ne peut pas depasser le montant deja decaisse sur cette donnee.']);
        }

        retour_caisse::create($data);

        return redirect()->route('retour_caisses.index')->with('success', 'Retour en caisse enregistre avec succes.');
    }

    public function destroy($id)
{
    $retour = retour_caisse::findOrFail($id);

    // Optionnel : sécurité (seul le créateur peut supprimer)
    if ($retour->id_user !== auth()->id()) {
        return back()->withErrors([
            'error' => 'Vous n’êtes pas autorisé à supprimer ce retour.'
        ]);
    }

    DB::transaction(function () use ($retour) {
        if ($retour->id_decaissement) {
            $decaissement = decaissement::lockForUpdate()->find($retour->id_decaissement);

            if ($decaissement) {
                $decaissement->montant = $decaissement->montant + $retour->montant;
                $decaissement->reste = max(0, ($decaissement->reste ?? 0) - $retour->montant);
                $decaissement->save();

                if ($decaissement->id_transfert_caisse) {
                    $transfert = Transfert_caisse::lockForUpdate()->find($decaissement->id_transfert_caisse);

                    if ($transfert) {
                        $transfert->sode_caisse = max(0, ($transfert->sode_caisse ?? 0) - $retour->montant);
                        $transfert->save();
                    }
                }
            }
        }

        $retour->delete();
    });

    return redirect()
        ->route('retour_caisses.index')
        ->with('success', 'Retour en caisse supprimé avec succès.');
}

    public function index(Request $request)
    {
        $retours = $this->query($request)->get();

        return view('Admin.Etats.sorties.retours_caisse.index', array_merge($this->filters(), [
            'retours' => $retours,
            'total' => $retours->sum('montant'),
            'currentUserOnly' => false,
        ]));
    }

    public function mine(Request $request)
    {
        $retours = $this->query($request, true)->get();

        return view('Admin.Etats.sorties.retours_caisse.index', array_merge($this->filters(), [
            'retours' => $retours,
            'total' => $retours->sum('montant'),
            'currentUserOnly' => true,
        ]));
    }

    public function exportPdf(Request $request)
    {
        $retours = $this->query($request)->get();

        $pdf = Pdf::loadView('Admin.Etats.sorties.retours_caisse.pdf', [
            'retours' => $retours,
            'total' => $retours->sum('montant'),
            'dateDebut' => $request->date_debut,
            'dateFin' => $request->date_fin,
            'currentUserOnly' => false,
        ])->setPaper('a3', 'landscape');

        return $pdf->download('retours_caisse.pdf');
    }

    public function exportMinePdf(Request $request)
    {
        $retours = $this->query($request, true)->get();

        $pdf = Pdf::loadView('Admin.Etats.sorties.retours_caisse.pdf', [
            'retours' => $retours,
            'total' => $retours->sum('montant'),
            'dateDebut' => $request->date_debut,
            'dateFin' => $request->date_fin,
            'currentUserOnly' => true,
        ])->setPaper('a3', 'landscape');

        return $pdf->download('mes_retours_caisse.pdf');
    }
}
