<?php

namespace App\Http\Controllers\Admin;

use App\Exports\EtudiantsTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\EtudiantsImport;
use App\Models\Etudiant;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class EtudiantController extends Controller
{
    public function index()
    {
        $etudiants = Etudiant::orderBy('created_at', 'desc')->get();
        $title = "Gestion des Étudiants";
        return view('Admin.Etudiant.index', compact('etudiants', 'title'));
    }

    public function import()
    {
        $title = "Importation des etudiants";

        return view('Admin.Etudiant.import', compact('title'));
    }

    public function downloadTemplate()
    {
        return Excel::download(new EtudiantsTemplateExport(), 'template_import_etudiants.xlsx');
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'fichier' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $import = new EtudiantsImport();
        Excel::import($import, $request->file('fichier'));

        return redirect()->route('etudiants.import')
            ->with('success', $import->createdCount() . ' etudiant(s) importe(s).')
            ->with('warning', $import->skippedCount() . ' ligne(s) ignoree(s).')
            ->with('import_errors', $import->errors());
    }

   /* public function store(Request $request)
    {
        $request->validate([
            'nom'                      => 'required|string|max:255',
            'telephone_whatsapp'       => 'required|string|max:30',
            'date_naissance'           => 'required|date',
            'lieu_naissance'           => 'required|string|max:255',
            'sexe'                     => 'required|string|in:Masculin,Féminin,Autre',
            'email'                    => 'nullable|email|max:255',
            'adresse'                  => 'nullable|string|max:255',
            'departement_origine'      => 'nullable|string|max:255',
            'region_origine'           => 'nullable|string|max:255',
            'nom_pere'                 => 'nullable|string|max:255',
            'telephone_whatsapp_pere'  => 'nullable|string|max:30',
            'nom_mere'                 => 'nullable|string|max:255',
            'nom_tuteur'               => 'nullable|string|max:255',
            'telephone_tuteur'         => 'nullable|string|max:30',
            'matricule'                => 'required|string|max:100|unique:etudiants,matricule',
            'telephone_2_etudiants'    => 'nullable|string|max:30',
            'adresse_tuteur'           => 'nullable|string|max:255',
            'photo'                    => 'nullable|image|mimes:jpg,jpeg,png,gif|max:4096',
            'dernier_etablissement_frequente' => 'nullable|string|max:255',
        ]);

        $path = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $fileName = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('uploads/images'), $fileName);
            $path = 'uploads/images/'.$fileName; // chemin relatif enregistré en DB
        }

        Etudiant::create(array_merge(
            $request->except('photo'),
            ['photo' => $path]
        ));

        return redirect()->route('etudiant_management')->with('success', 'Étudiant ajouté avec succès ✅');
    }

    public function update(Request $request)
    {
        $id = $request->id;
        $etudiant = Etudiant::findOrFail($id);

        $request->validate([
            'nom'                      => 'required|string|max:255',
            'telephone_whatsapp'       => 'required|string|max:30',
            'date_naissance'           => 'required|date',
            'lieu_naissance'           => 'required|string|max:255',
            'sexe'                     => 'required|string|in:Masculin,Féminin,Autre',
            'email'                    => 'nullable|email|max:255',
            'adresse'                  => 'nullable|string|max:255',
            'departement_origine'      => 'nullable|string|max:255',
            'region_origine'           => 'nullable|string|max:255',
            'nom_pere'                 => 'nullable|string|max:255',
            'telephone_whatsapp_pere'  => 'nullable|string|max:30',
            'nom_mere'                 => 'nullable|string|max:255',
            'nom_tuteur'               => 'nullable|string|max:255',
            'telephone_tuteur'         => 'nullable|string|max:30',
            'matricule'                => 'required|string|max:100|unique:etudiants,matricule,' . $id,
            'telephone_2_etudiants'    => 'nullable|string|max:30',
            'adresse_tuteur'           => 'nullable|string|max:255',
            'photo'                    => 'nullable|image|mimes:jpg,jpeg,png,gif|max:4096',
            'dernier_etablissement_frequente' => 'nullable|string|max:255',
        ]);

        $data = $request->except('photo');

        if ($request->hasFile('photo')) {
            // supprimer l’ancienne photo si existe
            if ($etudiant->photo && file_exists(public_path($etudiant->photo))) {
                @unlink(public_path($etudiant->photo));
            }
            $file = $request->file('photo');
            $fileName = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('uploads/images'), $fileName);
            $data['photo'] = 'uploads/images/'.$fileName;
        }

        $etudiant->update($data);

        return redirect()->route('etudiant_management')->with('success', 'Étudiant modifié avec succès ✏️');
    }*/
    // app/Http/Controllers/Admin/EtudiantController.php

    /**
     * Enregistrer un étudiant (photo optionnelle, champs optionnels acceptés vides)
     */

    public function store(Request $req)
    {
        $this->validate($req, [
            'nom'            => 'required|string|max:255',
            'date_naissance' => 'nullable|date',
            'date_naiss'     => 'nullable|date',
            'lieu_naissance' => 'nullable|string|max:255',
            'lieu_naiss'     => 'nullable|string|max:255',
            'sexe'           => 'required|string|max:20',
            'matricule'      => 'nullable|string|max:100|unique:etudiants,matricule',
            'email'          => 'nullable|email|max:255',
            'photo'          => 'nullable|image|mimes:jpg,jpeg,png,gif|max:4096',
        ]);

        // helper pour normaliser les champs optionnels
        $norm = function ($v) {
            if ($v === null) return "";               // DB NOT NULL => ""
            $v = trim((string)$v);
            if ($v === "" || strtolower($v) === "null") return "";
            return $v;
        };

        $e = new Etudiant();

        // requis
        $e->nom            = $req->input('nom');
        $e->sexe           = $req->input('sexe');

        // compat champs
        $e->date_naissance = $req->input('date_naissance') ?? $req->input('date_naiss');
        $e->lieu_naissance = $req->input('lieu_naissance') ?? $req->input('lieu_naiss');

        // optionnels normalisés en "" si vides/null
        $e->telephone_whatsapp       = $norm($req->input('telephone_whatsapp'));
        $e->email                    = $norm($req->input('email'));
        $e->adresse                  = $norm($req->input('adresse'));
        $e->departement_origine      = $norm($req->input('departement_origine'));
        $e->region_origine           = $norm($req->input('region_origine'));
        $e->nom_pere                 = $norm($req->input('nom_pere'));
        $e->telephone_whatsapp_pere  = $norm($req->input('telephone_whatsapp_pere'));
        $e->nom_mere                 = $norm($req->input('nom_mere'));
        $e->nom_tuteur               = $norm($req->input('nom_tuteur'));
        $e->telephone_tuteur         = $norm($req->input('telephone_tuteur'));
        $e->matricule                = $norm($req->input('matricule'));
        $e->telephone_2_etudiants    = $norm($req->input('telephone_2_etudiants'));
        $e->adresse_tuteur           = $norm($req->input('adresse_tuteur'));
        $e->dernier_etablissement_frequente = $norm($req->input('dernier_etablissement_frequente'));

        // photo optionnelle
        if ($req->hasFile('photo')) {
            @mkdir(public_path('uploads/images'), 0775, true);
            $file = $req->file('photo');
            $name = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('uploads/images'), $name);
            $e->photo = 'uploads/images/'.$name;
        } else {
            $e->photo = ""; // NOT NULL -> chaine vide
        }

        $e->save();

        return redirect()->route('etudiant_management')
            ->with('success', 'Étudiant enregistré avec succès ✅');
    }

    public function store2(Request $req)
    {
        // Validation à la manière de ton exemple
        $this->validate($req, [
            'nom'           => 'required|string|max:255',
            // on accepte date_naissance OU date_naiss
            'date_naissance'=> 'nullable|date',
            'date_naiss'    => 'nullable|date',
            // on accepte lieu_naissance OU lieu_naiss
            'lieu_naissance'=> 'nullable|string|max:255',
            'lieu_naiss'    => 'nullable|string|max:255',

            'sexe'          => 'required|string|max:20',
            'matricule'     => 'nullable|string|max:100|unique:etudiants,matricule',
            'email'         => 'nullable|email|max:255',
            'photo'         => 'nullable|image|mimes:jpg,jpeg,png,gif|max:4096',
        ]);

        $e = new Etudiant();

        // Champs requis / principaux
        $e->nom             = $req->input('nom');
        $e->sexe            = $req->input('sexe');

        // Compat noms champs (form vs migration)
        $e->date_naissance  = $req->input('date_naissance') ?? $req->input('date_naiss');
        $e->lieu_naissance  = $req->input('lieu_naissance') ?? $req->input('lieu_naiss');

        // Champs optionnels (acceptent null/vides)
        $e->telephone_whatsapp        = $req->input('telephone_whatsapp');
        $e->email                     = $req->input('email');
        $e->adresse                   = $req->input('adresse');
        $e->departement_origine       = $req->input('departement_origine');
        $e->region_origine            = $req->input('region_origine');
        $e->nom_pere                  = $req->input('nom_pere');
        $e->telephone_whatsapp_pere   = $req->input('telephone_whatsapp_pere');
        $e->nom_mere                  = $req->input('nom_mere');
        $e->nom_tuteur                = $req->input('nom_tuteur');
        $e->telephone_tuteur          = $req->input('telephone_tuteur');
        $e->matricule                 = $req->input('matricule');
        $e->telephone_2_etudiants     = $req->input('telephone_2_etudiants');
        $e->adresse_tuteur            = $req->input('adresse_tuteur');
        $e->dernier_etablissement_frequente = $req->input('dernier_etablissement_frequente');

        // PHOTO (optionnelle) : on enregistre le chemin relatif en DB (ex: uploads/images/xxxx.jpg)
        $photo = $req->file('photo');
        if ($photo) {
            // s’assurer que le dossier existe (création silencieuse)
            @mkdir(public_path('uploads/images'), 0775, true);
            $fileName = time().'_'.$photo->getClientOriginalName();
            $photo->move(public_path('uploads/images'), $fileName);
            $e->photo = 'uploads/images/'.$fileName;
        } else {
            $e->photo = null; // ok si colonne nullable, sinon laisse vide
        }

        $e->save();

        return redirect()->route('etudiant_management')
            ->with('success', 'Étudiant enregistré avec succès ✅');
    }

    /**
     * Mettre à jour un étudiant (photo remplaçable simplement)
     */

    public function update(Request $req)
    {
        $this->validate($req, [
            'id'             => 'required|integer|exists:etudiants,id',
            'nom'            => 'required|string|max:255',
            'date_naissance' => 'nullable|date',
            'date_naiss'     => 'nullable|date',
            'lieu_naissance' => 'nullable|string|max:255',
            'lieu_naiss'     => 'nullable|string|max:255',
            'sexe'           => 'required|string|max:20',
            'matricule'      => 'nullable|string|max:100|unique:etudiants,matricule,'.$req->id,
            'email'          => 'nullable|email|max:255',
            'photo'          => 'nullable|image|mimes:jpg,jpeg,png,gif|max:4096',
        ]);

        $norm = function ($v) {
            if ($v === null) return "";
            $v = trim((string)$v);
            if ($v === "" || strtolower($v) === "null") return "";
            return $v;
        };

        $e = Etudiant::findOrFail($req->id);

        // requis
        $e->nom            = $req->input('nom');
        $e->sexe           = $req->input('sexe');
        $e->date_naissance = $req->input('date_naissance') ?? $req->input('date_naiss');
        $e->lieu_naissance = $req->input('lieu_naissance') ?? $req->input('lieu_naiss');

        // optionnels normalisés
        $e->telephone_whatsapp       = $norm($req->input('telephone_whatsapp'));
        $e->email                    = $norm($req->input('email'));
        $e->adresse                  = $norm($req->input('adresse'));
        $e->departement_origine      = $norm($req->input('departement_origine'));
        $e->region_origine           = $norm($req->input('region_origine'));
        $e->nom_pere                 = $norm($req->input('nom_pere'));
        $e->telephone_whatsapp_pere  = $norm($req->input('telephone_whatsapp_pere'));
        $e->nom_mere                 = $norm($req->input('nom_mere'));
        $e->nom_tuteur               = $norm($req->input('nom_tuteur'));
        $e->telephone_tuteur         = $norm($req->input('telephone_tuteur'));
        $e->matricule                = $norm($req->input('matricule'));
        $e->telephone_2_etudiants    = $norm($req->input('telephone_2_etudiants'));
        $e->adresse_tuteur           = $norm($req->input('adresse_tuteur'));
        $e->dernier_etablissement_frequente = $norm($req->input('dernier_etablissement_frequente'));

        // photo (remplacement simple si nouveau fichier)
        if ($req->hasFile('photo')) {
            if ($e->photo && file_exists(public_path($e->photo))) {
                @unlink(public_path($e->photo));
            }
            @mkdir(public_path('uploads/images'), 0775, true);
            $file = $req->file('photo');
            $name = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('uploads/images'), $name);
            $e->photo = 'uploads/images/'.$name;
        }
        // sinon on garde la photo existante (qui peut être "" si jamais)

        $e->save();

        return redirect()->route('etudiant_management')
            ->with('success', 'Étudiant modifié avec succès ✏️');
    }

    public function update2(Request $req)
    {
        $this->validate($req, [
            'id'             => 'required|integer|exists:etudiants,id',
            'nom'            => 'required|string|max:255',
            'date_naissance' => 'nullable|date',
            'date_naiss'     => 'nullable|date',
            'lieu_naissance' => 'nullable|string|max:255',
            'lieu_naiss'     => 'nullable|string|max:255',
            'sexe'           => 'required|string|max:20',
            'matricule'      => 'nullable|string|max:100|unique:etudiants,matricule,'.$req->id,
            'email'          => 'nullable|email|max:255',
            'photo'          => 'nullable|image|mimes:jpg,jpeg,png,gif|max:4096',
        ]);

        $e = Etudiant::findOrFail($req->id);

        // Champs principaux
        $e->nom             = $req->input('nom');
        $e->sexe            = $req->input('sexe');
        $e->date_naissance  = $req->input('date_naissance') ?? $req->input('date_naiss');
        $e->lieu_naissance  = $req->input('lieu_naissance') ?? $req->input('lieu_naiss');

        // Champs optionnels
        $e->telephone_whatsapp        = $req->input('telephone_whatsapp');
        $e->email                     = $req->input('email');
        $e->adresse                   = $req->input('adresse');
        $e->departement_origine       = $req->input('departement_origine');
        $e->region_origine            = $req->input('region_origine');
        $e->nom_pere                  = $req->input('nom_pere');
        $e->telephone_whatsapp_pere   = $req->input('telephone_whatsapp_pere');
        $e->nom_mere                  = $req->input('nom_mere');
        $e->nom_tuteur                = $req->input('nom_tuteur');
        $e->telephone_tuteur          = $req->input('telephone_tuteur');
        $e->matricule                 = $req->input('matricule');
        $e->telephone_2_etudiants     = $req->input('telephone_2_etudiants');
        $e->adresse_tuteur            = $req->input('adresse_tuteur');
        $e->dernier_etablissement_frequente = $req->input('dernier_etablissement_frequente');

        // PHOTO (nouvelle fournie → on remplace simplement)
        if ($req->hasFile('photo')) {
            $photo = $req->file('photo');

            // supprimer l’ancienne si elle existe
            if ($e->photo && file_exists(public_path($e->photo))) {
                @unlink(public_path($e->photo));
            }

            @mkdir(public_path('uploads/images'), 0775, true);
            $fileName = time().'_'.$photo->getClientOriginalName();
            $photo->move(public_path('uploads/images'), $fileName);
            $e->photo = 'uploads/images/'.$fileName;
        }
        // sinon on ne touche pas à la valeur existante

        $e->save();

        return redirect()->route('etudiant_management')
            ->with('success', 'Étudiant modifié avec succès ✏️');
    }



    public function destroy($id)
    {
        $etudiant = Etudiant::findOrFail($id);

        // supprimer la photo du disque si existe
        if ($etudiant->photo && file_exists(public_path($etudiant->photo))) {
            @unlink(public_path($etudiant->photo));
        }

        $etudiant->delete();
        return redirect()->route('etudiant_management')->with('success', 'Étudiant supprimé avec succès 🗑️');
    }
}
