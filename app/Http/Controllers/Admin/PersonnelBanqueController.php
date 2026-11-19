<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\banque;
use App\Models\personnel;
use Illuminate\Http\Request;

class PersonnelBanqueController extends Controller
{
    public function index()
    {
        return view('Admin.PersonnelBanque.index', [
            'title' => 'Banques du personnel',
            'banques' => banque::withCount('personnels')->orderBy('nom_banque')->get(),
            'sansBanque' => personnel::whereNull('id_banque')->count(),
        ]);
    }

    public function create(banque $banque)
    {
        return view('Admin.PersonnelBanque.affecter', [
            'title' => 'Affecter des personnels - '.$banque->nom_banque,
            'banque' => $banque,
            'personnels' => personnel::with('banque')->orderBy('nom')->get(),
        ]);
    }

    public function store(Request $request, banque $banque)
    {
        $data = $request->validate([
            'personnels' => 'required|array|min:1|max:5000',
            'personnels.*' => 'required|integer|distinct|exists:personnels,id',
        ]);
        $count = personnel::whereIn('id', $data['personnels'])->update(['id_banque' => $banque->id]);

        return redirect()->route('personnels_banques.show', $banque->id)
            ->with('success', $count.' personnel(s) affecte(s) a '.$banque->nom_banque.'.');
    }

    public function show(Request $request, banque $banque)
    {
        $data = $request->validate(['recherche' => 'nullable|string|max:255']);
        $personnels = $banque->personnels()
            ->when($data['recherche'] ?? null, fn($query, $term) => $query->where(fn($q) => $q->where('nom', 'like', '%'.$term.'%')->orWhere('niveau_etude', 'like', '%'.$term.'%')))
            ->orderBy('nom')->get();

        return view($request->boolean('imprimer') ? 'Admin.PersonnelBanque.imprimer' : 'Admin.PersonnelBanque.show', [
            'title' => 'Personnel - '.$banque->nom_banque,
            'banque' => $banque,
            'personnels' => $personnels,
            'banques' => banque::orderBy('nom_banque')->get(),
        ]);
    }

    public function update(Request $request, personnel $personnel)
    {
        $data = $request->validate(['id_banque' => 'required|integer|exists:banques,id']);
        $personnel->update(['id_banque' => $data['id_banque']]);

        return redirect()->route('personnels_banques.show', $data['id_banque'])->with('success', 'Banque du personnel modifiee.');
    }

    public function destroy(banque $banque, personnel $personnel)
    {
        $count = personnel::whereKey($personnel->id)->where('id_banque', $banque->id)->update(['id_banque' => null]);
        abort_if($count === 0, 404);

        return redirect()->route('personnels_banques.show', $banque->id)->with('success', 'Affectation bancaire retiree.');
    }
}
