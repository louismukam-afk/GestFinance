<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\personnel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class PersonnelController extends Controller
{
    public function index()
    {
        $personnels = personnel::orderBy('created_at', 'desc')->get();
        $title = "Gestion du Personnel";
        return view('Admin.Personnel.index', compact('personnels', 'title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom'                 => 'required|string|max:255',
            'date_naissance'      => 'required|date',
            'lieu_naissance'      => 'required|string|max:255',
            'adresse'             => 'required|string|max:255',
            'sexe'                => 'required|in:Masculin,Féminin,Autre',
            'statut_matrimonial'  => 'required|in:Célibataire,Marié(e),Divorcé(e),Veuf(ve)',
            'email'               => 'nullable|email|max:255',
            'telephone'           => 'required|string|max:50',
            'telephone_whatsapp'  => 'nullable|string|max:50',
            'diplome'             => 'nullable|string|max:255',
            'niveau_etude'        => 'nullable|string|max:255',
            'domaine_formation'   => 'nullable|string|max:255',
            'date_recrutement'    => 'required|date',
            'nationalite'         => 'required|string|max:100',
        ]);

        personnel::create([
            'nom'                 => $request->nom,
            'date_naissance'      => $request->date_naissance,
            'lieu_naissance'      => $request->lieu_naissance,
            'adresse'             => $request->adresse,
            'sexe'                => $request->sexe,
            'statut_matrimonial'  => $request->statut_matrimonial,
            'email'               => $request->email,
            'telephone'           => $request->telephone,
            'telephone_whatsapp'  => $request->telephone_whatsapp,
            'diplome'             => $request->diplome,
            'niveau_etude'        => $request->niveau_etude,
            'domaine_formation'   => $request->domaine_formation,
            'date_recrutement'    => $request->date_recrutement,
            'nationalite'         => $request->nationalite,
            'id_user'             => Auth::id() ?? 0,
        ]);

        return redirect()->route('personnel_management')->with('success', 'Membre du personnel ajouté avec succès ✅');
    }

    public function update(Request $request)
    {
        $request->validate([
            'id'                  => 'required|integer|exists:personnels,id',
            'nom'                 => 'required|string|max:255',
            'date_naissance'      => 'required|date',
            'lieu_naissance'      => 'required|string|max:255',
            'adresse'             => 'required|string|max:255',
            'sexe'                => 'required|in:Masculin,Féminin,Autre',
            'statut_matrimonial'  => 'required|in:Célibataire,Marié(e),Divorcé(e),Veuf(ve)',
            'email'               => 'nullable|email|max:255',
            'telephone'           => 'required|string|max:50',
            'telephone_whatsapp'  => 'nullable|string|max:50',
            'diplome'             => 'nullable|string|max:255',
            'niveau_etude'        => 'nullable|string|max:255',
            'domaine_formation'   => 'nullable|string|max:255',
            'date_recrutement'    => 'required|date',
            'nationalite'         => 'required|string|max:100',
        ]);

        $p = personnel::findOrFail($request->id);

        $p->update([
            'nom'                 => $request->nom,
            'date_naissance'      => $request->date_naissance,
            'lieu_naissance'      => $request->lieu_naissance,
            'adresse'             => $request->adresse,
            'sexe'                => $request->sexe,
            'statut_matrimonial'  => $request->statut_matrimonial,
            'email'               => $request->email,
            'telephone'           => $request->telephone,
            'telephone_whatsapp'  => $request->telephone_whatsapp,
            'diplome'             => $request->diplome,
            'niveau_etude'        => $request->niveau_etude,
            'domaine_formation'   => $request->domaine_formation,
            'date_recrutement'    => $request->date_recrutement,
            'nationalite'         => $request->nationalite,
        ]);

        return redirect()->route('personnel_management')->with('success', 'Membre du personnel modifié avec succès ✏️');
    }

    public function destroy($id)
    {
        $p = personnel::findOrFail($id);
        $p->delete();

        return redirect()->route('personnel_management')->with('success', 'Membre du personnel supprimé avec succès 🗑️');
    }
}
