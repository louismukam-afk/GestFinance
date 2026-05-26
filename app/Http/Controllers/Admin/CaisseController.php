<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CaisseUser;
use App\Models\caisse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CaisseController extends Controller
{
    public function index()
    {
        $caisses = caisse::orderBy('created_at', 'desc')->get();
        $title = 'Gestion des Caisses';

        return view('Admin.Caisse.index', compact('caisses', 'title'));
    }

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
            ->with('success', 'Caisse ajoutee avec succes.');
    }

    public function update(Request $request)
    {
        $id = $request->id;
        $caisse = caisse::findOrFail($id);

        $request->validate([
            'nom_caisse' => 'required|string|max:255',
            'code_caisse' => 'required|string|max:50|unique:caisses,code_caisse,' . $id,
            'description' => 'nullable|string',
        ]);

        $caisse->update($request->only(['nom_caisse', 'status_caisse', 'type_caisse', 'code_caisse', 'description']));

        return redirect()->route('caisse_management')
            ->with('success', 'Caisse modifiee avec succes.');
    }

    public function destroy($id)
    {
        caisse::findOrFail($id)->delete();

        return redirect()->route('caisse_management')
            ->with('success', 'Caisse supprimee avec succes.');
    }

    public function affectations()
    {
        $affectations = CaisseUser::with(['caisse', 'user'])
            ->orderByDesc('created_at')
            ->get();
        $caisses = caisse::orderBy('nom_caisse')->get();
        $users = User::orderBy('name')->get();
        $title = 'Affectation des caisses aux utilisateurs';

        return view('Admin.Caisse.affectations', compact('affectations', 'caisses', 'users', 'title'));
    }

    public function storeAffectation(Request $request)
    {
        $data = $request->validate([
            'id_caisse' => 'required|integer|exists:caisses,id',
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

        CaisseUser::updateOrCreate(
            ['id_caisse' => $data['id_caisse'], 'id_user' => $data['id_user']],
            $data
        );

        return back()->with('success', 'Affectation de caisse enregistree.');
    }

    public function destroyAffectation($id)
    {
        CaisseUser::findOrFail($id)->delete();

        return back()->with('success', 'Affectation supprimee.');
    }
}
