<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\cycle;
use App\Models\niveau;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NiveauController extends Controller
{
    public function index()
    {
        $niveaux = niveau::with('cycles')->latest()->get();
        $cycles = cycle::all();

        $title = "Gestion des niveaux";

        return view('Admin.niveaux.index', compact('niveaux', 'cycles', 'title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom_niveau' => 'required|string|max:255',
            'code_niveau' => 'required|string|max:50|unique:niveaux,code_niveau',
            'id_cycle' => 'required|exists:cycles,id',
        ]);

        niveau::create([
            'nom_niveau' => $request->nom_niveau,
            'code_niveau' => $request->code_niveau,
            'id_cycle' => $request->id_cycle,
            'id_user' => Auth::id(),
        ]);

        return redirect()->route('niveau_management')->with('success', 'Niveau créé avec succès ✅');
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:niveaux,id',
            'nom_niveau' => 'required|string|max:255',
            'code_niveau' => 'required|string|max:50|unique:niveaux,code_niveau,' . $request->id,
            'id_cycle' => 'required|exists:cycles,id',
        ]);

        $niveau = niveau::findOrFail($request->id);

        $niveau->update([
            'nom_niveau' => $request->nom_niveau,
            'code_niveau' => $request->code_niveau,
            'id_cycle' => $request->id_cycle,
            'id_user' => Auth::id(),
        ]);

        return redirect()->route('niveau_management')->with('success', 'Niveau modifié avec succès ✏️');
    }

    public function destroy($id)
    {
        niveau::findOrFail($id)->delete();
        return redirect()->route('niveau_management')->with('success', 'Niveau supprimé 🗑️');
    }
}
