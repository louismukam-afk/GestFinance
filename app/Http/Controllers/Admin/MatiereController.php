<?php

namespace App\Http\Controllers\Admin;

use App\Exports\MatieresTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\MatieresImport;
use App\Models\Matiere;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class MatiereController extends Controller
{
    public function index()
    {
        return view('Admin.Matieres.index', [
            'title' => 'Gestion des matieres',
            'matieres' => Matiere::orderBy('nom_matiere')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom_matiere' => 'required|string|max:255',
            'code_matiere' => 'nullable|string|max:50|unique:matieres,code_matiere',
            'description' => 'nullable|string',
        ]);

        $data['id_user'] = Auth::id() ?? 0;
        Matiere::create($data);

        return back()->with('success', 'Matiere creee avec succes.');
    }

    public function downloadTemplate()
    {
        return Excel::download(new MatieresTemplateExport(), 'template_import_matieres.xlsx');
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'fichier' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $import = new MatieresImport();
        Excel::import($import, $request->file('fichier'));

        return redirect()->route('matieres.index')
            ->with('success', $import->createdCount() . ' matiere(s) creee(s), ' . $import->updatedCount() . ' matiere(s) mise(s) a jour.')
            ->with('warning', $import->skippedCount() . ' ligne(s) ignoree(s).')
            ->with('import_errors', $import->errors());
    }

    public function update(Request $request, Matiere $matiere)
    {
        $data = $request->validate([
            'nom_matiere' => 'required|string|max:255',
            'code_matiere' => 'nullable|string|max:50|unique:matieres,code_matiere,' . $matiere->id,
            'description' => 'nullable|string',
        ]);

        $matiere->update($data);

        return back()->with('success', 'Matiere modifiee avec succes.');
    }

    public function destroy(Matiere $matiere)
    {
        if ($matiere->lignes_rattrapage()->exists()) {
            return back()->withErrors(['matiere' => 'Impossible de supprimer cette matiere car elle est deja utilisee sur une facture de rattrapage.']);
        }

        $matiere->delete();

        return back()->with('success', 'Matiere supprimee avec succes.');
    }
}
