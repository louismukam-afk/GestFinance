<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\caisse;
use App\Models\Transfert_caisse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransfertCaisseController extends Controller
{
    public function index()
    {
        $transferts = Transfert_caisse::with(['caisseDepart', 'caisseArrivee'])
            ->latest()
            ->get();
        $caisses1 = caisse::all()->map(function ($caisse) {
            $caisse->solde_calcule = $this->getSoldeCaisse($caisse->id);
            return $caisse;
        });
        $caisses = caisse::all();
        $title = "Gestion des Transferts";

        return view('Admin.Transfert.index', compact('transferts', 'caisses','caisses1', 'title'));
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
    public function store(Request $request)
    {
        $request->validate([
            'code_transfert' => 'required|unique:transfert_caisses,code_transfert',
            'id_caisse_depart' => 'required|different:id_caisse_arrivee',
            'id_caisse_arrivee' => 'required',
            'montant_transfert' => 'required|numeric|min:1',
        ]);

        DB::beginTransaction();

        try {

            $depart = caisse::findOrFail($request->id_caisse_depart);
            $arrivee = caisse::findOrFail($request->id_caisse_arrivee);

            // 🔥 SOLDE CALCULÉ
            $soldeDepart = $this->getSoldeCaisse($depart->id);

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
                'id_caisse_depart' => $depart->id,
                'id_caisse_arrivee' => $arrivee->id,

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

            $depart = caisse::findOrFail($transfert->id_caisse_depart);

            // 🔁 RECONSTITUER SOLDE AVANT TRANSFERT
            $soldeAvant = $this->getSoldeCaisse($depart->id) + $ancienMontant;

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
        $transfert = Transfert_caisse::findOrFail($request->id);

        DB::beginTransaction();

        try {

            $ancien_montant = $transfert->montant_transfert;

            $depart = caisse::findOrFail($transfert->id_caisse_depart);
            $arrivee = caisse::findOrFail($transfert->id_caisse_arrivee);

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

            $depart = caisse::findOrFail($transfert->id_caisse_depart);
            $arrivee = caisse::findOrFail($transfert->id_caisse_arrivee);

            // 🔁 ANNULATION DU TRANSFERT
            $depart->sode_caisse += $transfert->montant_transfert;
            $arrivee->sode_caisse -= $transfert->montant_transfert;

            $depart->save();
            $arrivee->save();

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
        $transfert = Transfert_caisse::with(['caisseDepart', 'caisseArrivee', 'user'])
            ->findOrFail($id);

        return view('Admin.Transfert.show', compact('transfert'));
    }
}
