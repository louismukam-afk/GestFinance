<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\banque;
use App\Models\caisse;
use App\Models\Transfert_caisse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransfertCaisseController extends Controller
{
    public function index()
    {
        $transferts = Transfert_caisse::with(['caisseDepart', 'caisseArrivee', 'banqueDepart', 'banqueArrivee'])
            ->latest()
            ->get();
        $caisses1 = caisse::all()->map(function ($caisse) {
            $caisse->solde_calcule = $this->getSoldeCaisse($caisse->id);
            return $caisse;
        });
        $caisses = caisse::all();
        $banques = banque::orderBy('nom_banque')->get();
        $title = "Gestion des Transferts";

        return view('Admin.Transfert.index', compact('transferts', 'caisses','caisses1', 'banques', 'title'));
    }

    /**
     * 📌 CREATE
     */
    private function getSoldeCaisse($caisseId)
    {
        $caisse = caisse::find($caisseId);

       /* if ($caisse->type_caisse != 0) {
            return 0; // sécurité
        }*/

        $entrees = \App\Models\reglement_etudiant::where('id_caisse', $caisseId)
            ->sum('montant_reglement');

        $transfertsEntrants = \App\Models\Transfert_caisse::where('id_caisse_arrivee', $caisseId)
            ->sum('montant_transfert');

        $transfertsSortants = \App\Models\Transfert_caisse::where('id_caisse_depart', $caisseId)
            ->sum('montant_transfert');

        return $entrees + $transfertsEntrants - $transfertsSortants;
    }
    private function getSoldeCaisse1($caisseId)
    {
        $entrees = \App\Models\reglement_etudiant::where('id_caisse', $caisseId)
            ->sum('montant_reglement');

        $transfertsEntrants = \App\Models\Transfert_caisse::where('id_caisse_arrivee', $caisseId)
            ->sum('montant_transfert');

        $transfertsSortants = \App\Models\Transfert_caisse::where('id_caisse_depart', $caisseId)
            ->sum('montant_transfert');

        return $entrees + $transfertsEntrants - $transfertsSortants;
    }

    private function getSoldeBanque($banqueId)
    {
        $entrees = \App\Models\reglement_etudiant::where('id_banque', $banqueId)
            ->sum('montant_reglement');

        $sorties = \App\Models\decaissement::where('id_banque', $banqueId)
            ->sum('montant');

        $transfertsEntrants = \App\Models\Transfert_caisse::where('id_banque_arrivee', $banqueId)
            ->sum('montant_transfert');

        $transfertsSortants = \App\Models\Transfert_caisse::where('id_banque_depart', $banqueId)
            ->sum('montant_transfert');

        return $entrees + $transfertsEntrants - $transfertsSortants - $sorties;
    }

    private function getSoldeCompte($type, $id)
    {
        return $type === 'banque'
            ? $this->getSoldeBanque((int) $id)
            : $this->getSoldeCaisse((int) $id);
    }

    private function validateCompteTransfert(Request $request)
    {
        $request->validate([
            'code_transfert' => 'required|unique:transfert_caisses,code_transfert',
            'compte_depart_type' => 'required|in:caisse,banque',
            'compte_arrivee_type' => 'required|in:caisse,banque',
            'montant_transfert' => 'required|numeric|min:1',
        ]);

        $departId = $request->compte_depart_type === 'banque'
            ? (int) $request->id_banque_depart
            : (int) $request->id_caisse_depart;
        $arriveeId = $request->compte_arrivee_type === 'banque'
            ? (int) $request->id_banque_arrivee
            : (int) $request->id_caisse_arrivee;

        if (!$departId || !$arriveeId) {
            throw new \RuntimeException('Veuillez choisir le compte de depart et le compte d arrivee.');
        }

        if ($request->compte_depart_type === $request->compte_arrivee_type && $departId === $arriveeId) {
            throw new \RuntimeException('Le compte de depart doit etre different du compte d arrivee.');
        }

        $caisseDepart = $request->compte_depart_type === 'caisse' ? caisse::findOrFail($departId) : null;
        $caisseArrivee = $request->compte_arrivee_type === 'caisse' ? caisse::findOrFail($arriveeId) : null;

        if ($request->compte_depart_type !== $request->compte_arrivee_type
            && (($caisseDepart && (int) $caisseDepart->type_caisse !== 2) || ($caisseArrivee && (int) $caisseArrivee->type_caisse !== 2))) {
            throw new \RuntimeException('Les transferts banque <-> caisse doivent passer par une caisse centrale.');
        }

        return [$departId, $arriveeId];
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {

            [$departId, $arriveeId] = $this->validateCompteTransfert($request);

            // 🔥 SOLDE CALCULÉ
            $soldeDepart = $this->getSoldeCompte($request->compte_depart_type, $departId);

            // 🔒 SÉCURITÉ
            $montant = (float) $request->montant_transfert;

            if ($soldeDepart < $montant) {
                return back()->with('error', 'Solde insuffisant ❌');
            }
//            dd("ici");
            [
                'solde' => $soldeDepart,
                'montant' => $montant,
                'condition' => $soldeDepart < $montant
            ];

            // 📊 ENREGISTREMENT
            Transfert_caisse::create([
                'observation' => $request->observation,
                'code_transfert' => $request->code_transfert,
                'type_transfert' => 0,

                // snapshot après transfert
                'sode_caisse' => $soldeDepart - $request->montant_transfert,

                'montant_transfert' => $request->montant_transfert,
                'id_caisse_depart' => $request->compte_depart_type === 'caisse' ? $departId : 0,
                'id_caisse_arrivee' => $request->compte_arrivee_type === 'caisse' ? $arriveeId : 0,
                'id_banque_depart' => $request->compte_depart_type === 'banque' ? $departId : 0,
                'id_banque_arrivee' => $request->compte_arrivee_type === 'banque' ? $arriveeId : 0,

                'date_transfert' => now(),

                // sortie = négatif
                'statut_caisse_transfert' => 0,

                'id_user' => auth()->id(),
            ]);

            DB::commit();

            return redirect()->route('transfert_management')
                ->with('success', 'Transfert effectué ✅');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * 📌 UPDATE
     */

    public function update(Request $request)
    {
        $transfert = Transfert_caisse::findOrFail($request->id);

        DB::beginTransaction();

        try {

            $ancienMontant = $transfert->montant_transfert;

            $departType = (int) ($transfert->id_banque_depart ?? 0) > 0 ? 'banque' : 'caisse';
            $departId = $departType === 'banque' ? (int) $transfert->id_banque_depart : (int) $transfert->id_caisse_depart;

            // 🔁 RECONSTITUER SOLDE AVANT TRANSFERT
            $soldeAvant = $this->getSoldeCompte($departType, $departId) + $ancienMontant;

            // 🔒 VÉRIFICATION NOUVEAU MONTANT
            if ($soldeAvant < $request->montant_transfert) {
                return back()->with('error', 'Solde insuffisant après modification ❌');
            }

            // 📊 UPDATE
            $transfert->update([
                'observation' => $request->observation,
                'montant_transfert' => $request->montant_transfert,

                // nouveau snapshot
                'sode_caisse' => $soldeAvant - $request->montant_transfert,

                // devient validé
                'statut_caisse_transfert' => 1,

                'id_last_editor' => auth()->id(),
            ]);

            DB::commit();

            return redirect()->route('transfert_management')
                ->with('success', 'Transfert modifié avec succès ✏️');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
    public function update1(Request $request)
    {
        return $this->update($request);

        $transfert = Transfert_caisse::findOrFail($request->id);

        DB::beginTransaction();

        try {

            $ancien_montant = $transfert->montant_transfert;

            // 🔁 ANNULER ancien transfert
            $depart->sode_caisse += $ancien_montant;
            $arrivee->sode_caisse -= $ancien_montant;

            // 🔁 NOUVEAU transfert
            $depart->sode_caisse -= $request->montant_transfert;
            $arrivee->sode_caisse += $request->montant_transfert;

            $depart->save();
            $arrivee->save();

            $transfert->update([
                'observation' => $request->observation,
                'montant_transfert' => $request->montant_transfert,
                'sode_caisse' => $depart->sode_caisse,

                // Si validé → devient positif
                'statut_caisse_transfert' => 1,

                'id_last_editor' => Auth::id(),
            ]);

            DB::commit();

            return redirect()->route('transfert_management')
                ->with('success', 'Modification réussie ✏️');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * 📌 DELETE
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            $transfert = Transfert_caisse::findOrFail($id);

            // 🔁 ANNULATION DU TRANSFERT
            $transfert->delete();

            DB::commit();

            return redirect()->route('transfert_management')
                ->with('success', 'Transfert supprimé 🗑️');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * 📌 SHOW (optionnel)
     */
    public function show($id)
    {
        $transfert = Transfert_caisse::with(['caisseDepart', 'caisseArrivee', 'banqueDepart', 'banqueArrivee', 'user'])
            ->findOrFail($id);

        return view('Admin.Transfert.show', compact('transfert'));
    }
}
