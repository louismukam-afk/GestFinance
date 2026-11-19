<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{BiometrieImport, LigneVirementVacataire};
use App\Services\BulletinsVacataires;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class BulletinVacataireController extends Controller
{
    public function index()
    {
        return view('Admin.EmploiTemps.bulletins_vacataires.index', [
            'title' => 'Bulletins de paie vacataires',
            'imports' => BiometrieImport::where(fn($q) => $q->whereNull('type_import')->orWhere('type_import', 'cours'))
                ->where(fn($q) => $q->whereHas('heures_realisees')->orWhereIn('id', LigneVirementVacataire::select('id_biometrie_import')))
                ->latest('id')->paginate(25),
        ]);
    }

    public function show(BiometrieImport $import)
    {
        return view('Admin.EmploiTemps.bulletins_vacataires.show', [
            'title' => 'Bulletins vacataires - import #'.$import->id,
            'import' => $import, 'bulletins' => $this->bulletins($import),
        ]);
    }

    public function pdf(Request $request, BiometrieImport $import)
    {
        $request->validate(['id_personnel' => 'nullable|integer|min:1']);
        $bulletins = $this->bulletins($import);
        if ($request->filled('id_personnel')) {
            $bulletins = $bulletins->where('id_personnel', (int) $request->id_personnel)->values();
            abort_if($bulletins->isEmpty(), 404);
        }

        return Pdf::loadView('Admin.EmploiTemps.bulletins_vacataires.pdf', [
            'import' => $import, 'bulletins' => $bulletins,
        ])->setPaper('a4')->stream('bulletins_vacataires_import_'.$import->id.($request->filled('id_personnel') ? '_personnel_'.$request->id_personnel : '').'.pdf');
    }

    private function bulletins(BiometrieImport $import)
    {
        abort_unless($import->type_import === null || $import->type_import === 'cours', 404);
        $bulletins = (new BulletinsVacataires())->pourImport($import);
        abort_if($bulletins->isEmpty(), 422, 'Aucune heure consolidee ni virement archive pour cet import.');

        return $bulletins;
    }
}
