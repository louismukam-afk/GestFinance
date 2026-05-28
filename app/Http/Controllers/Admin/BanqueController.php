<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BanqueUser;
use App\Models\banque;
use App\Models\User;
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

    public function affectations()
    {
        $affectations = BanqueUser::with(['banque', 'user'])
            ->orderByDesc('created_at')
            ->get();
        $banques = banque::orderBy('nom_banque')->get();
        $users = User::orderBy('name')->get();
        $title = 'Affectation des banques aux utilisateurs';

        return view('Admin.Banque.affectations', compact('affectations', 'banques', 'users', 'title'));
    }

    public function storeAffectation(Request $request)
    {
        $data = $request->validate([
            'id_banque' => 'required|integer|exists:banques,id',
            'id_user' => 'required|integer|exists:users,id',
            'peut_encaisser' => 'nullable|boolean',
            'peut_decaisser' => 'nullable|boolean',
            'actif' => 'nullable|boolean',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'observation' => 'nullable|string',
        ]);

        $data['peut_encaisser'] = $request->boolean('peut_encaisser');
        $data['peut_decaisser'] = $request->boolean('peut_decaisser');
        $data['actif'] = $request->boolean('actif', true);

        if (!$data['peut_encaisser'] && !$data['peut_decaisser']) {
            return back()->withInput()->with('error', 'Veuillez cocher au moins encaissement ou decaissement.');
        }

        BanqueUser::updateOrCreate(
            ['id_banque' => $data['id_banque'], 'id_user' => $data['id_user']],
            $data
        );

        return back()->with('success', 'Affectation de banque enregistree.');
    }

    public function destroyAffectation($id)
    {
        BanqueUser::findOrFail($id)->delete();

        return back()->with('success', 'Affectation supprimee.');
    }
}
