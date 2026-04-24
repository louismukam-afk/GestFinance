<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\annee_academique;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnneeAcademiqueController extends Controller
{
    public function index()
    {
        $annees = annee_academique::orderBy('created_at', 'desc')->get();
        $title = "Gestion des Années Académiques";
        return view('Admin.AnneeAcademique.index', compact('annees', 'title'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom'         => 'required|string|max:255',   // tu peux ajouter unique si nécessaire: |unique:annee_academiques,nom
            'description' => 'nullable|string',
        ]);

        $data['id_user'] = Auth::id() ?? 0;

        annee_academique::create($data);

        return redirect()->route('annee_academique_management')
            ->with('success', 'Année académique ajoutée avec succès ✅');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'id'          => 'required|integer|exists:annee_academiques,id',
            'nom'         => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $a = annee_academique::findOrFail($data['id']);
        $a->update([
            'nom'         => $data['nom'],
            'description' => $data['description'] ?? null,
        ]);

        return redirect()->route('annee_academique_management')
            ->with('success', 'Année académique modifiée avec succès ✏️');
    }

    public function destroy($id)
    {
        $a = annee_academique::findOrFail($id);
        $a->delete();

        return redirect()->route('annee_academique_management')
            ->with('success', 'Année académique supprimée avec succès 🗑️');
    }
}
