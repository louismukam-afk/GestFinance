<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\frais;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FraisController extends Controller
{
    public function index()
    {
        $frais = frais::orderBy('created_at', 'desc')->get();
        $title = "Gestion des Frais";
        return view('Admin.Frais.index', compact('frais', 'title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom_frais'   => 'required|string|max:255',
            'description' => 'nullable|string',
            'type_frais'  => 'required|in:0,1',               // hidden 0/1 vient du JS
            'montant'     => 'required_if:type_frais,1|numeric|min:0',
        ], [
            'montant.required_if' => 'Le montant est requis pour un frais en espèce.'
        ]);

        $type    = (int) $request->type_frais;
        $montant = $type === frais::TYPE_ESPECE ? (float) ($request->montant ?? 0) : 0.0;

        frais::create([
            'nom_frais'   => $request->nom_frais,
            'description' => $request->description,
            'type_frais'  => $type,
            'montant'     => $montant,
            'id_user'     => Auth::id() ?? 0,
        ]);

        return redirect()->route('frais_management')->with('success', 'Frais ajouté avec succès ✅');
    }

    public function update(Request $request)
    {
        $request->validate([
            'id'          => 'required|integer|exists:frais,id',
            'nom_frais'   => 'required|string|max:255',
            'description' => 'nullable|string',
            'type_frais'  => 'required|in:0,1',
            'montant'     => 'required_if:type_frais,1|numeric|min:0',
        ], [
            'montant.required_if' => 'Le montant est requis pour un frais en espèce.'
        ]);

        $frais = frais::findOrFail($request->id);

        $type    = (int) $request->type_frais;
        $montant = $type === frais::TYPE_ESPECE ? (float) ($request->montant ?? 0) : 0.0;

        $frais->update([
            'nom_frais'   => $request->nom_frais,
            'description' => $request->description,
            'type_frais'  => $type,
            'montant'     => $montant,
        ]);

        return redirect()->route('frais_management')->with('success', 'Frais modifié avec succès ✏️');
    }

    /**
     * Suppression
     */
    public function destroy($id)
    {
        $frais = frais::findOrFail($id);
        $frais->delete();

        return redirect()->route('frais_management')->with('success', 'Frais supprimé avec succès 🗑️');
    }
}
