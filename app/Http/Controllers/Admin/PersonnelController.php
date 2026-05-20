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
            'numero_cnps'         => 'nullable|string|max:100',
            'numero_contribuable' => 'nullable|string|max:100',
            'diplome'             => 'nullable|string|max:255',
            'niveau_etude'        => 'nullable|string|max:255',
            'domaine_formation'   => 'nullable|string|max:255',
            'date_recrutement'    => 'required|date',
            'nationalite'         => 'required|string|max:100',
            'type_personnel'      => 'nullable|in:permanent,vacataire',
            'mode_horaire'        => 'nullable|in:strict,souple',
            'categorie_horaire'   => 'nullable|in:standard,conseil_administration,chef_service,coordination',
            'horaire_travail'     => 'nullable|in:permanent_jour,permanent_soir,vacataire_jour,vacataire_soir',
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
            'numero_cnps'         => $request->numero_cnps,
            'numero_contribuable' => $request->numero_contribuable,
            'diplome'             => $request->diplome,
            'niveau_etude'        => $request->niveau_etude,
            'domaine_formation'   => $request->domaine_formation,
            'date_recrutement'    => $request->date_recrutement,
            'nationalite'         => $request->nationalite,
            'type_personnel'      => $request->type_personnel ?? 'permanent',
            'mode_horaire'        => $request->mode_horaire ?? 'strict',
            'categorie_horaire'   => $request->categorie_horaire ?? 'standard',
            'horaire_travail'     => $request->horaire_travail,
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
            'numero_cnps'         => 'nullable|string|max:100',
            'numero_contribuable' => 'nullable|string|max:100',
            'diplome'             => 'nullable|string|max:255',
            'niveau_etude'        => 'nullable|string|max:255',
            'domaine_formation'   => 'nullable|string|max:255',
            'date_recrutement'    => 'required|date',
            'nationalite'         => 'required|string|max:100',
            'type_personnel'      => 'nullable|in:permanent,vacataire',
            'mode_horaire'        => 'nullable|in:strict,souple',
            'categorie_horaire'   => 'nullable|in:standard,conseil_administration,chef_service,coordination',
            'horaire_travail'     => 'nullable|in:permanent_jour,permanent_soir,vacataire_jour,vacataire_soir',
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
            'numero_cnps'         => $request->numero_cnps,
            'numero_contribuable' => $request->numero_contribuable,
            'diplome'             => $request->diplome,
            'niveau_etude'        => $request->niveau_etude,
            'domaine_formation'   => $request->domaine_formation,
            'date_recrutement'    => $request->date_recrutement,
            'nationalite'         => $request->nationalite,
            'type_personnel'      => $request->type_personnel ?? 'permanent',
            'mode_horaire'        => $request->mode_horaire ?? 'strict',
            'categorie_horaire'   => $request->categorie_horaire ?? 'standard',
            'horaire_travail'     => $request->horaire_travail,
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
