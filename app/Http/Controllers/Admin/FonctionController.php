<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\fonction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FonctionController extends Controller
{
    public function index()
    {
        $fonctions = Fonction::orderBy('created_at', 'desc')->get();
        $title = "Gestion des Fonctions";
        return view('Admin.Fonction.index', compact('fonctions', 'title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom_fonction' => 'required|string|max:255',
            'description'  => 'nullable|string',
        ]);

        fonction::create([
            'nom_fonction' => $request->nom_fonction,
            'description'  => $request->description,
            'id_user'      => Auth::id() ?? 0,
        ]);

        return redirect()->route('fonction_management')
            ->with('success', 'Fonction ajoutée avec succès ✅');
    }

    public function update(Request $request)
    {
        $request->validate([
            'id'           => 'required|integer|exists:fonctions,id',
            'nom_fonction' => 'required|string|max:255',
            'description'  => 'nullable|string',
        ]);

        $fonction = fonction::findOrFail($request->id);

        $fonction->update([
            'nom_fonction' => $request->nom_fonction,
            'description'  => $request->description,
        ]);

        return redirect()->route('fonction_management')
            ->with('success', 'Fonction modifiée avec succès ✏️');
    }

    public function destroy($id)
    {
        $fonction = fonction::findOrFail($id);
        $fonction->delete();

        return redirect()->route('fonction_management')
            ->with('success', 'Fonction supprimée avec succès 🗑️');
    }
}
