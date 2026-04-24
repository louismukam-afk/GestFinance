<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\cycle;
use App\Models\filiere;
use App\Models\niveau;
use App\Models\scolarite;
use App\Models\specialite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScolariteController extends Controller
{
    public function index()
    {
        $scolarites = scolarite::with(['cycles','filiere','niveaux','specialites'])->latest()->get();
        $cycles = cycle::all();
        $filieres = filiere::all();
        $niveaux = niveau::all();
        $specialites = specialite::all();

        $title = "Gestion des scolarités";

        return view('Admin.scolarites.index', compact('scolarites','cycles','filieres','niveaux','specialites','title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_cycle' => 'required|exists:cycles,id',
            'id_filiere' => 'required|exists:filieres,id',
            'id_niveau' => 'required|exists:niveaux,id',
            'id_specialite' => 'required|exists:specialites,id',
            'montant_total' => 'required|numeric|min:0',
            'inscription' => 'required|numeric|min:0',
            'type_scolarite' => 'required|integer',
        ]);

        scolarite::create([
            'id_cycle' => $request->id_cycle,
            'id_filiere' => $request->id_filiere,
            'id_niveau' => $request->id_niveau,
            'id_specialite' => $request->id_specialite,
            'montant_total' => $request->montant_total,
            'inscription' => $request->inscription,
            'type_scolarite' => $request->type_scolarite,
            'id_user' => Auth::id(),
        ]);

        return redirect()->route('scolarite_management')->with('success', 'Scolarité enregistrée ✅');
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:scolarites,id',
            'id_cycle' => 'required|exists:cycles,id',
            'id_filiere' => 'required|exists:filieres,id',
            'id_niveau' => 'required|exists:niveaux,id',
            'id_specialite' => 'required|exists:specialites,id',
            'montant_total' => 'required|numeric|min:0',
            'inscription' => 'required|numeric|min:0',
            'type_scolarite' => 'required|integer',
        ]);

        $scolarite = scolarite::findOrFail($request->id);

        $scolarite->update([
            'id_cycle' => $request->id_cycle,
            'id_filiere' => $request->id_filiere,
            'id_niveau' => $request->id_niveau,
            'id_specialite' => $request->id_specialite,
            'montant_total' => $request->montant_total,
            'inscription' => $request->inscription,
            'type_scolarite' => $request->type_scolarite,
            'id_user' => Auth::id(),
        ]);

        return redirect()->route('scolarite_management')->with('success', 'Scolarité modifiée ✏️');
    }

    public function destroy($id)
    {
        scolarite::findOrFail($id)->delete();
        return redirect()->route('scolarite_management')->with('success', 'Scolarité supprimée 🗑️');
    }
}
