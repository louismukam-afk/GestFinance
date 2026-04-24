<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\cycle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CycleController extends Controller
{
    /**
     * Afficher la liste des cycles
     */
    public function index()
    {
        $cycles = cycle::orderBy('created_at', 'desc')->get();
        $title = "Gestion des Cycles";

        return view('Admin.Cycle.index', compact('cycles', 'title'));
    }

    /**
     * Enregistrer un nouveau cycle
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom_cycle' => 'required|string|max:255',
            'code_cycle' => 'required|string|max:50|unique:cycles,code_cycle',
            'description' => 'nullable|string',
        ]);

        cycle::create([
            'nom_cycle' => $request->nom_cycle,
            'code_cycle' => $request->code_cycle,
            'description' => $request->description,
            'id_user' => Auth::id() ?? 0,
        ]);
        $this->values['title']='Création des cycles ';
        return redirect()->route('cycle_management')->with('success', 'Cycle ajouté avec succès ✅',$this->values);
    }

    /**
     * Mettre à jour un cycle
     */
    public function update(Request $request)
    {
        $id = $request->id; // récupérer l'ID depuis le formulaire

        $cycle = Cycle::findOrFail($id);

        $request->validate([
            'nom_cycle' => 'required|string|max:255',
            'code_cycle' => 'required|string|max:50|unique:cycles,code_cycle,' . $id,
            'description' => 'nullable|string',
            'id_user' => auth()->id() ?? $cycle->id_user,         ]);

        $cycle->update($request->only(['nom_cycle', 'code_cycle', 'description']));

        return redirect()->route('cycle_management')
            ->with('success', 'Cycle modifié avec succès ✏️');
    }

    public function update1(Request $request, $id)
    {
        $cycle = cycle::findOrFail($id);

        $request->validate([
            'nom_cycle' => 'required|string|max:255',
            'code_cycle' => 'required|string|max:50|unique:cycles,code_cycle,' . $id,
            'description' => 'nullable|string',
        ]);
        $this->values['title']='Modification des cycles ';
        $cycle->update($request->all());

        return redirect()->route('cycle_management')->with('success', 'Cycle modifié avec succès ✏️',$this->values);
    }

    /**
     * Supprimer un cycle
     */
    public function destroy($id)
    {
        $cycle = cycle::findOrFail($id);
        $cycle->delete();

        return redirect()->route('cycle_management')->with('success', 'Cycle supprimé avec succès 🗑️');
    }
}
