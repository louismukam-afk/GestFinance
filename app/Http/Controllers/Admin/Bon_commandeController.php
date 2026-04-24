<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\bon_commandeok;
use App\Models\entite;
use App\Models\personnel;
use Encore\Admin\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Bon_commandeController extends Controller
{
    protected $values=[];
    public function __construct()
    {
        $this->values['big_title']='Administration';

        $this->values['title']='Gestion des bons';
    }
    public function index()
    {
        $bons = bon_commandeok::with('personnels')
            ->orderBy('debut', 'desc')
            ->get()
            ->groupBy('personnel_id');
        $p=personnel::all();
        $e=entite::all();
        $this->values['personnels']=$p;
        $this->values['entites']=$e;
        $this->values['bon_comandes']=$bons;
        $this->values['title']='Gestion des bons';
        return view('Admin.index',$this->values);
    }


    public function index_bon()
    {
        $bons = bon_commandeok::with('personnels','entites')
            ->orderBy('date_debut', 'desc')
            ->get()
            ->groupBy('personnel_id')
            ; // ✅ 10 par page

        $p = personnel::all();
        $e = entite::all();

        $this->values['personnels'] = $p;
        $this->values['entites'] = $e;
        $this->values['bon_comandes'] = $bons;
        $this->values['title']='Gestion des bons';
        // 👉 Ici on charge la vue complète
        return view('Admin.index_bon', $this->values);
    }
    public function search_bon(Request $request)
    {
        $query = bon_commandeok::with('personnels','entites')
            ->orderBy('date_debut', 'desc');

        if ($request->has('search_bon') && !empty($request->search_bon)) {
            $search = $request->search_bon;
            $query->where(function($q) use ($search) {
                $q->where('nom_bon_commande', 'LIKE', "%{$search}%")
                    ->orWhere('description_bon_commande', 'LIKE', "%{$search}%");
            });
        }

        $bons = $query->get()->groupBy('personnel_id');

        $p = personnel::all();
        $e = entite::all();
        $this->values['title']='Gestion des bons';
        $this->values['personnels'] = $p;
        $this->values['entites'] = $e;
        $this->values['bon_comandes'] = $bons;

        // 👉 Ici on ne renvoie QUE le tableau partiel
        return view('Admin.partials_bons_table', $this->values);
    }

    public function index_bonvalide()
    {
        $bons = bon_commandeok::with('personnels','entites')
            ->orderBy('date_debut', 'desc')
            ->get()
            ->groupBy('personnel_id');
        $p=personnel::all();
        $e=entite::all();
        $this->values['personnels']=$p;
        $this->values['entites']=$e;
        $this->values['bon_comandes']=$bons;
        return view('Admin.index_bon',$this->values);
    }
    public function index_bonko(Request $request)
    {
        $query = bon_commandeok::with('personnels', 'entites')
            ->orderBy('date_debut', 'desc');

        // Filtre par nom de bon
        if ($request->filled('search_bon')) {
            $query->where('nom_bon_commande', 'like', '%' . $request->search_bon . '%');
        }

        // Filtre par personnel
        if ($request->filled('search_personnel')) {
            $query->where('id_personnel', $request->search_personnel);
        }

        $bons = $query->get()->groupBy('id_personnel');

        $personnels = personnel::all();
        $entites = entite::all();
        $this->values['personnels']=$personnels;
        $this->values['entites']=$entites;
        $this->values['bon_comandes']=$bons;
        // Si c'est une requête AJAX, retourner seulement le tableau
        if ($request->ajax()) {
            return view('Admin.partials_bons_table', $this->values()->render());
        }

        return view('Admin.index_bon', $this->values);
    }



    public function store(Request $request)
    {
        $this->validate($request, [
            'nom_bon_commande' => 'required|string|max:255|unique:bon_commandeoks',
            'description_bon_commande' => 'required|string',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'date_entree_signature' => 'required|date',
//            'date_validation' => 'required|date',
            'montant_total' => 'required|numeric|min:0',
            'montant_realise' => 'nullable|numeric|min:0',
            'reste' => 'nullable|numeric|min:0',
            'montant_lettre' => 'required|string',
            'id_personnel' => 'required|integer|exists:personnels,id',
//            'id_user' => 'required|integer|exists:users,id',
            'id_entite' => 'required|integer|exists:entites,id',
//            'statuts' => 'required|integer'
        ]);

        $bon = new bon_commandeok();
        $bon->nom_bon_commande = $request->input('nom_bon_commande');
        $bon->description_bon_commande = $request->input('description_bon_commande');
        $bon->date_debut = $request->input('date_debut');
        $bon->date_fin = $request->input('date_fin');
        $bon->date_entree_signature = $request->input('date_entree_signature');
//        $bon->date_validation = $request->input('date_validation');
        $bon->montant_total = $request->input('montant_total');
        $bon->montant_realise = $request->input('montant_realise');
        if ($bon->montant_realise === null) {
            $bon->montant_realise = 0;
        }
        $bon->reste = $request->input('reste', 0);
        $bon->montant_lettre = $request->input('montant_lettre');
        $bon->id_personnel = $request->input('id_personnel');
        $bon->id_user = Auth::user()->id ;// ou Auth::id() si hors de Laravel Admin
//        $bon->id_user = $request->input('id_user');
        $bon->id_entite = $request->input('id_entite');
//        $bon->statuts = $request->input('statuts');
        $bon->save();

        return redirect()->route('bon_commande_management')
            ->withSuccess(['ok' => 'Bon de commande enregistré avec succès']);
    }

    /**
     * Met à jour un bon de commande existant.
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|numeric|exists:bon_commandeoks,id',
//            'nom_bon_commande' => 'required|string|max:255|unique:bon_commandeoks,nom_bon_commande,' . $request->id,
            'nom_bon_commande' => 'required',
            'description_bon_commande' => 'required|string',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'date_entree_signature' => 'required|date',
//            'date_validation' => 'required|date',
            'montant_total' => 'required|numeric|min:0',
            'montant_realise' => 'nullable|numeric|min:0',
            'reste' => 'nullable|numeric|min:0',
            'montant_lettre' => 'required|string',
            'id_personnel' => 'required|integer|exists:personnels,id',
//            'id_user' => 'required|integer|exists:users,id',
            'id_entite' => 'required|integer|exists:entites,id',
//            'statuts' => 'required|integer'
        ]);

        $bon = bon_commandeok::find($request->id);
        $bon->nom_bon_commande = $request->input('nom_bon_commande');
        $bon->description_bon_commande = $request->input('description_bon_commande');
        $bon->date_debut = $request->input('date_debut');
        $bon->date_fin = $request->input('date_fin');
        $bon->date_entree_signature = $request->input('date_entree_signature');
//        $bon->date_validation = $request->input('date_validation');
        $bon->montant_total = $request->input('montant_total');
        $bon->montant_realise = $request->input('montant_realise', 0);
        $bon->reste = $request->input('reste', 0);
        $bon->montant_lettre = $request->input('montant_lettre');
        $bon->id_personnel = $request->input('id_personnel');
       // $bon->id_user = $request->input('id_user');
        $bon->id_entite = $request->input('id_entite');
//        $bon->statuts = $request->input('statuts');
        $bon->save();

        return redirect()->route('bon_commande_management')
            ->withSuccess(['ok' => 'Bon de commande mis à jour avec succès']);
    }
    public function validerPDG($id)
    {
        $commande = bon_commandeok::findOrFail($id);
        $commande->validation_pdg = 1;
        $this->updateStatut($commande);
        admin_toastr('Validation PDG réussie', 'success');
        return redirect(admin_url('bon_commande'));
    }
    public function validerDAF($id)
    {
        $commande = bon_commandeok::findOrFail($id);
        $commande->validation_daf = 1;
        $this->updateStatut($commande);
        admin_toastr('Validation DAF réussie', 'success');
        return redirect(admin_url('bon_commande'));
    }

    public function validerAchats($id)
    {
        $commande = bon_commandeok::findOrFail($id);
        $commande->validation_achats = 1;
        $this->updateStatut($commande);
        admin_toastr('Validation Achats réussie', 'success');
        return redirect(admin_url('bon_commande'));
    }

    public function validerEmetteur($id)
    {
        $commande = bon_commandeok::findOrFail($id);
        $commande->validation_emetteur = 1;
        $this->updateStatut($commande);
        admin_toastr('Validation Émetteur réussie', 'success');
        return redirect(admin_url('bon_commande'));
    }

    private function updateStatut($commande)
    {
        // Si toutes les validations sont faites, on valide le bon de commande
        if (
            $commande->validation_pdg &&
            $commande->validation_daf &&
            $commande->validation_achats &&
            $commande->validation_emetteur
        ) {
            $commande->statuts = 1; // validé
            $commande->date_validation = now(); // Enregistre la date actuelle
        }

        $commande->save();
    }
    public function destroy($id)
    {
        $bon = bon_commandeok::findOrFail($id);
        $bon->delete();

        return redirect()->route('bon_commande_management')
            ->withSuccess(['ok' => 'Bon de commande supprimé avec succès']);
    }
}
