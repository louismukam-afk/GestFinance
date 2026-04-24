<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\entite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EntiteController extends Controller
{
    /**
     * Liste des entités
     */
    public function index()
    {
        $entites = entite::orderBy('created_at', 'desc')->get();
        $title = "Gestion des Entités";

        return view('Admin.Entite.index', compact('entites', 'title'));
    }

    /**
     * Ajouter une entité
     */
    public function store1(Request $request)
    {
        $request->validate([
            'nom_entite' => 'required|string|max:255',
            'localisation' => 'required|string|max:255',
            'telephone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|string|max:255',
        ]);

        entite::create([
            'nom_entite' => $request->nom_entite,
            'localisation' => $request->localisation,
            'telephone' => $request->telephone,
            'email' => $request->email,
            'description' => $request->description,
            'logo' => $request->logo,
            'user' => Auth::id() ?? 0,
        ]);

        return redirect()->route('entite_management')
            ->with('success', 'Entité ajoutée avec succès ✅');
    }

    /**
     * Modifier une entité
     */
    public function update1(Request $request)
    {
        $id = $request->id;
        $entite = entite::findOrFail($id);

        $request->validate([
            'nom_entite' => 'required|string|max:255',
            'localisation' => 'required|string|max:255',
            'telephone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|string|max:255',
        ]);

        $entite->update($request->only([
            'nom_entite', 'localisation', 'telephone', 'email', 'description', 'logo'
        ]));

        return redirect()->route('entite_management')
            ->with('success', 'Entité modifiée avec succès ✏️');
    }
    public function store(Request $request)
    {
        $request->validate([
            'nom_entite'   => 'required|string|max:255',
            'localisation' => 'required|string|max:255',
            'telephone'    => 'required|string|max:20',
            'email'        => 'nullable|email|max:255',
            'description'  => 'nullable|string',
            'logo'         => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $fileName = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('uploads/images'), $fileName);
            $logoPath = 'uploads/images/'.$fileName; // chemin enregistré en base
        }

        entite::create([
            'nom_entite'   => $request->nom_entite,
            'localisation' => $request->localisation,
            'telephone'    => $request->telephone,
            'email'        => $request->email,
            'description'  => $request->description,
            'logo'         => $logoPath,
            'user'         => Auth::id() ?? 0,
        ]);

        return redirect()->route('entite_management')
            ->with('success', 'Entité ajoutée avec succès ✅');
    }


    public function update(Request $request)
    {
        $id = $request->id;
        $entite = entite::findOrFail($id);

        $request->validate([
            'nom_entite'   => 'required|string|max:255',
            'localisation' => 'required|string|max:255',
            'telephone'    => 'required|string|max:20',
            'email'        => 'nullable|email|max:255',
            'description'  => 'nullable|string',
            'logo'         => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $data = $request->only(['nom_entite', 'localisation', 'telephone', 'email', 'description']);

        if ($request->hasFile('logo')) {
            // Supprimer l’ancien fichier si existe
            if ($entite->logo && file_exists(public_path($entite->logo))) {
                unlink(public_path($entite->logo));
            }

            $file = $request->file('logo');
            $fileName = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('uploads/images'), $fileName);
            $data['logo'] = 'uploads/images/'.$fileName;
        }

        $entite->update($data);

        return redirect()->route('entite_management')
            ->with('success', 'Entité modifiée avec succès ✏️');
    }



    /**
     * Supprimer une entité
     */
    public function destroy($id)
    {
        $entite = entite::findOrFail($id);
        $entite->delete();

        return redirect()->route('entite_management')
            ->with('success', 'Entité supprimée avec succès 🗑️');
    }
}
