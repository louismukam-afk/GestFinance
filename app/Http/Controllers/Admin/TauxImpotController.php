<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TauxImpot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TauxImpotController extends Controller
{
    public function index()
    {
        return view('Admin.EmploiTemps.impots.taux_index', [
            'title' => 'Taux d impots',
            'taux' => TauxImpot::orderByDesc('par_defaut')->orderBy('nom')->get(),
        ]);
    }

    public function create()
    {
        return view('Admin.EmploiTemps.impots.taux_form', ['title' => 'Nouveau taux d impot', 'taux' => new TauxImpot()]);
    }

    public function edit(TauxImpot $taux_impot)
    {
        return view('Admin.EmploiTemps.impots.taux_form', ['title' => 'Modifier le taux d impot', 'taux' => $taux_impot]);
    }

    public function store(Request $request)
    {
        $this->enregistrer($request, new TauxImpot());

        return redirect()->route('taux_impots.index')->with('success', 'Taux d impot cree.');
    }

    public function update(Request $request, TauxImpot $taux_impot)
    {
        $this->enregistrer($request, $taux_impot);

        return redirect()->route('taux_impots.index')->with('success', 'Taux d impot modifie.');
    }

    private function enregistrer(Request $request, TauxImpot $taux): void
    {
        if (is_string($request->input('taux'))) {
            $request->merge(['taux' => str_replace(',', '.', $request->input('taux'))]);
        }
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:255', Rule::unique('taux_impots', 'nom')->ignore($taux->id)],
            'taux' => 'required|numeric|decimal:0,2|min:0|max:100',
            'par_defaut' => 'nullable|boolean',
        ]);
        DB::transaction(function () use ($request, $data, $taux) {
            $existants = TauxImpot::orderBy('id')->lockForUpdate()->get();
            $data['par_defaut'] = $request->boolean('par_defaut') || $existants->firstWhere('id', $taux->id)?->par_defaut || $existants->isEmpty();
            if ($data['par_defaut']) {
                TauxImpot::where('id', '<>', $taux->id ?? 0)->update(['par_defaut' => false]);
            }
            $taux->fill($data)->save();
        });
    }
}
