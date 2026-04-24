<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\filiere;
use App\Models\specialite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SpecialiteController extends Controller
{
    public function index()
    {
        $specialites = Specialite::with('filiere')->latest()->get();
        $filieres = filiere::all();

        $title = "Gestion des spécialités";

        return view('Admin.specialites.index', compact('specialites', 'filieres', 'title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom_specialite' => 'required|string|max:255',
            'code_specialite' => 'required|string|max:50|unique:specialites,code_specialite',
            'id_filiere' => 'required|exists:filieres,id',
            'capacite' => 'nullable|integer|min:0',
        ]);

        specialite::create([
            'nom_specialite' => $request->nom_specialite,
            'code_specialite' => $request->code_specialite,
            'id_filiere' => $request->id_filiere,
            'capacite' => $request->capacite ?? 0,
            'id_user' => Auth::id(),
        ]);

        return redirect()->route('specialite_management')->with('success', 'Spécialité créée avec succès ✅');
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:specialites,id',
            'nom_specialite' => 'required|string|max:255',
            'code_specialite' => 'required|string|max:50|unique:specialites,code_specialite,' . $request->id,
            'id_filiere' => 'required|exists:filieres,id',
            'capacite' => 'nullable|integer|min:0',
        ]);

        $specialite = specialite::findOrFail($request->id);

        $specialite->update([
            'nom_specialite' => $request->nom_specialite,
            'code_specialite' => $request->code_specialite,
            'id_filiere' => $request->id_filiere,
            'capacite' => $request->capacite ?? 0,
            'id_user' => Auth::id(),
        ]);

        return redirect()->route('specialite_management')->with('success', 'Spécialité modifiée avec succès ✏️');
    }

    public function destroy($id)
    {
        specialite::findOrFail($id)->delete();
        return redirect()->route('specialite_management')->with('success', 'Spécialité supprimée 🗑️');
    }
}
