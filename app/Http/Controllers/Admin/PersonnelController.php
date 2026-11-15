<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PersonnelsTemplateExport;
use App\Exports\ViewExport;
use App\Http\Controllers\Controller;
use App\Imports\PersonnelsImport;
use App\Models\personnel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class PersonnelController extends Controller
{
    public function index()
    {
        $personnels = personnel::orderBy('created_at', 'desc')->get();
        $title = 'Gestion du Personnel';

        return view('Admin.Personnel.index', compact('personnels', 'title'));
    }

    public function import()
    {
        $title = 'Importation des personnels';

        return view('Admin.Personnel.import', compact('title'));
    }

    public function downloadTemplate()
    {
        return Excel::download(new PersonnelsTemplateExport(), 'template_import_personnels.xlsx');
    }

    public function exportExcel()
    {
        $personnels = personnel::orderBy('nom')->get();

        return Excel::download(
            new ViewExport('Admin.Personnel.excel', compact('personnels')),
            'liste_personnel.xlsx'
        );
    }

    public function store(Request $request)
    {
        $validated = $this->validatePersonnel($request);

        if ($this->duplicateExists($validated)) {
            return back()
                ->withErrors(['nom' => 'Ce personnel existe deja dans la base de donnees.'])
                ->withInput();
        }

        personnel::create($this->payload($validated) + ['id_user' => Auth::id() ?: 0]);

        return redirect()->route('personnel_management')->with('success', 'Membre du personnel ajoute avec succes.');
    }

    public function show($id)
    {
        $personnel = personnel::findOrFail($id);
        $title = 'Fiche du personnel';

        return view('Admin.Personnel.show', compact('personnel', 'title'));
    }

    public function edit($id)
    {
        $personnel = personnel::findOrFail($id);
        $title = 'Modifier le personnel';

        return view('Admin.Personnel.edit', compact('personnel', 'title'));
    }

    public function update(Request $request)
    {
        $validated = $this->validatePersonnel($request, true);
        $personnel = personnel::findOrFail($validated['id']);

        if ($this->duplicateExists($validated, $personnel->getKey())) {
            return back()
                ->withErrors(['nom' => 'Un autre personnel existe deja avec ces informations.'])
                ->withInput();
        }

        $personnel->update($this->payload($validated));

        return redirect()->route('personnel_management')->with('success', 'Membre du personnel modifie avec succes.');
    }

    public function fichePdf($id)
    {
        $personnel = personnel::findOrFail($id);
        $pdf = Pdf::loadView('Admin.Personnel.fiche_pdf', compact('personnel'));

        return $pdf->download('fiche_personnel_'.$personnel->id.'.pdf');
    }

    public function destroy($id)
    {
        $personnel = personnel::findOrFail($id);
        $personnel->delete();

        return redirect()->route('personnel_management')->with('success', 'Membre du personnel supprime avec succes.');
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'fichier' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $import = new PersonnelsImport();
        Excel::import($import, $request->file('fichier'));

        return redirect()->route('personnel_management')
            ->with('success', $import->createdCount().' personnel(s) importe(s).')
            ->with('warning', $import->skippedCount().' ligne(s) ignoree(s).')
            ->with('import_errors', $import->errors());
    }

    private function validatePersonnel(Request $request, $updating = false)
    {
        return $request->validate([
            'id'                  => $updating ? 'required|integer|exists:personnels,id' : 'nullable',
            'nom'                 => 'required|string|max:255',
            'nom_biometrie'       => 'nullable|string|max:255',
            'date_naissance'      => 'required|date',
            'lieu_naissance'      => 'required|string|max:255',
            'adresse'             => 'required|string|max:255',
            'sexe'                => 'required|in:Masculin,Feminin,Féminin,Autre',
            'statut_matrimonial'  => 'required|in:Celibataire,Célibataire,Marie(e),Marié(e),Divorce(e),Divorcé(e),Veuf(ve)',
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
    }

    private function payload(array $data)
    {
        return [
            'nom'                 => $data['nom'],
            'nom_biometrie'       => trim($data['nom_biometrie'] ?? '') ?: $data['nom'],
            'date_naissance'      => $data['date_naissance'],
            'lieu_naissance'      => $data['lieu_naissance'],
            'adresse'             => $data['adresse'],
            'sexe'                => $data['sexe'],
            'statut_matrimonial'  => $data['statut_matrimonial'],
            'email'               => isset($data['email']) ? $data['email'] : '',
            'telephone'           => $data['telephone'],
            'telephone_whatsapp'  => isset($data['telephone_whatsapp']) ? $data['telephone_whatsapp'] : '',
            'numero_cnps'         => isset($data['numero_cnps']) ? $data['numero_cnps'] : '',
            'numero_contribuable' => isset($data['numero_contribuable']) ? $data['numero_contribuable'] : '',
            'diplome'             => isset($data['diplome']) ? $data['diplome'] : '',
            'niveau_etude'        => isset($data['niveau_etude']) ? $data['niveau_etude'] : '',
            'domaine_formation'   => isset($data['domaine_formation']) ? $data['domaine_formation'] : '',
            'date_recrutement'    => $data['date_recrutement'],
            'nationalite'         => $data['nationalite'],
            'type_personnel'      => isset($data['type_personnel']) ? $data['type_personnel'] : 'permanent',
            'mode_horaire'        => isset($data['mode_horaire']) ? $data['mode_horaire'] : 'strict',
            'categorie_horaire'   => isset($data['categorie_horaire']) ? $data['categorie_horaire'] : 'standard',
            'horaire_travail'     => isset($data['horaire_travail']) ? $data['horaire_travail'] : '',
        ];
    }

    private function duplicateExists(array $data, $ignoreId = null)
    {
        $query = personnel::where('nom', $data['nom'])
            ->whereDate('date_naissance', $data['date_naissance']);

        if ($ignoreId) {
            $query->whereKeyNot($ignoreId);
        }

        if ($query->exists()) {
            return true;
        }

        if (!empty($data['email'])) {
            $emailQuery = personnel::where('email', $data['email']);

            if ($ignoreId) {
                $emailQuery->whereKeyNot($ignoreId);
            }

            return $emailQuery->exists();
        }

        return false;
    }
}
