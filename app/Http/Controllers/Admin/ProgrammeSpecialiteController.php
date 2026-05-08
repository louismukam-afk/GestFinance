<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\annee_academique;
use App\Models\cycle;
use App\Models\entite;
use App\Models\filiere;
use App\Models\Matiere;
use App\Models\niveau;
use App\Models\ProgrammeSpecialite;
use App\Models\scolarite;
use App\Models\specialite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgrammeSpecialiteController extends Controller
{
    public function index()
    {
        $scolarites = scolarite::with(['cycles', 'filiere', 'niveaux', 'specialites', 'annee_academique'])
            ->orderBy('id_cycle')
            ->orderBy('id_filiere')
            ->orderBy('id_specialite')
            ->get()
            ->groupBy(fn($s) => ($s->cycles->nom_cycle ?? 'Cycle non defini') . ' / ' . ($s->filiere->nom_filiere ?? 'Filiere non definie'));

        return view('Admin.ProgrammesSpecialites.index', [
            'title' => 'Programmes de specialite',
            'scolaritesGrouped' => $scolarites,
        ]);
    }

    public function configure(Request $request, specialite $specialite)
    {
        $baseContext = $this->baseContextFromRequest($request);

        if (!$baseContext) {
            return redirect()
                ->route('programmes_specialites.index')
                ->with('error', 'Choisis d\'abord une specialite, un cycle et un niveau dans la liste.');
        }

        return view('Admin.ProgrammesSpecialites.configure', [
            'title' => 'Choisir le contexte du programme',
            'specialite' => $specialite->load('filiere'),
            'baseContext' => $baseContext,
            'baseContextLabels' => $this->baseContextLabels($baseContext),
            'annees' => annee_academique::orderBy('nom', 'desc')->get(),
            'entites' => entite::orderBy('nom_entite')->get(),
        ]);
    }

    public function edit(Request $request, specialite $specialite)
    {
        $context = $this->contextFromRequest($request);

        if (!$context) {
            $baseContext = $this->baseContextFromRequest($request);

            return redirect()->route('programmes_specialites.configure', array_merge([
                'specialite' => $specialite->id,
            ], $baseContext ?? []));
        }

        $specialite->load(['filiere', 'programme_specialites.matiere']);
        $affectedIds = ProgrammeSpecialite::where('id_specialite', $specialite->id)
            ->where($context)
            ->pluck('id_matiere')
            ->all();

        return view('Admin.ProgrammesSpecialites.edit', [
            'title' => 'Programme de specialite',
            'specialite' => $specialite,
            'context' => $context,
            'contextLabels' => $this->contextLabels($context),
            'programmes' => ProgrammeSpecialite::with('matiere')
                ->where('id_specialite', $specialite->id)
                ->where($context)
                ->orderBy('semestre')
                ->orderBy('id')
                ->get(),
            'matieres' => Matiere::whereNotIn('id', $affectedIds)->orderBy('nom_matiere')->get(),
        ]);
    }

    public function store(Request $request, specialite $specialite)
    {
        $request->validate([
            'id_cycle' => 'required|integer|exists:cycles,id',
            'id_filiere' => 'required|integer|exists:filieres,id',
            'id_niveau' => 'required|integer|exists:niveaux,id',
            'id_annee_academique' => 'required|integer|exists:annee_academiques,id',
            'id_entite' => 'required|integer|exists:entites,id',
            'programmes' => 'nullable|array',
            'programmes.*.selected' => 'nullable|boolean',
            'programmes.*.id_matiere' => 'required_with:programmes.*.selected|integer|exists:matieres,id',
            'programmes.*.code_matiere_specialite' => 'nullable|string|max:100',
            'programmes.*.coefficient' => 'nullable|numeric|min:0',
            'programmes.*.coefficient_maximum' => 'nullable|numeric|min:0',
            'programmes.*.type_matiere' => 'nullable|in:transversale,professionnelle,fondamentale',
            'programmes.*.semestre' => 'nullable|string|max:50',
        ]);

        foreach ($request->input('programmes', []) as $line) {
            if (empty($line['selected'])) {
                continue;
            }

            ProgrammeSpecialite::updateOrCreate(
                [
                    'id_specialite' => $specialite->id,
                    'id_cycle' => (int) $request->id_cycle,
                    'id_filiere' => (int) $request->id_filiere,
                    'id_niveau' => (int) $request->id_niveau,
                    'id_annee_academique' => (int) $request->id_annee_academique,
                    'id_entite' => (int) $request->id_entite,
                    'id_matiere' => (int) $line['id_matiere'],
                ],
                [
                    'code_matiere_specialite' => $line['code_matiere_specialite'] ?? null,
                    'coefficient' => (float)($line['coefficient'] ?? 0),
                    'coefficient_maximum' => (float)($line['coefficient_maximum'] ?? 0),
                    'type_matiere' => $line['type_matiere'] ?? 'professionnelle',
                    'semestre' => $line['semestre'] ?? null,
                    'id_user' => Auth::id() ?? 0,
                ]
            );
        }

        return back()->with('success', 'Programme de specialite mis a jour.');
    }

    public function update(Request $request, ProgrammeSpecialite $programme)
    {
        $data = $request->validate([
            'code_matiere_specialite' => 'nullable|string|max:100',
            'coefficient' => 'nullable|numeric|min:0',
            'coefficient_maximum' => 'nullable|numeric|min:0',
            'type_matiere' => 'required|in:transversale,professionnelle,fondamentale',
            'semestre' => 'nullable|string|max:50',
        ]);

        $programme->update($data);

        return back()->with('success', 'Matiere du programme modifiee.');
    }

    public function destroy(ProgrammeSpecialite $programme)
    {
        $programme->delete();

        return back()->with('success', 'Matiere retiree du programme.');
    }

    private function contextFromRequest(Request $request): ?array
    {
        $keys = ['id_cycle', 'id_filiere', 'id_niveau', 'id_annee_academique', 'id_entite'];

        foreach ($keys as $key) {
            if (!$request->filled($key)) {
                return null;
            }
        }

        return collect($keys)->mapWithKeys(fn($key) => [$key => (int) $request->$key])->all();
    }

    private function baseContextFromRequest(Request $request): ?array
    {
        $keys = ['id_cycle', 'id_filiere', 'id_niveau'];

        foreach ($keys as $key) {
            if (!$request->filled($key)) {
                return null;
            }
        }

        return collect($keys)->mapWithKeys(fn($key) => [$key => (int) $request->$key])->all();
    }

    private function baseContextLabels(array $context): array
    {
        return [
            'cycle' => optional(cycle::find($context['id_cycle']))->nom_cycle,
            'filiere' => optional(filiere::find($context['id_filiere']))->nom_filiere,
            'niveau' => optional(niveau::find($context['id_niveau']))->nom_niveau,
        ];
    }

    private function contextLabels(array $context): array
    {
        return [
            'cycle' => optional(cycle::find($context['id_cycle']))->nom_cycle,
            'filiere' => optional(filiere::find($context['id_filiere']))->nom_filiere,
            'niveau' => optional(niveau::find($context['id_niveau']))->nom_niveau,
            'annee' => optional(annee_academique::find($context['id_annee_academique']))->nom,
            'entite' => optional(entite::find($context['id_entite']))->nom_entite,
        ];
    }
}
