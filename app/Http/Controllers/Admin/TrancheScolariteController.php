<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\scolarite;
use App\Models\tranche_scolarite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class TrancheScolariteController extends Controller
{
    public function manage($scolarite_id)
    {
        $scolarite = scolarite::findOrFail($scolarite_id);
        $tranches = tranche_scolarite::where('id_scolarite', $scolarite_id)->get();

        if ($tranches->isEmpty()) {
            return redirect()->route('tranche_scolarite.create', $scolarite_id);
        }
        $title = "Choix des tranches de scolarité" ;
        return view('Admin.tranche_scolarite.manage', compact('scolarite', 'tranches','title'));
    }

    public function create($scolarite_id)
    {
        $scolarite = scolarite::findOrFail($scolarite_id);
        return view('Admin.tranche_scolarite.create', compact('scolarite'));
    }
    public function index($scolarite_id)
    {
        // 🔎 Récupération de la scolarité
        $scolarite = scolarite::with(['cycles','filiere','niveaux','specialites'])
            ->findOrFail($scolarite_id);

        // 🔎 Récupération des tranches
        $tranches = tranche_scolarite::where('id_scolarite', $scolarite_id)
            ->orderBy('date_limite', 'asc')
            ->get();

        $title = "Liste des tranches de scolarité pour " . $scolarite->montant_total;

        return view('Admin.tranche_scolarite.index', compact('scolarite', 'tranches', 'title'));
    }
    public function store(Request $request, $scolarite_id)
    {
        $scolarite = \App\Models\scolarite::with(['cycles','filiere','niveaux','specialites'])
            ->findOrFail($scolarite_id);

        $data = $request->validate([
            'nom_tranche.*'   => 'required|string|max:255',
            'date_limite.*'   => 'required|date',
            'montant_tranche.*' => 'required|numeric|min:0',
        ]);

        foreach ($data['nom_tranche'] as $i => $nom) {
            \App\Models\tranche_scolarite::create([
                'nom_tranche'   => $nom,
                'date_limite'   => $data['date_limite'][$i],
                'montant_tranche' => $data['montant_tranche'][$i],
                'id_scolarite'  => $scolarite->id,
                'id_cycle'      => $scolarite->id_cycle,
                'id_filiere'    => $scolarite->id_filiere,
                'id_niveau'     => $scolarite->id_niveau,
                'id_specialite' => $scolarite->id_specialite,
                'id_user'       => auth()->id(),
            ]);
        }

        return redirect()->route('tranche_scolarite.index', $scolarite_id)
            ->with('success', '✅ Tranches enregistrées avec succès.');
    }

    public function store1(Request $request, $scolarite_id)
    {
        $scolarite = scolarite::findOrFail($scolarite_id);

        $data = $request->validate([
            'nom_tranche.*' => 'required|string',
            'date_limite.*' => 'required|date',
            'montant_tranche.*' => 'required|numeric|min:0',
        ]);

        $total = 0;
        foreach ($data['nom_tranche'] as $i => $nom) {
            $montant = $data['montant_tranche'][$i];
            $total += $montant;

            tranche_scolarite::create([
                'nom_tranche' => $nom,
                'date_limite' => $data['date_limite'][$i],
                'montant_tranche' => $montant,
                'id_user' => Auth::id(),
                'id_scolarite' => $scolarite->id,
                'id_cycle' => $scolarite->id_cycle,
                'id_filiere' => $scolarite->id_filiere,
                'id_niveau' => $scolarite->id_niveau,
                'id_specialite' => $scolarite->id_specialite,
            ]);
        }

        if ($total != $scolarite->montant_total) {
            return back()->with('error', "⚠️ Le total des tranches ($total) doit être égal au montant de la scolarité ({$scolarite->montant_total}).");
        }

        return redirect()->route('tranche_scolarite.manage', $scolarite->id)
            ->with('success', '✅ Tranches enregistrées avec succès.');
    }
    public function update(Request $request, $id)
    {
        $tranche = \App\Models\tranche_scolarite::findOrFail($id);

        $request->validate([
            'nom_tranche' => 'required|string|max:255',
            'date_limite' => 'required|date',
            'montant_tranche' => 'required|numeric|min:0',
        ]);

        $tranche->update([
            'nom_tranche'   => $request->nom_tranche,
            'date_limite'   => $request->date_limite,
            'montant_tranche' => $request->montant_tranche,
            'id_user'       => Auth::id(),
        ]);

        return redirect()->route('tranche_scolarite.index', $tranche->id_scolarite)
            ->with('success', '✏️ Tranche modifiée avec succès.');
    }
    public function editForm($id)
    {
        $tranche = tranche_scolarite::findOrFail($id);
        $title = "Choix des tranches de scolarité" ;
        return view('Admin.tranche_scolarite.edit', compact('tranche','title'));
    }

    public function exportPdf($scolarite_id)
    {
        $scolarite = scolarite::findOrFail($scolarite_id);
        $tranches = tranche_scolarite::where('id_scolarite', $scolarite_id)->get();

        $pdf = Pdf::loadView('Admin.tranche_scolarite.pdf', compact('scolarite', 'tranches'));
        return $pdf->download("tranches_scolarite_{$scolarite->id}.pdf");
    }

    public function destroy($id)
    {
        $tranche = tranche_scolarite::findOrFail($id);
        $scolarite_id = $tranche->id_scolarite;
        $tranche->delete();

        return redirect()->route('tranche_scolarite.index', $scolarite_id)
            ->with('success', '🗑️ Tranche supprimée avec succès.');
    }
}
