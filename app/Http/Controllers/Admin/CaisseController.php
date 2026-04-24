<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\caisse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CaisseController extends Controller
{
    /**
     * Afficher la liste des caisses
     */
    public function index()
    {
        $caisses = caisse::orderBy('created_at', 'desc')->get();
        $title = "Gestion des Caisses";

        return view('Admin.Caisse.index', compact('caisses', 'title'));
    }

    /**
     * Enregistrer une nouvelle caisse
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom_caisse' => 'required|string|max:255',
            'code_caisse' => 'required|string|max:50|unique:caisses,code_caisse',
            'description' => 'nullable|string',
            'type_caisse' => 'nullable|integer',
        ]);

        caisse::create([
            'nom_caisse' => $request->nom_caisse,
            'type_caisse' => $request->type_caisse,
            'status_caisse' => $request->status_caisse,
            'code_caisse' => $request->code_caisse,
            'description' => $request->description,
            'id_user' => Auth::id() ?? 0,
        ]);

        return redirect()->route('caisse_management')
            ->with('success', 'Caisse ajoutée avec succès ✅');
    }

    /**
     * Mettre à jour une caisse
     */
    public function update(Request $request)
    {
        $id = $request->id;
        $caisse = caisse::findOrFail($id);

        $request->validate([
            'nom_caisse' => 'required|string|max:255',
            'code_caisse' => 'required|string|max:50|unique:caisses,code_caisse,' . $id,
            'description' => 'nullable|string',
        ]);

        $caisse->update($request->only(['nom_caisse','status_caisse', 'type_caisse', 'code_caisse', 'description']));

        return redirect()->route('caisse_management')
            ->with('success', 'Caisse modifiée avec succès ✏️');
    }

    /**
     * Supprimer une caisse
     */
    public function destroy($id)
    {
        $caisse = caisse::findOrFail($id);
        $caisse->delete();

        return redirect()->route('caisse_management')
            ->with('success', 'Caisse supprimée avec succès 🗑️');
    }
}
