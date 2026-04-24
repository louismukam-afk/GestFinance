<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\banque;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BanqueController extends Controller
{
    /**
     * Liste des banques
     */
    public function index()
    {
        $banques = banque::orderBy('created_at', 'desc')->get();
        $title = "Gestion des Banques";

        return view('Admin.Banque.index', compact('banques', 'title'));
    }

    /**
     * Ajouter une banque
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom_banque' => 'required|string|max:255',
            'telephone' => 'required|string|max:20',
            'localisation' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:banques,code',
            'description' => 'nullable|string',
            'email' => 'nullable|email|max:255',
        ]);

        banque::create([
            'nom_banque' => $request->nom_banque,
            'telephone' => $request->telephone,
            'localisation' => $request->localisation,
            'code' => $request->code,
            'description' => $request->description,
            'email' => $request->email,
            'id_user' => Auth::id() ?? 0,
        ]);

        return redirect()->route('banque_management')
            ->with('success', 'Banque ajoutée avec succès ✅');
    }

    /**
     * Modifier une banque
     */
    public function update(Request $request)
    {
        $id = $request->id;
        $banque = banque::findOrFail($id);

        $request->validate([
            'nom_banque' => 'required|string|max:255',
            'telephone' => 'required|string|max:20',
            'localisation' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:banques,code,' . $id,
            'description' => 'nullable|string',
            'email' => 'nullable|email|max:255',
        ]);

        $banque->update($request->only([
            'nom_banque', 'telephone', 'localisation', 'code', 'description', 'email'
        ]));

        return redirect()->route('banque_management')
            ->with('success', 'Banque modifiée avec succès ✏️');
    }

    /**
     * Supprimer une banque
     */
    public function destroy($id)
    {
        $banque = banque::findOrFail($id);
        $banque->delete();

        return redirect()->route('banque_management')
            ->with('success', 'Banque supprimée avec succès 🗑️');
    }
}
