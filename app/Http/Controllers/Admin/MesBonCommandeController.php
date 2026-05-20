<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\bon_commandeok;
use App\Models\entite;
use App\Models\personnel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MesBonCommandeController extends Controller
{
    public function attente(Request $request)
    {
        return $this->renderIndex($request, 'attente');
    }

    public function valides(Request $request)
    {
        return $this->renderIndex($request, 'valides');
    }

    public function refuses(Request $request)
    {
        return $this->renderIndex($request, 'refuses');
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $data['id_user'] = auth()->id();
        $data['statuts'] = 0;
        $data['validation_pdg'] = 0;
        $data['validation_daf'] = 0;
        $data['validation_achats'] = 0;
        $data['validation_emetteur'] = 0;
        $data['refus_pdg'] = 0;
        $data['refus_daf'] = 0;
        $data['refus_achats'] = 0;
        $data['refus_emetteur'] = 0;
        $data['montant_realise'] = 0;
        $data['reste'] = $data['montant_total'];

        bon_commandeok::create($data);

        return back()->with('success', 'Bon de commande cree avec succes.');
    }

    public function update(Request $request, bon_commandeok $bon)
    {
        $this->authorizeOwner($bon);

        if ($bon->statut_bon_code === 1) {
            return back()->withErrors(['bon' => 'Un bon deja valide ne peut plus etre modifie depuis cette interface.']);
        }

        $data = $this->validatedData($request, $bon->id);
        $data['reste'] = max(0, $data['montant_total'] - $bon->montant_realise);

        $bon->update($data);

        return back()->with('success', 'Bon de commande modifie avec succes.');
    }

    public function destroy(bon_commandeok $bon)
    {
        $this->authorizeOwner($bon);

        if ($bon->element_bon_commandes()->exists()) {
            return back()->with('error', 'Supprimez d abord les elements du bon avant de supprimer le bon.');
        }

        $bon->delete();

        return back()->with('success', 'Bon de commande supprime avec succes.');
    }

    public function validerEmetteur(bon_commandeok $bon)
    {
        $this->authorizeOwner($bon);

        $bon->validation_emetteur = 1;
        $bon->refus_emetteur = 0;
        $bon->motif_refus_emetteur = null;
        $bon->date_refus_emetteur = null;

        if ($bon->validation_pdg) {
            $bon->statuts = 1;
            $bon->date_validation = $bon->date_validation ?: now();
        } elseif ($bon->validation_daf && $bon->validation_achats && $bon->validation_emetteur) {
            $bon->statuts = 0;
        }

        $bon->save();

        return back()->with('success', 'Validation emetteur effectuee avec succes.');
    }

    public function refuserEmetteur(Request $request, bon_commandeok $bon)
    {
        $this->authorizeOwner($bon);

        $data = $request->validate([
            'motif_refus' => 'required|string|max:2000',
        ]);

        $bon->validation_emetteur = 0;
        $bon->refus_emetteur = 1;
        $bon->motif_refus_emetteur = $data['motif_refus'];
        $bon->date_refus_emetteur = now();
        $bon->statuts = 2;
        $bon->date_validation = null;
        $bon->save();

        return back()->with('success', 'Bon refuse par l emetteur.');
    }

    public function exportPdf(Request $request, string $type)
    {
        $bons = $this->query($request, $type)->get();

        $pdf = Pdf::loadView('Admin.MesBons.pdf', [
            'bons' => $bons,
            'type' => $type,
            'dateDebut' => $request->date_debut,
            'dateFin' => $request->date_fin,
            'user' => auth()->user(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('mes_bons_' . $type . '.pdf');
    }

    public function document(bon_commandeok $bon)
    {
        $this->authorizeOwner($bon);

        $bon->load(['personnels', 'entites', 'user', 'element_bon_commandes']);

        return view('Admin.MesBons.document', [
            'bon' => $bon,
            'elements' => $bon->element_bon_commandes,
            'title' => 'Bon de commande',
        ]);
    }

    public function documentPdf(bon_commandeok $bon)
    {
        $this->authorizeOwner($bon);

        $bon->load(['personnels', 'entites', 'user', 'element_bon_commandes']);

        $pdf = Pdf::loadView('Admin.MesBons.document_pdf', [
            'bon' => $bon,
            'elements' => $bon->element_bon_commandes,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('bon_commande_' . $bon->id . '.pdf');
    }

    private function renderIndex(Request $request, string $type)
    {
        return view('Admin.MesBons.index', [
            'bons' => $this->query($request, $type)->get(),
            'type' => $type,
            'entites' => entite::orderBy('nom_entite')->get(),
            'personnels' => personnel::orderBy('nom')->get(),
        ]);
    }

    private function query(Request $request, string $type)
    {
        return bon_commandeok::with(['personnels', 'entites', 'user'])
            ->where('id_user', auth()->id())
            ->when($request->date_debut, fn($q) => $q->whereDate('date_debut', '>=', $request->date_debut))
            ->when($request->date_fin, fn($q) => $q->whereDate('date_debut', '<=', $request->date_fin))
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('nom_bon_commande', 'like', '%' . $request->search . '%')
                        ->orWhere('description_bon_commande', 'like', '%' . $request->search . '%');
                });
            })
            ->when($type === 'valides', fn($q) => $q->where(function ($sub) {
                $sub->where('statuts', 1);
            }))
            ->when($type === 'attente', fn($q) => $q->where('validation_pdg', 0)->where('statuts', 0))
            ->when($type === 'refuses', fn($q) => $q->where('statuts', 2))
            ->orderBy('date_debut', 'desc');
    }

    private function validatedData(Request $request, ?int $bonId = null): array
    {
        return $request->validate([
            'nom_bon_commande' => [
                'required',
                'string',
                'max:255',
                Rule::unique('bon_commandeoks', 'nom_bon_commande')->ignore($bonId),
            ],
            'description_bon_commande' => 'required|string',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'date_entree_signature' => 'required|date',
            'montant_total' => 'required|numeric|min:0',
            'montant_lettre' => 'required|string|max:255',
            'id_personnel' => 'required|integer|exists:personnels,id',
            'id_entite' => 'required|integer|exists:entites,id',
        ]);
    }

    private function authorizeOwner(bon_commandeok $bon): void
    {
        abort_if((int) $bon->id_user !== (int) auth()->id(), 403, 'Ce bon ne vous appartient pas.');
    }
}
