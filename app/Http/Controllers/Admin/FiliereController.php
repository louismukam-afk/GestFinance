<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\filiere;
use Illuminate\Http\Request;

class FiliereController extends Controller
{
    public function index()
    {
        $filieres = filiere::orderBy('created_at','desc')->get();
        $title = "Gestion des Filières";

        return view('Admin.filieres.index', compact('filieres','title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom_filiere' => 'required|string|max:255',
            'code_filiere' => 'required|string|max:50|unique:filieres,code_filiere',
            'description' => 'nullable|string',
        ]);

        filiere::create([
            'nom_filiere' => $request->nom_filiere,
            'code_filiere' => $request->code_filiere,
            'description' => $request->description,
            'id_user' => auth()->id() ?? 0, // ✅ id utilisateur connecté
        ]);

        return redirect()->route('filiere_management')->with('success','Filière ajoutée avec succès ✅');
    }

    public function update(Request $request)
    {
        $id = $request->id;
        $filiere = filiere::findOrFail($id);

        $request->validate([
            'nom_filiere' => 'required|string|max:255',
            'code_filiere' => 'required|string|max:50|unique:filieres,code_filiere,'.$id,
            'description' => 'nullable|string',
        ]);

        $filiere->update([
            'nom_filiere' => $request->nom_filiere,
            'code_filiere' => $request->code_filiere,
            'description' => $request->description,
            'id_user' => auth()->id() ?? $filiere->id_user, // ✅ remet l’ID de l’utilisateur qui fait la modif
        ]);

        return redirect()->route('filiere_management')->with('success','Filière modifiée avec succès ✏️');
    }

    public function destroy($id)
    {
        filiere::findOrFail($id)->delete();
        return redirect()->route('filiere_management')->with('success','Filière supprimée 🗑️');
    }
}
