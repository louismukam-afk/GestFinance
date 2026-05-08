<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Matiere;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
