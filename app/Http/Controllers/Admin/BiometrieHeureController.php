<?php

namespace App\Http\Controllers\Admin;

use App\Exports\HeuresRealiseesExport;
use App\Http\Controllers\Controller;
use App\Models\BiometrieImport;
use App\Models\BiometriePointage;
use App\Models\CoursEnseignant;
use App\Models\DisciplinePersonnel;
use App\Models\EmploiPermanent;
use App\Models\HeureRealiseeEnseignant;
use App\Models\PlageHoraire;
use App\Models\PresencePermanent;
use App\Models\SalairePermanent;
use App\Models\SeanceCours;
use App\Models\TauxHoraire;
use App\Models\TauxImpot;
use App\Models\personnel;
use App\Services\BiometriePersonnelMatcher;
use App\Services\DecompteHeuresEnseignant;
use App\Services\RapportImportBiometrie;
use App\Services\ConsolidationImpots;
use App\Services\PaieImportVacataire;
use App\Models\LigneVirementVacataire;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class BiometrieHeureController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['id_biometrie_import' => 'nullable|integer|min:1']);
        $imports = BiometrieImport::pourEnseignants()->latest()->get();

        $importSelectionne = $request->filled('id_biometrie_import') ? $imports->firstWhere('id', (int) $request->id_biometrie_import) : null;
        abort_if($request->filled('id_biometrie_import') && !$importSelectionne, 404, 'Import biometrie introuvable.');
        $heures = $importSelectionne ? $this->heuresRealiseesCollection($request) : collect();
        $totaux = $this->totauxParEnseignant($heures);

        $pointagesCours = BiometriePointage::where('id_biometrie_import', $importSelectionne?->id ?? 0)->whereHas('import', function ($query) {
            $query->whereNull('type_import')->orWhereIn('type_import', ['cours', 'manuel']);
        });

        $unmatchedPointages = (clone $pointagesCours)->whereNull('id_personnel')
            ->select('nom_biometrie', 'numero_biometrie')
            ->whereNotNull('nom_biometrie')
            ->groupBy('nom_biometrie', 'numero_biometrie')
            ->orderBy('nom_biometrie')
            ->get();

        $biometrieIdentites = (clone $pointagesCours)
            ->select('nom_biometrie', 'numero_biometrie', DB::raw('COUNT(*) as total'))
            ->whereNotNull('nom_biometrie')
            ->groupBy('nom_biometrie', 'numero_biometrie')
            ->orderBy('nom_biometrie')
            ->get();

        return view('Admin.EmploiTemps.biometrie.index', [
            'title' => 'Decompte des heures realisees',
            'imports' => $imports,
            'importSelectionne' => $importSelectionne,
            'orphelins' => [
                'pointages' => BiometriePointage::whereDoesntHave('import')->count(),
                'heures' => HeureRealiseeEnseignant::whereDoesntHave('import')->count(),
            ],
            'heures' => $heures,
            'totaux' => $totaux,
            'personnels' => $importSelectionne ? personnel::whereIn('type_personnel', ['vacataire', 'permanent'])
                ->whereHas('cours_enseignants', fn($query) => $query->where('statut', 'actif')->whereHas('seances.plage', fn($plage) => $plage->where('type_plage', 'cours')))
                ->orderBy('nom')->get() : collect(),
            'unmatchedPointages' => $unmatchedPointages,
            'biometrieIdentites' => $biometrieIdentites,
        ]);
    }

    public function historique(Request $request)
    {
        $request->validate([
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date'.($request->filled('date_debut') ? '|after_or_equal:date_debut' : ''),
        ]);
        $imports = BiometrieImport::pourEnseignants()
            ->when($request->date_debut, fn($query) => $query->whereDate('date_fin', '>=', $request->date_debut))
            ->when($request->date_fin, fn($query) => $query->whereDate('date_debut', '<=', $request->date_fin))
            ->withCount('heures_realisees')
            ->latest('id')->paginate(25)->withQueryString();

        return view('Admin.EmploiTemps.biometrie.historique', [
            'title' => 'Historique des consolidations des heures vacataires',
            'imports' => $imports,
        ]);
    }

    public function rapportImport(Request $request, BiometrieImport $import)
    {
        abort_unless($import->concerneEnseignants(), 404);
        $data = $request->validate(['rapport' => 'required|in:discipline,financier']);
        $heures = $this->heuresRealiseesCollection(new Request(['id_biometrie_import' => $import->id]));
        abort_if($heures->isEmpty(), 422, 'Cet import ne contient aucune heure consolidee.');
        $heures->loadMissing('cours.programme.specialite');
        $service = new RapportImportBiometrie();
        $disciplines = DisciplinePersonnel::where('id_biometrie_import', $import->id)->get();

        return Pdf::loadView('Admin.EmploiTemps.biometrie.rapport_import', [
            'title' => $data['rapport'] === 'discipline' ? 'Rapport de discipline des enseignants' : 'Rapport financier de consolidation',
            'rapport' => $data['rapport'],
            'import' => $import,
            'groupes' => $data['rapport'] === 'discipline' ? $service->discipline($heures, $disciplines) : collect(),
            'finances' => $data['rapport'] === 'financier' ? $service->finances($heures) : collect(),
        ])->setPaper('a4', 'landscape')->stream('rapport_'.$data['rapport'].'_import_'.$import->id.'.pdf');
    }

    public function impotsImport(BiometrieImport $import)
    {
        $heures = $this->heuresImportFiscal($import);
        $defaut = TauxImpot::where('par_defaut', true)->firstOrFail();

        return view('Admin.EmploiTemps.impots.consolidation', [
            'title' => 'Consolidation des impots - import #'.$import->id,
            'import' => $import,
            'defaut' => $defaut,
            'taux' => TauxImpot::orderBy('nom')->get(),
            'lignes' => $this->lignesFiscales($import, $heures, $defaut),
        ]);
    }

    public function enregistrerImpotsImport(Request $request, BiometrieImport $import)
    {
        abort_if($import->estRequete(), 422, 'Une requete conserve le taux archive de l ordre initial.');
        $heures = $this->heuresImportFiscal($import);
        $data = $request->validate([
            'taux_personnels' => 'required|array|max:5000',
            'taux_personnels.*' => 'nullable|integer|exists:taux_impots,id',
        ]);
        $allowed = $heures->pluck('id_personnel')->unique()->flip();
        foreach (array_keys($data['taux_personnels']) as $id) {
            if (!$allowed->has($id)) {
                throw \Illuminate\Validation\ValidationException::withMessages(['taux_personnels' => 'Un personnel selectionne ne figure pas dans cet import.']);
            }
        }
        DB::transaction(function () use ($data) {
            foreach ($data['taux_personnels'] as $id => $tauxId) {
                personnel::whereKey($id)->update(['id_taux_impot' => $tauxId ?: null]);
            }
        });

        return redirect()->route('biometrie_heures.impots', $import->id)->with('success', 'Taux d impots des personnels enregistres.');
    }

    public function pdfImpotsImport(BiometrieImport $import)
    {
        $heures = $this->heuresImportFiscal($import);
        $defaut = TauxImpot::where('par_defaut', true)->firstOrFail();

        return Pdf::loadView('Admin.EmploiTemps.impots.pdf', [
            'title' => 'Consolidation des impots',
            'import' => $import,
            'lignes' => $this->lignesFiscales($import, $heures, $defaut),
        ])->setPaper('a4', 'landscape')->stream('impots_import_'.$import->id.'.pdf');
    }

    private function heuresImportFiscal(BiometrieImport $import)
    {
        abort_unless($import->concerneEnseignants(), 404);
        $heures = $this->heuresRealiseesCollection(new Request(['id_biometrie_import' => $import->id]));
        abort_if($heures->isEmpty(), 422, 'Cet import ne contient aucune heure consolidee.');
        $heures->loadMissing('personnel.taux_impot');

        return $heures;
    }

    private function lignesFiscales(BiometrieImport $import, $heures, TauxImpot $defaut)
    {
        if (!$import->estRequete()) { return (new ConsolidationImpots())->lignes($heures, $defaut); }
        return (new PaieImportVacataire())->lignes($import, $heures)->groupBy('id_personnel')->map(function ($items) use ($import) {
            $premier = $items->first();
            return ['id_personnel' => $premier['id_personnel'], 'nom' => $premier['nom'], 'id_taux_impot' => null,
                'taux_nom' => 'Taux archive', 'taux' => $premier['details'][0]['taux'], 'mois' => $import->mois_requete->format('Y-m'),
                'brut' => round($items->sum('brut_fiscal'), 2), 'impot' => round($items->sum('impot'), 2),
                'net' => round($items->sum('net_avant_frais'), 2)];
        })->values();
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new HeuresRealiseesExport($this->heuresRealiseesCollection($request)), 'heures_realisees_enseignants.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $request->validate(['tableau' => 'nullable|in:complet,detail,totaux']);
        $tableau = $request->input('tableau') ?: 'complet';
        $heures = $this->heuresRealiseesCollection($request);
        $pdf = Pdf::loadView('Admin.EmploiTemps.biometrie.pdf', [
            'title' => $tableau === 'totaux' ? 'Recapitulatif global des heures et paiements' : 'Decompte des heures realisees',
            'tableau' => $tableau,
            'heures' => $heures,
            'totaux' => $this->totauxParEnseignant($heures),
        ])->setPaper('a4', 'landscape');

        return $tableau === 'complet'
            ? $pdf->download('heures_realisees_enseignants.pdf')
            : $pdf->stream('heures_enseignants_'.$tableau.'.pdf');
    }

    public function permanentIndex(Request $request)
    {
        $imports = BiometrieImport::whereIn('type_import', ['permanent', 'permanent_manuel'])->latest()->get();
        $presences = $this->presencesPermanentsCollection($request);
        $permanentImportIds = $imports->pluck('id');
        $unmatchedPointages = BiometriePointage::whereIn('id_biometrie_import', $permanentImportIds)
            ->whereNull('id_personnel')
            ->select('nom_biometrie', 'numero_biometrie')
            ->whereNotNull('nom_biometrie')
            ->groupBy('nom_biometrie', 'numero_biometrie')
            ->orderBy('nom_biometrie')
            ->get();
        $biometrieIdentites = BiometriePointage::query()
            ->whereIn('id_biometrie_import', $permanentImportIds)
            ->select('nom_biometrie', 'numero_biometrie', DB::raw('COUNT(*) as total'))
            ->whereNotNull('nom_biometrie')
            ->groupBy('nom_biometrie', 'numero_biometrie')
            ->orderBy('nom_biometrie')
            ->get();

        return view('Admin.EmploiTemps.biometrie.permanents', [
            'title' => 'Biometrie permanents',
            'imports' => $imports,
            'presences' => $presences,
            'totaux' => $this->totauxPermanents($presences),
            'personnels' => personnel::where('type_personnel', 'permanent')->orderBy('nom')->get(),
            'unmatchedPointages' => $unmatchedPointages,
            'biometrieIdentites' => $biometrieIdentites,
        ]);
    }

    public function permanentExportPdf(Request $request)
    {
        $request->validate([
            'id_biometrie_import' => 'nullable|integer|exists:biometrie_imports,id',
            'id_personnel' => 'nullable|integer|exists:personnels,id',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
        ]);
        $presences = $this->presencesPermanentsCollection($request);
        if ($presences->isEmpty()) {
            return back()->withErrors(['presences' => 'Aucune presence consolidee ne correspond aux criteres.']);
        }

        $pdf = Pdf::loadView('Admin.EmploiTemps.biometrie.permanents_pdf', [
            'title' => 'Pointages biometriques consolides',
            'presences' => $presences,
            'totaux' => $this->totauxPermanents($presences),
        ])->setPaper('a4', 'landscape');

        $suffixe = $request->id_personnel ? 'personnel_' . $request->id_personnel : 'tous';
        return $pdf->download('pointages_permanents_' . $suffixe . '.pdf');
    }

    public function permanentStore(Request $request)
    {
        $data = $request->validate([
            'libelle' => 'nullable|string|max:255',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'fichier' => 'required|file|max:20480',
        ]);

        $file = $request->file('fichier');
        $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9_.-]/', '_', $file->getClientOriginalName());
        $path = 'uploads/biometrie/' . $fileName;
        File::ensureDirectoryExists(public_path('uploads/biometrie'));
        $file->move(public_path('uploads/biometrie'), $fileName);

        $import = BiometrieImport::create([
            'libelle' => $data['libelle'] ?: 'Import biometrie permanent du ' . now()->format('d/m/Y H:i'),
            'date_debut' => $data['date_debut'],
            'date_fin' => $data['date_fin'],
            'fichier' => $path,
            'statut' => 'importe',
            'type_import' => 'permanent',
            'id_user' => auth()->id() ?? 0,
        ]);

        [$created, $unmatched] = $this->importBiometriePointages($import, public_path($path));

        $import->update([
            'total_lignes' => $created,
            'total_non_associees' => $unmatched,
        ]);

        return redirect()->route('biometrie_permanents.index')
            ->with('success', "{$created} pointage(s) permanent(s) importe(s). {$unmatched} non associe(s) a un personnel.");
    }

    public function permanentConsolider(BiometrieImport $import)
    {
        if ($import->type_import !== 'permanent') {
            return back()->withErrors(['import' => 'Cet import ne correspond pas a la biometrie des permanents.']);
        }

        $this->reassocierPointages($import);
        PresencePermanent::where('id_biometrie_import', $import->id)->delete();

        $start = Carbon::parse($import->date_debut)->startOfDay();
        $end = Carbon::parse($import->date_fin)->endOfDay();
        $emplois = EmploiPermanent::with(['personnel', 'plage'])
            ->where('statut', 'actif')
            ->whereDate('date_debut', '<=', $end->toDateString())
            ->where(function ($query) use ($start) {
                $query->whereNull('date_fin')
                    ->orWhereDate('date_fin', '>=', $start->toDateString());
            })
            ->get();

        $count = 0;
        DB::transaction(function () use ($emplois, $import, $start, $end, &$count) {
            $processedDatesByPersonnel = [];
            foreach ($emplois as $emploi) {
                if (!$emploi->plage || !$emploi->personnel) {
                    continue;
                }

                $periodeStart = max(Carbon::parse($emploi->date_debut)->startOfDay(), $start);
                $periodeEnd = $emploi->date_fin
                    ? min(Carbon::parse($emploi->date_fin)->endOfDay(), $end)
                    : $end->copy();

                $date = $periodeStart->copy();
                while ($date->lte($periodeEnd)) {
                    if ($date->dayOfWeekIso === (int) $emploi->jour_semaine) {
                        $this->consoliderPresencePermanent($import, $emploi, $date);
                        $processedDatesByPersonnel[$emploi->id_personnel][$date->toDateString()] = true;
                        $count++;
                    }
                    $date->addDay();
                }
            }

            $count += $this->consoliderJoursOuvrablesSansEmploi($import, $start, $end, $processedDatesByPersonnel);
            $count += $this->consoliderJoursPayesNonTravailles($import, $start, $end, $processedDatesByPersonnel);
        });

        $import->update([
            'statut' => 'permanent_consolide',
            'total_consolidees' => $count,
            'total_non_associees' => $import->pointages()->whereNull('id_personnel')->count(),
        ]);

        return back()->with('success', "{$count} presence(s) permanente(s) consolidee(s).");
    }

    public function permanentClear(BiometrieImport $import)
    {
        $deleted = PresencePermanent::where('id_biometrie_import', $import->id)->delete();
        $import->update([
            'statut' => $import->type_import === 'permanent_manuel' ? 'manuel' : 'importe',
            'total_consolidees' => 0,
            'total_lignes' => $import->type_import === 'permanent_manuel' ? 0 : $import->total_lignes,
        ]);

        return back()->with('success', "{$deleted} consolidation(s) permanente(s) supprimee(s).");
    }

    public function permanentManualCreate()
    {
        $selectedImportId = request('id_biometrie_import');
        $imports = BiometrieImport::where('type_import', 'permanent_manuel')->latest()->get();
        $emplois = EmploiPermanent::with(['personnel', 'plage', 'annee_academique', 'entite'])
            ->where('statut', 'actif')
            ->whereHas('personnel', fn($query) => $query->where('type_personnel', 'permanent'))
            ->orderBy('id_personnel')
            ->orderBy('jour_semaine')
            ->get();

        $jours = [
            1 => 'Lundi',
            2 => 'Mardi',
            3 => 'Mercredi',
            4 => 'Jeudi',
            5 => 'Vendredi',
            6 => 'Samedi',
            7 => 'Dimanche',
        ];
        $emploisData = $emplois->mapWithKeys(function ($emploi) use ($jours) {
            return [$emploi->id => [
                'jour' => (int) $emploi->jour_semaine,
                'jour_label' => $jours[$emploi->jour_semaine] ?? '-',
                'debut' => substr((string) ($emploi->plage->heure_debut ?? ''), 0, 5),
                'fin' => substr((string) ($emploi->plage->heure_fin ?? ''), 0, 5),
                'date_debut' => optional($emploi->date_debut)->format('Y-m-d'),
                'date_fin' => optional($emploi->date_fin)->format('Y-m-d'),
            ]];
        });

        return view('Admin.EmploiTemps.biometrie.permanents_manual', [
            'title' => 'Saisie manuelle des presences permanentes',
            'imports' => $imports,
            'selectedImport' => $selectedImportId ? $imports->firstWhere('id', (int) $selectedImportId) : null,
            'emplois' => $emplois,
            'emploisData' => $emploisData,
            'jours' => $jours,
            'presencesManuelles' => PresencePermanent::with(['personnel', 'plage', 'import'])
                ->whereHas('import', fn($query) => $query->where('type_import', 'permanent_manuel'))
                ->when($selectedImportId, fn($query) => $query->where('id_biometrie_import', $selectedImportId))
                ->orderByDesc('date_presence')
                ->latest('id')
                ->get(),
        ]);
    }

    public function permanentManualStore(Request $request)
    {
        $data = $request->validate([
            'id_biometrie_import' => 'nullable|integer|exists:biometrie_imports,id',
            'libelle' => 'nullable|string|max:255',
            'date_debut_import' => 'required_without:id_biometrie_import|nullable|date',
            'date_fin_import' => 'required_without:id_biometrie_import|nullable|date|after_or_equal:date_debut_import',
            'id_emploi_permanent' => 'required|integer|exists:emploi_permanents,id',
            'date_presence' => 'required|date',
            'heure_debut_reelle' => 'required|date_format:H:i',
            'heure_fin_reelle' => 'required|date_format:H:i',
            'observation' => 'nullable|string|max:1000',
        ]);

        $emploi = EmploiPermanent::with(['personnel', 'plage'])->findOrFail($data['id_emploi_permanent']);
        if (!$emploi->personnel || $emploi->personnel->type_personnel !== 'permanent' || !$emploi->plage) {
            return back()->withErrors(['id_emploi_permanent' => 'Cet emploi permanent est incomplet ou invalide.'])->withInput();
        }

        if (!empty($data['id_biometrie_import'])) {
            $import = BiometrieImport::where('type_import', 'permanent_manuel')->findOrFail($data['id_biometrie_import']);
        } else {
            $import = null;
        }

        $date = Carbon::parse($data['date_presence'])->startOfDay();
        $importStart = Carbon::parse($import?->date_debut ?? $data['date_debut_import'])->startOfDay();
        $importEnd = Carbon::parse($import?->date_fin ?? $data['date_fin_import'])->endOfDay();
        if ($date->lt($importStart) || $date->gt($importEnd)) {
            return back()->withErrors(['date_presence' => 'La date doit etre comprise dans la periode de l\'import manuel.'])->withInput();
        }
        if ($date->lt(Carbon::parse($emploi->date_debut)->startOfDay())
            || ($emploi->date_fin && $date->gt(Carbon::parse($emploi->date_fin)->endOfDay()))) {
            return back()->withErrors(['date_presence' => 'La date doit etre comprise dans la periode de cet emploi permanent.'])->withInput();
        }
        if ($date->dayOfWeekIso !== (int) $emploi->jour_semaine) {
            return back()->withErrors(['date_presence' => 'La date choisie ne correspond pas au jour programme pour cet emploi.'])->withInput();
        }

        if (!$import) {
            $import = BiometrieImport::create([
                'libelle' => $data['libelle'] ?: 'Saisie manuelle permanent du ' . now()->format('d/m/Y H:i'),
                'date_debut' => $data['date_debut_import'],
                'date_fin' => $data['date_fin_import'],
                'statut' => 'manuel',
                'type_import' => 'permanent_manuel',
                'observations' => 'Import cree depuis la saisie manuelle des presences permanentes.',
                'id_user' => auth()->id() ?? 0,
            ]);
        }

        $plannedStart = Carbon::parse($date->format('Y-m-d') . ' ' . $emploi->plage->heure_debut);
        $plannedEnd = Carbon::parse($date->format('Y-m-d') . ' ' . $emploi->plage->heure_fin);
        if ($plannedEnd->lessThanOrEqualTo($plannedStart)) {
            $plannedEnd->addDay();
        }
        $realStart = Carbon::parse($date->format('Y-m-d') . ' ' . $data['heure_debut_reelle']);
        $realEnd = Carbon::parse($date->format('Y-m-d') . ' ' . $data['heure_fin_reelle']);
        if ($realEnd->lessThanOrEqualTo($realStart)) {
            $realEnd->addDay();
        }

        $plannedDuration = $this->plannedDuration($plannedStart, $plannedEnd, $emploi->plage);
        [$duration, $calculationObservation] = $this->permanentPresenceDuration(
            $emploi,
            $realStart,
            $realEnd,
            $plannedStart,
            $plannedEnd,
            $plannedDuration
        );
        $salaire = $this->salairePermanentForDate(
            $emploi->id_personnel,
            $date,
            $emploi->id_annee_academique,
            $emploi->id_entite
        );
        $salaireJournalier = SalairePermanentController::salaireJournalier($salaire, $date);
        $heuresJour = $this->heuresReferenceSalairePermanent($plannedDuration);
        $tauxHoraire = $heuresJour > 0 ? round($salaireJournalier / $heuresJour, 6) : 0;
        $montantDu = $tauxHoraire > 0 ? round($tauxHoraire * min($duration, $heuresJour), 2) : 0;
        $penalite = max(round($salaireJournalier - $montantDu, 2), 0);
        $observation = trim(implode(' ', array_filter([
            $data['observation'] ?? null,
            $calculationObservation,
            'Saisie manuelle.',
        ])));

        PresencePermanent::updateOrCreate(
            [
                'id_biometrie_import' => $import->id,
                'id_emploi_permanent' => $emploi->id,
                'date_presence' => $date->toDateString(),
            ],
            [
                'id_personnel' => $emploi->id_personnel,
                'id_plage_horaire' => $emploi->id_plage_horaire,
                'id_annee_academique' => $emploi->id_annee_academique,
                'id_entite' => $emploi->id_entite,
                'jour_semaine' => $emploi->jour_semaine,
                'heure_debut_prevue' => $plannedStart->format('H:i:s'),
                'heure_fin_prevue' => $plannedEnd->format('H:i:s'),
                'heure_debut_reelle' => $realStart->format('H:i:s'),
                'heure_fin_reelle' => $realEnd->format('H:i:s'),
                'duree_prevue' => $plannedDuration,
                'duree_realisee' => $duration,
                'salaire_journalier' => $salaireJournalier,
                'montant_du' => $montantDu,
                'penalite_montant' => $penalite,
                'statut' => $duration >= $plannedDuration ? 'present' : ($duration > 0 ? 'partiel' : 'absent'),
                'observation' => $observation,
                'id_user' => auth()->id() ?? 0,
            ]
        );

        $this->syncDisciplineForPresencePermanent(
            $import,
            $emploi,
            $date,
            $plannedStart,
            $plannedDuration,
            $realStart,
            $duration,
            $observation,
            true
        );

        $total = $import->presences_permanents()->count();
        $import->update(['total_lignes' => $total, 'total_consolidees' => $total]);

        return redirect()
            ->route('biometrie_permanents.manual.create', ['id_biometrie_import' => $import->id])
            ->with('success', 'Presence permanente enregistree manuellement.');
    }

    public function manualCreate(Request $request)
    {
        $request->validate(['id_biometrie_import' => 'nullable|integer|min:1', 'modifier_correction' => 'nullable|integer|min:1']);
        $selectedImportId = session()->getOldInput('id_biometrie_import', $request->input('id_biometrie_import'));
        $selectedImport = $selectedImportId ? BiometrieImport::pourEnseignants()->findOrFail($selectedImportId) : null;
        $correction = null;
        if ($request->filled('modifier_correction')) {
            abort_unless($selectedImport?->estRequete(), 404);
            $correction = $selectedImport->heures_realisees()->whereNotNull('type_requete')->findOrFail($request->modifier_correction);
        }
        $cours = CoursEnseignant::with([
            'personnel',
            'programme.matiere',
            'programme.specialite',
            'programme.cycle',
            'programme.filiere',
            'programme.niveau',
            'programme.annee_academique',
            'programme.entite',
            'taux_horaire',
            'seances.plage',
        ])
            ->when(!$selectedImport, fn($q) => $q->where('statut', 'actif'))
            ->whereHas('personnel', fn($q) => $q->whereIn('type_personnel', ['vacataire', 'permanent']))
            ->when($selectedImport, fn($q) => $q->whereDate('date_debut', '<=', $selectedImport->date_fin)->whereDate('date_fin', '>=', $selectedImport->date_debut))
            ->orderBy('id_personnel')
            ->orderByDesc('date_debut')
            ->get();
        $jours = [
            1 => 'Lundi',
            2 => 'Mardi',
            3 => 'Mercredi',
            4 => 'Jeudi',
            5 => 'Vendredi',
            6 => 'Samedi',
            7 => 'Dimanche',
        ];
        $coursData = $cours->mapWithKeys(function ($item) use ($jours) {
            return [$item->id => [
                'seances' => $item->seances->map(function ($seance) use ($jours) {
                    $debut = substr((string) ($seance->plage->heure_debut ?? ''), 0, 5);
                    $fin = substr((string) ($seance->plage->heure_fin ?? ''), 0, 5);

                    return [
                        'id' => $seance->id,
                        'jour' => $seance->jour_semaine,
                        'jour_label' => $jours[$seance->jour_semaine] ?? '-',
                        'libelle' => ($seance->plage->libelle ?? 'Plage') . ' (' . $debut . ' - ' . $fin . ')',
                        'debut' => $debut,
                        'fin' => $fin,
                    ];
                })->values(),
            ]];
        });

        return view('Admin.EmploiTemps.biometrie.manual', [
            'title' => 'Saisie manuelle des heures realisees',
            'imports' => BiometrieImport::pourEnseignants()->latest()->get(),
            'selectedImport' => $selectedImport,
            'correction' => $correction,
            'requetesAvecOrdre' => \App\Models\VirementVacataire::pluck('id_biometrie_import'),
            'parentsRequetes' => BiometrieImport::pourEnseignants()->where(fn($q) => $q->whereNull('type_import')->orWhere('type_import', '!=', 'requete'))
                ->latest()->get(),
            'heuresManuelles' => HeureRealiseeEnseignant::with(['personnel', 'cours.programme.matiere', 'seance.plage', 'import'])
                ->where('id_biometrie_import', $selectedImportId ?: 0)
                ->latest()
                ->get(),
            'cours' => $cours,
            'coursData' => $coursData,
        ]);
    }

    public function referenceRequete(Request $request, BiometrieImport $import)
    {
        abort_unless($import->estRequete(), 404);
        $data = $request->validate([
            'id_cours_enseignant' => 'required|integer|exists:cours_enseignants,id',
            'id_seance_cours' => 'required|integer|exists:seance_cours,id',
            'date_seance' => 'required|date_format:Y-m-d',
            'presence_import' => 'nullable|in:present,absent',
        ]);
        $cours = CoursEnseignant::findOrFail($data['id_cours_enseignant']);
        // La verification porte toujours sur l'import initial, meme si une requete anterieure existe.
        $initiales = HeureRealiseeEnseignant::with(['personnel', 'cours.programme.matiere'])->where('id_biometrie_import', $import->id_import_parent)
            ->where('id_personnel', $cours->id_personnel)->whereBetween('date_seance', [$import->date_debut, $import->date_fin])->get();
        $initiale = $initiales->first(fn($h) => $h->id_seance_cours == $data['id_seance_cours']
            && $h->id_cours_enseignant == $cours->id && $h->date_seance->format('Y-m-d') === $data['date_seance']);
        $totalInitial = $initiales->isNotEmpty() ? (new \App\Services\DecompteHeuresEnseignant())->totaux($initiales)->first() : null;
        $ordresInitiaux = LigneVirementVacataire::where('id_biometrie_import', $import->id_import_parent)
            ->where('id_personnel', $cours->id_personnel)->get();
        $detailsMois = $ordresInitiaux->flatMap(fn($ligne) => $ligne->details ?? [])
            ->where('mois', $import->mois_requete->format('Y-m'));
        $historique = [
            'seance_trouvee' => (bool) $initiale,
            'sanction_seance' => $initiale ? round($initiale->sanction_annulee ? 0 : max($initiale->duree_prevue - $initiale->duree_realisee, 0) * $initiale->montant_taux, 2) : null,
            'sanction_mois_calculee' => $totalInitial['montant_sanction'] ?? 0,
            'sanction_mois_plafonnee' => $totalInitial ? min($totalInitial['montant_sanction'], $totalInitial['montant_brut']) : 0,
            'ordre_initial' => $detailsMois->isNotEmpty(),
            'sanction_archivee' => $detailsMois->isNotEmpty() && $detailsMois->every(fn($d) => array_key_exists('sanction_retenue', $d))
                ? round($detailsMois->sum('sanction_retenue'), 2) : null,
            'autres_sanctions' => $initiales->filter(fn($h) => (!$initiale || $h->id !== $initiale->id) && !$h->sanction_annulee && $h->duree_prevue > $h->duree_realisee)
                ->map(fn($h) => ['date' => $h->date_seance->format('d/m/Y'), 'matiere' => $h->cours?->programme?->matiere?->nom_matiere ?? 'Matière non renseignée', 'debut' => substr($h->heure_debut_prevue, 0, 5),
                    'fin' => substr($h->heure_fin_prevue, 0, 5), 'montant' => round(max($h->duree_prevue - $h->duree_realisee, 0) * $h->montant_taux, 2)])->values(),
        ];
        $heure = (new PaieImportVacataire())->heuresReference($import, $cours->id_personnel)
            ->first(fn($h) => $h->id_seance_cours == $data['id_seance_cours']
                && $h->id_cours_enseignant == $cours->id && $h->date_seance->format('Y-m-d') === $data['date_seance']);
        if (!$heure && ($data['presence_import'] ?? null) === 'absent') {
            return response()->json(['sans_consolidation' => true, 'message' => 'Aucune séance consolidée : les horaires attestés seront saisis manuellement, sans sanction initiale supposée.', 'historique_initial' => $historique]);
        }
        abort_unless($heure, 422, 'Aucune seance consolidee pour cette selection.');
        $pointages = BiometriePointage::where('id_biometrie_import', $import->id_import_parent)
            ->where('id_personnel', $cours->id_personnel)->whereDate('date_heure_pointage', $data['date_seance'])
            ->orderBy('date_heure_pointage')->get()->map(fn($p) => $p->date_heure_pointage->format('H:i'));
        return response()->json([
            'duree_prevue' => $heure->duree_prevue, 'duree_realisee' => $heure->duree_realisee,
            'sanction' => $heure->sanction_annulee ? 0 : round(max($heure->duree_prevue - $heure->duree_realisee, 0) * $heure->montant_taux, 2),
            'montant_realise' => $heure->montant_total, 'taux' => $heure->montant_taux,
            'observation' => $heure->observation, 'pointages' => $pointages,
            'historique_initial' => $historique,
        ]);
    }

    public function manualStore(Request $request)
    {
        $data = $request->validate([
            'id_biometrie_import' => 'required|integer|exists:biometrie_imports,id',
            'id_heure_correction' => 'nullable|integer|exists:heures_realisees_enseignants,id',
            'presence_import' => 'nullable|in:present,absent',
            'type_requete' => 'required|in:absence_justifiee,presence_manuelle,pointage_incomplet',
            'id_cours_enseignant' => 'required|integer|exists:cours_enseignants,id',
            'id_seance_cours' => 'required|integer|exists:seance_cours,id',
            'date_seance' => 'required|date',
            'heure_debut_reelle' => 'exclude_if:type_requete,absence_justifiee|required_if:type_requete,presence_manuelle,pointage_incomplet|nullable|date_format:H:i',
            'heure_fin_reelle' => 'exclude_if:type_requete,absence_justifiee|required_if:type_requete,presence_manuelle,pointage_incomplet|nullable|date_format:H:i|after:heure_debut_reelle',
            'observation' => 'required|string|max:1000',
        ]);

        return DB::transaction(function () use ($data) {
        $cours = CoursEnseignant::with(['programme', 'taux_horaire', 'personnel'])->findOrFail($data['id_cours_enseignant']);
        abort_unless(in_array($cours->personnel?->type_personnel, ['vacataire', 'permanent'], true), 422, 'Personnel hors cours enseignants.');
        $seance = SeanceCours::with('plage')
            ->where('id_cours_enseignant', $cours->id)
            ->findOrFail($data['id_seance_cours']);

        $import = BiometrieImport::pourEnseignants()->lockForUpdate()->findOrFail($data['id_biometrie_import']);

        $dateSeance = Carbon::parse($data['date_seance']);
        if (!$import->estRequete()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['import' => 'Toute correction manuelle doit etre enregistree dans un dossier de requete. Creez ou choisissez un dossier lie a cet import et a ce mois.']);
        }
        if (!empty($data['id_heure_correction'])) {
            $correction = $import->heures_realisees()->whereNotNull('type_requete')->findOrFail($data['id_heure_correction']);
            if ((int) $correction->id_cours_enseignant !== (int) $data['id_cours_enseignant']
                || (int) $correction->id_seance_cours !== (int) $data['id_seance_cours']
                || $correction->date_seance->toDateString() !== $dateSeance->toDateString()) {
                throw \Illuminate\Validation\ValidationException::withMessages(['date_seance' => 'La modification doit concerner la seance initialement choisie.']);
            }
        }
        if ($import->exists && LigneVirementVacataire::where('id_personnel', $cours->id_personnel)
            ->where(fn($q) => $q->where('id_biometrie_import', $import->id)
                ->orWhereHas('virement', fn($v) => $v->where('id_import_parent', $import->id)))->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['import' => 'Cet enseignant a deja un ordre valide. Creez un nouveau dossier apres requete.']);
        }
        abort_unless($seance->plage?->type_plage === 'cours', 422, 'Choisissez une plage de cours.');
        if ($dateSeance->lt(Carbon::parse($import->date_debut)) || $dateSeance->gt(Carbon::parse($import->date_fin))) {
            return back()
                ->withErrors(['date_seance' => 'La date de la seance doit etre comprise dans la periode de l\'import manuel.'])
                ->withInput();
        }

        if ($dateSeance->lt(Carbon::parse($cours->date_debut)) || $dateSeance->gt(Carbon::parse($cours->date_fin))) {
            return back()
                ->withErrors(['date_seance' => 'La date de la seance doit etre comprise dans la periode du cours enseignant.'])
                ->withInput();
        }

        if ($dateSeance->dayOfWeekIso !== (int) $seance->jour_semaine) {
            return back()
                ->withErrors(['date_seance' => 'La date choisie ne correspond pas au jour programme pour cette seance.'])
                ->withInput();
        }

        $presenceImport = $data['presence_import'] ?? null;
        if ($presenceImport !== null) {
            $aPointage = BiometriePointage::where('id_biometrie_import', $import->id_import_parent)
                ->where('id_personnel', $cours->id_personnel)
                ->whereBetween('date_heure_pointage', [$import->date_debut->copy()->startOfDay(), $import->date_fin->copy()->endOfDay()])->exists();
            if (($presenceImport === 'present') !== $aPointage) {
                throw \Illuminate\Validation\ValidationException::withMessages(['presence_import' => $aPointage
                    ? 'Cet enseignant possede des pointages associes dans l import initial pour ce mois : choisissez Present.'
                    : 'Aucun pointage associe a cet enseignant dans l import initial pour ce mois : choisissez Non present.']);
            }
            if ($presenceImport === 'present' && !HeureRealiseeEnseignant::where('id_biometrie_import', $import->id_import_parent)
                ->where('id_personnel', $cours->id_personnel)->whereBetween('date_seance', [$import->date_debut, $import->date_fin])->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages(['presence_import' => 'Des pointages existent, mais aucune heure n est consolidee pour cet enseignant sur ce mois. Consolidez d abord l import initial.']);
            }
            if ($presenceImport === 'absent' && $data['type_requete'] !== 'presence_manuelle'
                && !(new PaieImportVacataire())->heuresReference($import, $cours->id_personnel)->isNotEmpty()) {
                throw \Illuminate\Validation\ValidationException::withMessages(['type_requete' => 'Sans consolidation initiale, choisissez une presence attestee et renseignez les horaires.']);
            }
        }
        (new PaieImportVacataire())->initialiserEnseignant($import, $cours->id_personnel,
            $presenceImport === 'absent' && $data['type_requete'] === 'presence_manuelle');
        $ancienneHeure = $import->heures_realisees()->where('id_seance_cours', $seance->id)->whereDate('date_seance', $dateSeance)->first();
        $plage = $seance->plage;
        $plannedStart = Carbon::parse($dateSeance->format('Y-m-d') . ' ' . ($ancienneHeure?->heure_debut_prevue ?? $plage->heure_debut));
        $plannedEnd = Carbon::parse($dateSeance->format('Y-m-d') . ' ' . ($ancienneHeure?->heure_fin_prevue ?? $plage->heure_fin));
        $plannedDuration = $ancienneHeure?->duree_prevue ?? round($plannedStart->diffInMinutes($plannedEnd) / 60, 2);
        if ($data['type_requete'] === 'absence_justifiee') {
            if (!$ancienneHeure) {
                throw \Illuminate\Validation\ValidationException::withMessages(['date_seance' => 'Aucune seance consolidee a justifier a cette date.']);
            }
            // Une justification ne constitue pas une preuve d'heures travaillees.
            $duration = $ancienneHeure->duree_realisee;
            $heureDebut = $ancienneHeure->heure_debut_reelle;
            $heureFin = $ancienneHeure->heure_fin_reelle;
        } else {
            $realStartInput = Carbon::parse($dateSeance->format('Y-m-d') . ' ' . $data['heure_debut_reelle']);
            $realEndInput = Carbon::parse($dateSeance->format('Y-m-d') . ' ' . $data['heure_fin_reelle']);
            $realStart = $realStartInput->greaterThan($plannedStart) ? $realStartInput : $plannedStart;
            $realEnd = $realEndInput->lessThan($plannedEnd) ? $realEndInput : $plannedEnd;
            $duration = $realEnd->greaterThan($realStart)
                ? min($plannedDuration, round($realStart->diffInMinutes($realEnd) / 60, 2)) : 0;
            if ($duration <= 0) {
                throw \Illuminate\Validation\ValidationException::withMessages(['heure_fin_reelle' => 'Les horaires attestes doivent couvrir une partie de la plage prevue.']);
            }
            $heureDebut = $realStart->format('H:i:s');
            $heureFin = $realEnd->format('H:i:s');
        }

        $taux = $cours->taux_horaire
            ?: TauxHoraire::where('statut', 'actif')->where('par_defaut', true)->first()
            ?: TauxHoraire::where('statut', 'actif')->orderByDesc('id')->first();
        $montantTaux = (float) ($ancienneHeure?->montant_taux ?? $taux->montant ?? 0);
        $missing = round($plannedDuration - $duration, 2);
        $observation = $data['observation'] ?? null;
        if ($missing > 0) {
            $observation = trim(($observation ? $observation . ' ' : '') . 'Saisie manuelle partielle : ' . number_format($missing, 2, ',', ' ') . ' h non comptabilisee(s).');
        }

        $heureRealisee = $ancienneHeure ?? new HeureRealiseeEnseignant([
            'id_biometrie_import' => $import->id, 'id_seance_cours' => $seance->id,
            'date_seance' => $dateSeance->toDateString(),
        ]);
        $heureRealisee->fill(
            [
                'id_cours_enseignant' => $cours->id,
                'id_personnel' => $cours->id_personnel,
                'id_programme_specialite' => $cours->id_programme_specialite,
                'id_taux_horaire' => $taux?->id,
                'id_salle' => $cours->id_salle,
                'id_cycle' => $cours->id_cycle,
                'id_filiere' => $cours->id_filiere,
                'id_niveau' => $cours->id_niveau,
                'id_specialite' => $cours->id_specialite,
                'id_annee_academique' => $cours->id_annee_academique,
                'id_entite' => $cours->id_entite,
                'jour_semaine' => $seance->jour_semaine,
                'id_plage_horaire' => $seance->id_plage_horaire,
                'heure_debut_prevue' => $plannedStart->format('H:i:s'),
                'heure_fin_prevue' => $plannedEnd->format('H:i:s'),
                'heure_debut_reelle' => $heureDebut,
                'heure_fin_reelle' => $heureFin,
                'duree_prevue' => $plannedDuration,
                'duree_realisee' => $duration,
                'montant_taux' => $montantTaux,
                'montant_total' => round($duration * $montantTaux, 2),
                'statut' => $duration > 0 ? 'realise' : 'non_realise',
                'observation' => $observation,
                'sanction_annulee' => $data['type_requete'] === 'absence_justifiee' || ($ancienneHeure?->sanction_annulee ?? false)
                    || (!$ancienneHeure && $presenceImport === 'absent' && $data['type_requete'] === 'presence_manuelle'),
                'type_requete' => $data['type_requete'],
                'duree_avant_requete' => $ancienneHeure?->duree_avant_requete ?? $ancienneHeure?->duree_realisee ?? 0,
                'montant_avant_requete' => $ancienneHeure?->montant_avant_requete ?? $ancienneHeure?->montant_total ?? 0,
                'sanction_avant_requete' => $ancienneHeure?->sanction_avant_requete
                    ?? (!$ancienneHeure || $ancienneHeure->sanction_annulee ? 0 : max($plannedDuration - $ancienneHeure->duree_realisee, 0) * $montantTaux),
                'id_user' => auth()->id() ?? 0,
            ]
        );

        $heureRealisee->save();
        $compteurs = ['total_consolidees' => $import->heures_realisees()->count()];
        if (in_array($import->type_import, ['manuel', 'requete'], true)) { $compteurs['total_lignes'] = $compteurs['total_consolidees']; }
        $import->update($compteurs);

        return redirect()
            ->route('biometrie_heures.manual.create', ['id_biometrie_import' => $import->id])
            ->with('success', 'Heure realisee enregistree manuellement.');
        });
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'libelle' => 'nullable|string|max:255',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'fichier' => 'required|file|max:20480',
        ]);

        $file = $request->file('fichier');
        $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9_.-]/', '_', $file->getClientOriginalName());
        $path = 'uploads/biometrie/' . $fileName;
        File::ensureDirectoryExists(public_path('uploads/biometrie'));
        $file->move(public_path('uploads/biometrie'), $fileName);

        $import = BiometrieImport::create([
            'libelle' => $data['libelle'] ?: 'Import biometrie du ' . now()->format('d/m/Y H:i'),
            'date_debut' => $data['date_debut'],
            'date_fin' => $data['date_fin'],
            'fichier' => $path,
            'statut' => 'importe',
            'type_import' => 'cours',
            'id_user' => auth()->id() ?? 0,
        ]);

        [$created, $unmatched, $ignored] = $this->importBiometriePointages($import, public_path($path));

        $import->update([
            'total_lignes' => $created,
            'total_non_associees' => $unmatched,
        ]);

        return redirect()->route('biometrie_heures.index')
            ->with('success', "{$created} pointage(s) importe(s). {$unmatched} en attente de correspondance. {$ignored} hors personnel avec cours planifie ignore(s).");
    }

    public function consolider(BiometrieImport $import)
    {
        return DB::transaction(function () use ($import) {
        $import = BiometrieImport::whereKey($import->id)->lockForUpdate()->firstOrFail();
        if (\App\Models\VirementVacataire::where('id_biometrie_import', $import->id)->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['import' => 'Cet import possede un ordre valide : sa consolidation ne peut plus etre recalculee.']);
        }
        if ($import->statut === 'manuel') {
            return back()->withErrors(['import' => 'Cet import est manuel : utilise la saisie manuelle pour ajouter ou modifier les heures.']);
        }

        $this->reassocierPointages($import);

        $start = Carbon::parse($import->date_debut)->startOfDay();
        $end = Carbon::parse($import->date_fin)->endOfDay();
        $defaultTaux = TauxHoraire::where('statut', 'actif')->where('par_defaut', true)->first()
            ?: TauxHoraire::where('statut', 'actif')->orderByDesc('id')->first();

        $cours = CoursEnseignant::with(['personnel', 'programme', 'seances.plage', 'taux_horaire'])
            ->where('statut', 'actif')
            ->whereIn('id_personnel', $this->personnelsCoursPourImport($import))
            ->whereIn('id_personnel', $import->pointages()->whereNotNull('id_personnel')->select('id_personnel'))
            ->whereDate('date_debut', '<=', $end->toDateString())
            ->whereDate('date_fin', '>=', $start->toDateString())
            ->get();

        // Serialise les consolidations concurrentes pour un meme enseignant.
        personnel::whereIn('id', $cours->pluck('id_personnel')->unique())->orderBy('id')->lockForUpdate()->get();
        HeureRealiseeEnseignant::where('id_biometrie_import', $import->id)->delete();
        $anterieures = HeureRealiseeEnseignant::with('cours.programme')
            ->where('id_biometrie_import', '!=', $import->id)
            ->where(fn($q) => $q->whereHas('import', fn($s) => $s->whereNull('type_import')->orWhereIn('type_import', ['cours', 'manuel']))
                ->orWhereExists(fn($s) => $s->selectRaw('1')->from('lignes_virement_vacataires as lv')
                    ->whereColumn('lv.id_biometrie_import', 'heures_realisees_enseignants.id_biometrie_import')
                    ->whereColumn('lv.id_personnel', 'heures_realisees_enseignants.id_personnel')
                    ->whereColumn('lv.id_entite', 'heures_realisees_enseignants.id_entite')))
            ->whereIn('id_personnel', $cours->pluck('id_personnel')->unique())
            ->whereDate('date_seance', '>=', $start->toDateString())
            ->whereDate('date_seance', '<=', $end->toDateString())
            ->lockForUpdate()->get()->groupBy(fn($h) => $h->id_personnel.'|'.$h->date_seance->toDateString());

        $count = 0;

        $processedSeances = [];

        $ignorees = 0;
        DB::transaction(function () use ($cours, $import, $start, $end, $defaultTaux, $anterieures, &$count, &$processedSeances, &$ignorees) {
            foreach ($cours as $coursEnseignant) {
                $periodeStart = max(Carbon::parse($coursEnseignant->date_debut)->startOfDay(), $start);
                $periodeEnd = min(Carbon::parse($coursEnseignant->date_fin)->endOfDay(), $end);

                foreach ($coursEnseignant->seances as $seance) {
                    if (!$seance->plage || $seance->plage->type_plage !== 'cours') { continue; }
                    $date = $periodeStart->copy();
                    while ($date->lte($periodeEnd)) {
                        if ($date->dayOfWeekIso === (int) $seance->jour_semaine) {
                            $programme = $coursEnseignant->programme;
                            $dedupeSubject = $programme?->type_matiere === 'transversale'
                                ? 'transversale-matiere-' . ($programme->id_matiere ?? $coursEnseignant->id_programme_specialite)
                                : 'programme-' . $coursEnseignant->id_programme_specialite;

                            $dedupeKey = implode('|', [
                                $coursEnseignant->id_personnel,
                                $date->toDateString(),
                                $seance->id_plage_horaire,
                                $dedupeSubject,
                            ]);

                            $precedentes = $anterieures->get($coursEnseignant->id_personnel.'|'.$date->toDateString(), collect());
                            $deja = $precedentes->contains(function ($h) use ($seance, $dedupeSubject) {
                                $programme = $h->cours?->programme;
                                $sujet = $programme?->type_matiere === 'transversale'
                                    ? 'transversale-matiere-'.($programme->id_matiere ?? $h->id_programme_specialite)
                                    : 'programme-'.$h->id_programme_specialite;
                                return $h->id_seance_cours == $seance->id || ($h->id_plage_horaire == $seance->id_plage_horaire && $sujet === $dedupeSubject);
                            });
                            if ($deja) {
                                $ignorees++;
                                $date->addDay();
                                continue;
                            }
                            $debutPlage = Carbon::parse($seance->plage->heure_debut)->format('H:i:s');
                            $finPlage = Carbon::parse($seance->plage->heure_fin)->format('H:i:s');
                            $chevauche = $precedentes->contains(fn($h) => Carbon::parse($h->heure_debut_prevue)->format('H:i:s') < $finPlage
                                && Carbon::parse($h->heure_fin_prevue)->format('H:i:s') > $debutPlage);
                            if ($chevauche) {
                                throw \Illuminate\Validation\ValidationException::withMessages(['import' => 'Une nouvelle seance chevauche une plage deja consolidee pour cet enseignant le '.$date->format('d/m/Y').'. Verifiez les emplois du temps avant de continuer.']);
                            }

                            if (isset($processedSeances[$dedupeKey])) {
                                $date->addDay();
                                continue;
                            }

                            $processedSeances[$dedupeKey] = true;
                            $count += $this->consoliderSeance($import, $coursEnseignant, $seance, $date, $defaultTaux);
                        }
                        $date->addDay();
                    }
                }
            }
        });

        $import->update([
            'statut' => 'consolide',
            'total_consolidees' => $count,
        ]);

        return back()->with('success', "{$count} plage(s) consolidee(s), {$ignorees} deja presente(s) dans un autre import et ignoree(s).");
        });
    }

    public function clearConsolidation(BiometrieImport $import)
    {
        return DB::transaction(function () use ($import) {
        $import = BiometrieImport::whereKey($import->id)->lockForUpdate()->firstOrFail();
        if (\App\Models\VirementVacataire::where('id_biometrie_import', $import->id)->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'import' => 'Les heures d un dossier avec ordre valide ne peuvent plus etre videes. Creez un nouveau dossier pour une autre decision.',
            ]);
        }
        $deleted = HeureRealiseeEnseignant::where('id_biometrie_import', $import->id)->delete();
        $import->update([
            'statut' => $import->statut === 'manuel' ? 'manuel' : 'importe',
            'total_consolidees' => 0,
            'total_lignes' => $import->statut === 'manuel' ? 0 : $import->total_lignes,
        ]);

        return back()->with('success', "{$deleted} consolidation(s) supprimee(s).");
        });
    }

    public function destroy(BiometrieImport $import)
    {
        DB::transaction(function () use ($import) {
            $source = BiometrieImport::whereKey($import->id)->lockForUpdate()->firstOrFail();
            if (\App\Models\VirementVacataire::where('id_biometrie_import', $source->id)->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'import' => 'Ce dossier de requete possede deja un ordre de virement valide et ne peut plus etre supprime.',
                ]);
            }
            HeureRealiseeEnseignant::where('id_biometrie_import', $source->id)->delete();
            BiometriePointage::where('id_biometrie_import', $source->id)->delete();
            $source->delete();
        });

        return $import->estRequete()
            ? redirect()->route('biometrie_heures.manual.create')->with('success', 'Dossier de requete supprime. L import initial est conserve.')
            : redirect()->route('biometrie_heures.index')->with('success', 'Import biometrie supprime.');
    }

    public function nettoyerOrphelins(Request $request)
    {
        $request->validate(['confirmation' => 'accepted']);
        [$pointages, $heures] = DB::transaction(function () {
            return [
                BiometriePointage::whereDoesntHave('import')->delete(),
                HeureRealiseeEnseignant::whereDoesntHave('import')->delete(),
            ];
        });

        return redirect()->route('biometrie_heures.index')
            ->with('success', $pointages.' pointage(s) et '.$heures.' heure(s) consolidee(s) sans import supprime(s).');
    }

    public function storeMapping(Request $request)
    {
        $data = $request->validate([
            'id_personnel' => 'required|integer|exists:personnels,id',
            'nom_biometrie' => 'required_without:numero_biometrie|nullable|string|max:255',
            'numero_biometrie' => 'required_without:nom_biometrie|nullable|string|max:255',
        ]);

        DB::table('biometrie_personnel_mappings')->updateOrInsert(
            [
                'id_personnel' => $data['id_personnel'],
                'nom_biometrie' => $data['nom_biometrie'] ?? null,
                'numero_biometrie' => $data['numero_biometrie'] ?? null,
            ],
            [
                'id_user' => auth()->id() ?? 0,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        if (!empty($data['nom_biometrie'])) {
            personnel::whereKey($data['id_personnel'])->update([
                'nom_biometrie' => trim($data['nom_biometrie']),
            ]);
        }

        BiometriePointage::query()
            ->when(!empty($data['numero_biometrie']), fn($q) => $q->where('numero_biometrie', $data['numero_biometrie']))
            ->when(empty($data['numero_biometrie']) && !empty($data['nom_biometrie']), fn($q) => $q->where('nom_biometrie', $data['nom_biometrie']))
            ->update(['id_personnel' => $data['id_personnel']]);

        BiometrieImport::all()->each(function ($import) {
            $import->update([
                'total_non_associees' => $import->pointages()->whereNull('id_personnel')->count(),
            ]);
        });

        return back()->with('success', 'Association biometrie / enseignant enregistree. Tu peux relancer la consolidation.');
    }

    private function importBiometriePointages(BiometrieImport $import, string $path): array
    {
        $rows = $this->readBiometrieFile($path);
        $matcher = $this->personnelMatcher();
        $eligibleIds = $import->type_import === 'permanent'
            ? null : $this->personnelsCoursPourImport($import)->all();
        $created = 0;
        $unmatched = 0;
        $ignored = 0;

        DB::transaction(function () use ($rows, $import, $matcher, $eligibleIds, &$created, &$unmatched, &$ignored) {
            foreach ($rows as $row) {
                $date = $this->parseDateTime($row['date_time'] ?? null);
                if (!$date) {
                    continue;
                }

                $personnel = $matcher->match($row['name'] ?? '', isset($row['no']) ? (string) $row['no'] : null);
                if ($personnel && $eligibleIds !== null && !in_array($personnel->id, $eligibleIds)) {
                    $ignored++;
                    continue;
                }
                if (!$personnel) {
                    $unmatched++;
                }

                BiometriePointage::create([
                    'id_biometrie_import' => $import->id,
                    'id_personnel' => $personnel?->id,
                    'departement' => $row['department'] ?? null,
                    'nom_biometrie' => $row['name'] ?? null,
                    'numero_biometrie' => $row['no'] ?? null,
                    'date_heure_pointage' => $date,
                    'location_id' => $row['location_id'] ?? null,
                    'id_number' => $row['id_number'] ?? null,
                    'verify_code' => $row['verify_code'] ?? null,
                    'card_no' => $row['card_no'] ?? null,
                    'raw_data' => $row,
                    'id_user' => auth()->id() ?? 0,
                ]);
                $created++;
            }
        });

        return [$created, $unmatched, $ignored];
    }

    private function personnelsCoursPourImport(BiometrieImport $import)
    {
        return CoursEnseignant::where('statut', 'actif')
            ->whereDate('date_debut', '<=', $import->date_fin->toDateString())
            ->whereDate('date_fin', '>=', $import->date_debut->toDateString())
            ->whereHas('personnel', fn($query) => $query->whereIn('type_personnel', ['vacataire', 'permanent']))
            ->whereHas('seances.plage', fn($query) => $query->where('type_plage', 'cours'))
            ->distinct()->pluck('id_personnel');
    }

    private function consoliderPresencePermanent(BiometrieImport $import, EmploiPermanent $emploi, Carbon $date): void
    {
        $plage = $emploi->plage;
        $plannedStart = Carbon::parse($date->format('Y-m-d') . ' ' . $plage->heure_debut);
        $plannedEnd = Carbon::parse($date->format('Y-m-d') . ' ' . $plage->heure_fin);
        $plannedDuration = $this->plannedDuration($plannedStart, $plannedEnd, $plage);
        $pointages = $this->dailyPointagesForPersonnel($import, $emploi->id_personnel, $date);
        $interval = $this->permanentIntervalForDay($pointages);

        $realStart = null;
        $realEnd = null;
        $duration = 0;
        $statut = 'absent';
        $observation = 'Aucun pointage complet pour la journee.';

        if ($interval) {
            [$entry, $exit] = $interval;
            $realStart = $entry;
            $realEnd = $exit;
            [$duration, $observation] = $this->permanentPresenceDuration(
                $emploi,
                $entry,
                $exit,
                $plannedStart,
                $plannedEnd,
                $plannedDuration
            );
            $statut = $duration >= $plannedDuration ? 'present' : ($duration > 0 ? 'partiel' : 'absent');
        } elseif ($pointages->count() === 1) {
            $singlePointage = Carbon::parse($pointages->first()->date_heure_pointage);
            [$duration, $realStart, $realEnd, $observation] = $this->singlePermanentPointageDuration(
                $singlePointage,
                $plannedStart,
                $plannedEnd,
                $plannedDuration
            );
            $statut = $duration > 0 ? 'partiel' : 'absent';
        }

        $salaire = $this->salairePermanentForDate(
            $emploi->id_personnel,
            $date,
            $emploi->id_annee_academique,
            $emploi->id_entite
        );
        $salaireJournalier = SalairePermanentController::salaireJournalier($salaire, $date);
        $heuresJour = $this->heuresReferenceSalairePermanent($plannedDuration);
        $tauxHorairePermanent = $heuresJour > 0 ? round($salaireJournalier / $heuresJour, 6) : 0;
        $montantDu = $tauxHorairePermanent > 0
            ? round($tauxHorairePermanent * min($duration, $heuresJour), 2)
            : 0;
        $penalite = max(round($salaireJournalier - $montantDu, 2), 0);

        PresencePermanent::updateOrCreate(
            [
                'id_biometrie_import' => $import->id,
                'id_emploi_permanent' => $emploi->id,
                'date_presence' => $date->toDateString(),
            ],
            [
                'id_personnel' => $emploi->id_personnel,
                'id_plage_horaire' => $emploi->id_plage_horaire,
                'id_annee_academique' => $emploi->id_annee_academique,
                'id_entite' => $emploi->id_entite,
                'jour_semaine' => $emploi->jour_semaine,
                'heure_debut_prevue' => $plannedStart->format('H:i:s'),
                'heure_fin_prevue' => $plannedEnd->format('H:i:s'),
                'heure_debut_reelle' => $realStart?->format('H:i:s'),
                'heure_fin_reelle' => $realEnd?->format('H:i:s'),
                'duree_prevue' => $plannedDuration,
                'duree_realisee' => $duration,
                'salaire_journalier' => $salaireJournalier,
                'montant_du' => $montantDu,
                'penalite_montant' => $penalite,
                'statut' => $statut,
                'observation' => trim(($observation ? $observation . ' ' : '') . 'Taux horaire permanent : ' . number_format($tauxHorairePermanent, 2, ',', ' ') . ' FCFA/h.'),
                'id_user' => auth()->id() ?? 0,
            ]
        );

        $this->syncDisciplineForPresencePermanent($import, $emploi, $date, $plannedStart, $plannedDuration, $realStart, $duration, $observation, $pointages->count() > 0);
    }

    private function consoliderJoursOuvrablesSansEmploi(BiometrieImport $import, Carbon $start, Carbon $end, array &$processedDatesByPersonnel): int
    {
        $salaires = SalairePermanent::with('personnel')
            ->where('statut', 'actif')
            ->whereDate('date_debut', '<=', $end->toDateString())
            ->where(function ($query) use ($start) {
                $query->whereNull('date_fin')
                    ->orWhereDate('date_fin', '>=', $start->toDateString());
            })
            ->get();

        $count = 0;
        foreach ($salaires as $salaire) {
            $personnel = $salaire->personnel;
            if (!$personnel || ($personnel->type_personnel ?? null) !== 'permanent') {
                continue;
            }

            $date = max(Carbon::parse($salaire->date_debut)->startOfDay(), $start);
            $periodeEnd = $salaire->date_fin
                ? min(Carbon::parse($salaire->date_fin)->endOfDay(), $end)
                : $end->copy();

            while ($date->lte($periodeEnd)) {
                $dateKey = $date->toDateString();
                if (!$this->isPaidNonWorkingDay($date) && empty($processedDatesByPersonnel[$personnel->id][$dateKey])) {
                    $this->enregistrerJourPayeSansHoraire(
                        $import,
                        $salaire,
                        $date,
                        'Jour paye non travaille : aucun horaire programme.'
                    );
                    $processedDatesByPersonnel[$personnel->id][$dateKey] = true;
                    $count++;
                }

                $date->addDay();
            }
        }

        return $count;
    }

    private function enregistrerJourPayeSansHoraire(BiometrieImport $import, SalairePermanent $salaire, Carbon $date, string $observation): void
    {
        $salaireJournalier = SalairePermanentController::salaireJournalier($salaire, $date);
        PresencePermanent::updateOrCreate(
            [
                'id_biometrie_import' => $import->id,
                'id_emploi_permanent' => -1 * (int) $salaire->id_personnel,
                'date_presence' => $date->toDateString(),
            ],
            [
                'id_personnel' => $salaire->id_personnel,
                'id_plage_horaire' => null,
                'id_annee_academique' => $salaire->id_annee_academique,
                'id_entite' => $salaire->id_entite,
                'jour_semaine' => $date->dayOfWeekIso,
                'heure_debut_prevue' => null,
                'heure_fin_prevue' => null,
                'heure_debut_reelle' => null,
                'heure_fin_reelle' => null,
                'duree_prevue' => 0,
                'duree_realisee' => 0,
                'salaire_journalier' => $salaireJournalier,
                'montant_du' => $salaireJournalier,
                'penalite_montant' => 0,
                'statut' => 'jour_paye',
                'observation' => $observation,
                'id_user' => auth()->id() ?? 0,
            ]
        );

        DisciplinePersonnel::where('id_biometrie_import', $import->id)
            ->where('id_personnel', $salaire->id_personnel)
            ->whereDate('date_discipline', $date->toDateString())
            ->where('statut', 'non_justifie')
            ->update(['statut' => 'annule']);
    }

    private function defaultPermanentPlageForPersonnel(personnel $personnel): ?PlageHoraire
    {
        $libelle = ($personnel->horaire_travail ?? '') === 'permanent_soir'
            ? 'Permanent soir'
            : 'Permanent jour';

        return PlageHoraire::where('libelle', $libelle)->where('statut', 'actif')->first()
            ?: PlageHoraire::where('type_personnel', 'permanent')->where('statut', 'actif')->orderBy('ordre')->first();
    }

    private function consoliderJoursPayesNonTravailles(BiometrieImport $import, Carbon $start, Carbon $end, array $processedDatesByPersonnel): int
    {
        $salaires = SalairePermanent::with('personnel')
            ->where('statut', 'actif')
            ->whereDate('date_debut', '<=', $end->toDateString())
            ->where(function ($query) use ($start) {
                $query->whereNull('date_fin')
                    ->orWhereDate('date_fin', '>=', $start->toDateString());
            })
            ->get();

        $count = 0;
        foreach ($salaires as $salaire) {
            if (!$salaire->personnel || ($salaire->personnel->type_personnel ?? null) !== 'permanent') {
                continue;
            }

            $date = max(Carbon::parse($salaire->date_debut)->startOfDay(), $start);
            $periodeEnd = $salaire->date_fin
                ? min(Carbon::parse($salaire->date_fin)->endOfDay(), $end)
                : $end->copy();

            while ($date->lte($periodeEnd)) {
                if ($this->isPaidNonWorkingDay($date) && empty($processedDatesByPersonnel[$salaire->id_personnel][$date->toDateString()])) {
                    $this->enregistrerJourPayeSansHoraire($import, $salaire, $date, $this->paidDayLabel($date));
                    $count++;
                }

                $date->addDay();
            }
        }

        return $count;
    }

    private function isPaidNonWorkingDay(Carbon $date): bool
    {
        return $date->isWeekend() || $this->isCameroonHoliday($date);
    }

    private function paidDayLabel(Carbon $date): string
    {
        if ($date->isWeekend()) {
            return 'Jour paye non travaille : week-end.';
        }

        return 'Jour paye non travaille : jour ferie.';
    }

    private function isCameroonHoliday(Carbon $date): bool
    {
        $monthDay = $date->format('m-d');
        $fixed = ['01-01', '02-11', '05-01', '05-20', '08-15', '11-01', '12-25'];
        if (in_array($monthDay, $fixed, true)) {
            return true;
        }

        $year = (int) $date->format('Y');
        $easter = Carbon::createFromTimestamp(easter_date($year));
        $movableChristian = [
            $easter->copy()->subDays(2)->toDateString(),
            $easter->copy()->addDay()->toDateString(),
            $easter->copy()->addDays(39)->toDateString(),
        ];
        $movableMuslim = [
            2026 => ['2026-03-20', '2026-05-27'],
            2027 => ['2027-03-10', '2027-03-16'],
            2028 => ['2028-02-27'],
        ];

        return in_array($date->toDateString(), $movableChristian, true)
            || in_array($date->toDateString(), $movableMuslim[$year] ?? [], true);
    }

    private function permanentPresenceDuration(EmploiPermanent $emploi, Carbon $entry, Carbon $exit, Carbon $plannedStart, Carbon $plannedEnd, float $plannedDuration): array
    {
        $worked = max($entry->diffInMinutes($exit) / 60, 0);
        $personnel = $emploi->personnel;
        $isFlexible = ($personnel->mode_horaire ?? 'strict') === 'souple'
            || in_array(($personnel->categorie_horaire ?? 'standard'), ['conseil_administration', 'chef_service', 'coordination'], true);

        if ($isFlexible) {
            $duration = min($plannedDuration, round($worked, 2));
            $missing = round($plannedDuration - $duration, 2);

            return [
                $duration,
                $missing > 0
                    ? 'Horaire souple : quota journalier incomplet, ' . number_format($missing, 2, ',', ' ') . ' h non comptabilisee(s).'
                    : 'Horaire souple : quota journalier atteint.',
            ];
        }

        $lateMinutes = $entry->greaterThan($plannedStart) ? $plannedStart->diffInMinutes($entry) : 0;
        $penaltyHours = $lateMinutes > 0 ? (int) ceil($lateMinutes / 60) : 0;
        $duration = max($plannedDuration - $penaltyHours, 0);

        if ($exit->lessThan($plannedEnd)) {
            $duration = min($duration, round(max($entry->diffInMinutes($exit) / 60, 0), 2));
        }

        $duration = min($duration, $plannedDuration);
        $observation = $penaltyHours > 0
            ? 'Horaire strict : retard de ' . $lateMinutes . ' min, penalite ' . $penaltyHours . ' h.'
            : null;

        return [round($duration, 2), $observation];
    }

    private function singlePermanentPointageDuration(Carbon $pointage, Carbon $plannedStart, Carbon $plannedEnd, float $plannedDuration): array
    {
        $midpoint = $plannedStart->copy()->addMinutes((int) floor($plannedStart->diffInMinutes($plannedEnd) / 2));
        $isLikelyEntry = $pointage->lessThanOrEqualTo($midpoint);

        if ($isLikelyEntry) {
            $realStart = $pointage;
            $realEnd = $plannedEnd;
        } else {
            $realStart = $plannedStart;
            $realEnd = $pointage;
        }

        $duration = 0;
        $missing = $plannedDuration;
        $observation = 'Pointage unique detecte : '
            . ($isLikelyEntry ? 'sortie manquante' : 'entree manquante')
            . ($missing > 0 ? ', ' . number_format($missing, 2, ',', ' ') . ' h non comptabilisee(s).' : '.');

        return [round($duration, 2), $realStart, $realEnd, $observation];
    }

    private function heuresReferenceSalairePermanent(float $plannedDuration): float
    {
        return 8.0;
    }

    private function salairePermanentForDate(int $personnelId, Carbon $date, ?int $anneeId, ?int $entiteId): ?SalairePermanent
    {
        return SalairePermanent::where('id_personnel', $personnelId)
            ->where('statut', 'actif')
            ->whereDate('date_debut', '<=', $date->toDateString())
            ->where(function ($query) use ($date) {
                $query->whereNull('date_fin')
                    ->orWhereDate('date_fin', '>=', $date->toDateString());
            })
            ->where(function ($query) use ($anneeId) {
                $query->whereNull('id_annee_academique');
                if ($anneeId) {
                    $query->orWhere('id_annee_academique', $anneeId);
                }
            })
            ->where(function ($query) use ($entiteId) {
                $query->whereNull('id_entite');
                if ($entiteId) {
                    $query->orWhere('id_entite', $entiteId);
                }
            })
            ->orderByDesc('id_annee_academique')
            ->orderByDesc('id_entite')
            ->orderByDesc('date_debut')
            ->first();
    }

    private function syncDisciplineForPresencePermanent(BiometrieImport $import, EmploiPermanent $emploi, Carbon $date, Carbon $plannedStart, float $plannedDuration, ?Carbon $realStart, float $duration, ?string $observation, bool $hasPointageOnDay = false): void
    {
        $base = [
            'id_personnel' => $emploi->id_personnel,
            'id_cours_enseignant' => null,
            'id_seance_cours' => null,
            'id_biometrie_import' => $import->id,
            'date_discipline' => $date->toDateString(),
        ];

        $plannedEnd = Carbon::parse($date->format('Y-m-d') . ' ' . ($emploi->plage->heure_fin ?? $plannedStart->format('H:i:s')));
        $hasPointageInPlage = $realStart && $realStart->betweenIncluded($plannedStart, $plannedEnd);

        if (!$hasPointageOnDay || !$hasPointageInPlage || $duration <= 0) {
            DisciplinePersonnel::updateOrCreate(
                array_merge($base, ['type_discipline' => 'absence']),
                [
                    'id_annee_academique' => $emploi->id_annee_academique,
                    'id_entite' => $emploi->id_entite,
                    'duree_heures' => $plannedDuration,
                    'minutes_retard' => 0,
                    'motif' => $observation ?: 'Absence detectee automatiquement : aucun pointage dans la plage prevue.',
                    'statut' => 'non_justifie',
                    'id_user' => auth()->id() ?? 0,
                ]
            );
            $this->annulerDisciplineAutomatique(array_merge($base, ['type_discipline' => 'retard']));
            return;
        }

        $this->annulerDisciplineAutomatique(array_merge($base, ['type_discipline' => 'absence']));
        $personnel = $emploi->personnel;
        $isFlexible = ($personnel->mode_horaire ?? 'strict') === 'souple'
            || in_array(($personnel->categorie_horaire ?? 'standard'), ['conseil_administration', 'chef_service', 'coordination'], true);
        $minutesRetard = (!$isFlexible && $realStart && $realStart->greaterThan($plannedStart))
            ? $plannedStart->diffInMinutes($realStart)
            : 0;

        if ($minutesRetard > 0) {
            DisciplinePersonnel::updateOrCreate(
                array_merge($base, ['type_discipline' => 'retard']),
                [
                    'id_annee_academique' => $emploi->id_annee_academique,
                    'id_entite' => $emploi->id_entite,
                    'duree_heures' => 0,
                    'minutes_retard' => $minutesRetard,
                    'motif' => 'Retard detecte automatiquement sur emploi permanent : arrivee a ' . $realStart->format('H:i') . ' au lieu de ' . $plannedStart->format('H:i') . '.',
                    'statut' => 'non_justifie',
                    'id_user' => auth()->id() ?? 0,
                ]
            );
        } else {
            $this->annulerDisciplineAutomatique(array_merge($base, ['type_discipline' => 'retard']));
        }
    }

    private function consoliderSeance(BiometrieImport $import, CoursEnseignant $cours, $seance, Carbon $date, ?TauxHoraire $defaultTaux): int
    {
        $plage = $seance->plage;
        if (!$plage || $plage->type_plage !== 'cours') {
            return 0;
        }

        $plannedStart = Carbon::parse($date->format('Y-m-d') . ' ' . $plage->heure_debut);
        $plannedEnd = Carbon::parse($date->format('Y-m-d') . ' ' . $plage->heure_fin);
        $plannedDuration = $this->plannedDuration($plannedStart, $plannedEnd, $plage);
        $pointages = $this->dailyPointagesForCours($import, $cours, $date);
        $matchingInterval = $this->intervalForPlage($pointages, $plannedStart, $plannedEnd, $date);

        $realStart = null;
        $realEnd = null;
        $duration = 0;
        $statut = 'non_realise';
        $observation = 'Aucun pointage complet sur la plage.';

        if ($matchingInterval) {
            [$entry, $exit] = $matchingInterval;
            $realStart = $entry->greaterThan($plannedStart) ? $entry : $plannedStart;
            $realEnd = $exit->lessThan($plannedEnd) ? $exit : $plannedEnd;

            if ($realEnd->greaterThan($realStart)) {
                $duration = min($plannedDuration, round($realStart->diffInMinutes($realEnd) / 60, 2));
                $statut = $duration > 0 ? 'realise' : 'non_realise';
                $missing = round($plannedDuration - $duration, 2);
                $observation = $missing > 0
                    ? 'Pointage partiel : ' . number_format($missing, 2, ',', ' ') . ' h non comptabilisee(s).'
                    : null;
            }
        } elseif ($pointages->count() === 1) {
            $only = Carbon::parse($pointages->first()->date_heure_pointage);
            $realStart = $only;
            $realEnd = null;
            $observation = 'Pointage incomplet le ' . $date->format('d/m/Y') . ' : entree ou sortie manquante, ' . number_format($plannedDuration, 2, ',', ' ') . ' h non comptabilisee(s).';
        } elseif ($pointages->count() > 1) {
            $observation = 'Pointages detectes le ' . $date->format('d/m/Y') . ', mais aucune entree/sortie complete ne couvre cette plage : ' . number_format($plannedDuration, 2, ',', ' ') . ' h non comptabilisee(s).';
        }

        $taux = $cours->taux_horaire ?: $defaultTaux;
        $montantTaux = (float) ($taux->montant ?? 0);

        HeureRealiseeEnseignant::updateOrCreate(
            [
                'id_biometrie_import' => $import->id,
                'id_seance_cours' => $seance->id,
                'date_seance' => $date->toDateString(),
            ],
            [
                'id_cours_enseignant' => $cours->id,
                'id_personnel' => $cours->id_personnel,
                'id_programme_specialite' => $cours->id_programme_specialite,
                'id_taux_horaire' => $taux?->id,
                'id_salle' => $cours->id_salle,
                'id_cycle' => $cours->id_cycle,
                'id_filiere' => $cours->id_filiere,
                'id_niveau' => $cours->id_niveau,
                'id_specialite' => $cours->id_specialite,
                'id_annee_academique' => $cours->id_annee_academique,
                'id_entite' => $cours->id_entite,
                'jour_semaine' => $seance->jour_semaine,
                'id_plage_horaire' => $seance->id_plage_horaire,
                'heure_debut_prevue' => $plannedStart->format('H:i:s'),
                'heure_fin_prevue' => $plannedEnd->format('H:i:s'),
                'heure_debut_reelle' => $realStart?->format('H:i:s'),
                'heure_fin_reelle' => $realEnd?->format('H:i:s'),
                'duree_prevue' => $plannedDuration,
                'duree_realisee' => $duration,
                'montant_taux' => $montantTaux,
                'montant_total' => round($duration * $montantTaux, 2),
                'statut' => $statut,
                'observation' => $observation,
                'id_user' => auth()->id() ?? 0,
            ]
        );

        $this->syncDisciplineForHeure($import, $cours, $seance, $date, $plannedStart, $plannedEnd, $plannedDuration, $realStart, $duration, $observation, $pointages->count() > 0);

        return 1;
    }

    private function syncDisciplineForHeure(BiometrieImport $import, CoursEnseignant $cours, $seance, Carbon $date, Carbon $plannedStart, Carbon $plannedEnd, float $plannedDuration, ?Carbon $realStart, float $duration, ?string $observation, bool $hasPointageOnDay = false): void
    {
        $base = [
            'id_personnel' => $cours->id_personnel,
            'id_cours_enseignant' => $cours->id,
            'id_seance_cours' => $seance->id,
            'id_biometrie_import' => $import->id,
            'date_discipline' => $date->toDateString(),
        ];

        $hasPointageInPlage = $realStart && $realStart->betweenIncluded($plannedStart, $plannedEnd);

        if (!$hasPointageOnDay || !$hasPointageInPlage) {
            DisciplinePersonnel::updateOrCreate(
                array_merge($base, ['type_discipline' => 'absence']),
                [
                    'id_annee_academique' => $cours->id_annee_academique,
                    'id_entite' => $cours->id_entite,
                    'duree_heures' => $plannedDuration,
                    'minutes_retard' => 0,
                    'motif' => $observation ?: 'Absence detectee automatiquement : aucun pointage dans la plage prevue.',
                    'statut' => 'non_justifie',
                    'id_user' => auth()->id() ?? 0,
                ]
            );
            $this->annulerDisciplineAutomatique(array_merge($base, ['type_discipline' => 'retard']));
            return;
        }

        $this->annulerDisciplineAutomatique(array_merge($base, ['type_discipline' => 'absence']));

        $minutesRetard = $realStart && $realStart->greaterThan($plannedStart)
            ? $plannedStart->diffInMinutes($realStart)
            : 0;

        if ($minutesRetard > 0) {
            DisciplinePersonnel::updateOrCreate(
                array_merge($base, ['type_discipline' => 'retard']),
                [
                    'id_annee_academique' => $cours->id_annee_academique,
                    'id_entite' => $cours->id_entite,
                    'duree_heures' => 0,
                    'minutes_retard' => $minutesRetard,
                    'motif' => 'Retard detecte automatiquement : arrivee a ' . $realStart->format('H:i') . ' au lieu de ' . $plannedStart->format('H:i') . '.',
                    'statut' => 'non_justifie',
                    'id_user' => auth()->id() ?? 0,
                ]
            );
        } else {
            $this->annulerDisciplineAutomatique(array_merge($base, ['type_discipline' => 'retard']));
        }
    }

    private function annulerDisciplineAutomatique(array $base): void
    {
        DisciplinePersonnel::where($base)
            ->where('statut', 'non_justifie')
            ->update(['statut' => 'annule']);
    }

    private function dailyPointagesForCours(BiometrieImport $import, CoursEnseignant $cours, Carbon $date)
    {
        return BiometriePointage::where('id_biometrie_import', $import->id)
            ->where('id_personnel', $cours->id_personnel)
            ->whereBetween('date_heure_pointage', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])
            ->orderBy('date_heure_pointage')
            ->get();
    }

    private function dailyPointagesForPersonnel(BiometrieImport $import, int $personnelId, Carbon $date)
    {
        return BiometriePointage::where('id_biometrie_import', $import->id)
            ->where('id_personnel', $personnelId)
            ->whereBetween('date_heure_pointage', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])
            ->orderBy('date_heure_pointage')
            ->get();
    }

    private function permanentIntervalForDay($pointages): ?array
    {
        $times = $pointages
            ->map(fn($pointage) => Carbon::parse($pointage->date_heure_pointage))
            ->sort()
            ->values();

        if ($times->count() < 2) {
            return null;
        }

        return [$times->first(), $times->last()];
    }

    private function permanentDuration(CoursEnseignant $cours, Carbon $entry, Carbon $exit, Carbon $plannedStart, Carbon $plannedEnd, float $plannedDuration): array
    {
        $realStart = $entry;
        $realEnd = $exit;
        $worked = max($entry->diffInMinutes($exit) / 60, 0);
        $personnel = $cours->personnel;
        $isFlexible = ($personnel->mode_horaire ?? 'strict') === 'souple'
            || in_array(($personnel->categorie_horaire ?? 'standard'), ['conseil_administration', 'chef_service', 'coordination'], true);

        if ($isFlexible) {
            $duration = min($plannedDuration, round($worked, 2));
            $missing = round($plannedDuration - $duration, 2);
            $observation = $missing > 0
                ? 'Permanent souple : quota journalier incomplet, ' . number_format($missing, 2, ',', ' ') . ' h non comptabilisee(s).'
                : 'Permanent souple : quota journalier atteint.';

            return [$duration, $realStart, $realEnd, $observation];
        }

        $lateMinutes = $entry->greaterThan($plannedStart) ? $plannedStart->diffInMinutes($entry) : 0;
        $penaltyHours = $lateMinutes > 0 ? (int) ceil($lateMinutes / 60) : 0;
        $duration = max($plannedDuration - $penaltyHours, 0);

        if ($exit->lessThan($plannedEnd)) {
            $duration = min($duration, round(max($entry->diffInMinutes($exit) / 60, 0), 2));
        }

        $observation = $penaltyHours > 0
            ? 'Permanent strict : retard de ' . $lateMinutes . ' min, penalite ' . $penaltyHours . ' h.'
            : null;

        return [round($duration, 2), $realStart, $realEnd, $observation];
    }

    private function plannedDuration(Carbon $plannedStart, Carbon $plannedEnd, $plage): float
    {
        if ($plage && $plage->duree_payable !== null) {
            return (float) $plage->duree_payable;
        }

        return round($plannedStart->diffInMinutes($plannedEnd) / 60, 2);
    }

    private function intervalForPlage($pointages, Carbon $plannedStart, Carbon $plannedEnd, Carbon $date): ?array
    {
        $times = $pointages
            ->map(fn($pointage) => Carbon::parse($pointage->date_heure_pointage))
            ->sort()
            ->values();

        if ($times->count() < 2) {
            return null;
        }

        $entryWindowStart = $plannedStart->copy()->subMinutes(30);
        $entryWindowEnd = $plannedStart->copy()->addMinutes(30);
        $exitWindowStart = $plannedEnd->copy()->subMinutes(30);
        $exitWindowEnd = $plannedEnd->copy()->addMinutes(30);

        $entry = $times
            ->filter(fn($time) => !$this->isPauseTime($time, $date)
                && $time->betweenIncluded($entryWindowStart, $entryWindowEnd))
            ->sortBy(fn($time) => abs($time->diffInSeconds($plannedStart, false)))
            ->first();

        $exit = $times
            ->filter(fn($time) => $entry
                && $time->greaterThan($entry)
                && $time->betweenIncluded($exitWindowStart, $exitWindowEnd))
            ->sortBy(fn($time) => abs($time->diffInSeconds($plannedEnd, false)))
            ->first();

        if ($entry && $exit) {
            return [$entry, $exit];
        }

        $presenceIntervals = $this->presenceIntervals($times, $date);

        return $this->bestIntervalForPlage($presenceIntervals, $plannedStart, $plannedEnd);
    }

    private function presenceIntervals($times, Carbon $date): array
    {
        $intervals = [];
        $entry = null;

        foreach ($times as $time) {
            $isPause = $this->isPauseTime($time, $date);

            if (!$entry) {
                if ($isPause) {
                    continue;
                }

                $entry = $time;
                continue;
            }

            if ($time->greaterThan($entry)) {
                $intervals[] = [$entry, $time];
            }

            $entry = null;
        }

        return $intervals;
    }

    private function isPauseTime(Carbon $time, Carbon $date): bool
    {
        return \App\Models\PlageHoraire::where('type_plage', 'pause')
            ->where('statut', 'actif')
            ->get()
            ->contains(function ($plage) use ($time, $date) {
                $start = Carbon::parse($date->format('Y-m-d') . ' ' . $plage->heure_debut);
                $end = Carbon::parse($date->format('Y-m-d') . ' ' . $plage->heure_fin);

                return $time->greaterThanOrEqualTo($start) && $time->lessThan($end);
            });
    }

    private function bestIntervalForPlage(array $intervals, Carbon $plannedStart, Carbon $plannedEnd): ?array
    {
        $best = null;
        $bestMinutes = 0;
        $latestValidExit = $plannedEnd->copy()->subMinutes(30);

        foreach ($intervals as [$entry, $exit]) {
            if ($exit->lessThan($latestValidExit)) {
                continue;
            }

            $overlapStart = $entry->greaterThan($plannedStart) ? $entry : $plannedStart;
            $overlapEnd = $exit->lessThan($plannedEnd) ? $exit : $plannedEnd;

            if ($overlapEnd->greaterThan($overlapStart)) {
                $minutes = $overlapStart->diffInMinutes($overlapEnd);
                if ($minutes > $bestMinutes) {
                    $bestMinutes = $minutes;
                    $best = [$entry, $exit];
                }
            }
        }

        return $best;
    }

    private function readBiometrieFile(string $path): array
    {
        try {
            $rows = $this->readWithSpreadsheet($path);
            return $rows ?: $this->readLegacyBiometrie($path);
        } catch (\Throwable) {
            return $this->readLegacyBiometrie($path);
        }
    }

    private function readWithSpreadsheet(string $path): array
    {
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $allRows = $sheet->toArray(null, true, true, true);
        $rows = $allRows;
        $headers = array_map(fn($v) => $this->normalizeHeader($v), array_shift($rows) ?: []);
        $data = [];

        foreach ($rows as $row) {
            $line = [];
            foreach ($headers as $column => $header) {
                $line[$header] = $row[$column] ?? null;
            }
            if (!empty($line['date_time'])) {
                $data[] = $line;
            }
        }

        return $data ?: $this->inferBiometrieRows($allRows);
    }

    private function inferBiometrieRows(array $rows): array
    {
        $data = [];

        foreach ($rows as $row) {
            $values = array_values($row);
            $dateIndex = null;
            $date = null;

            foreach ($values as $index => $value) {
                $parsed = $this->parseDateTime($value);
                if ($parsed) {
                    $dateIndex = $index;
                    $date = $parsed;
                    break;
                }
            }

            if ($dateIndex === null || !$date) {
                continue;
            }

            $timeValue = $values[$dateIndex + 1] ?? null;
            if ($timeValue && !str_contains((string) $values[$dateIndex], ':')) {
                $time = $this->parseTimeValue($timeValue);
                if ($time) {
                    $date = Carbon::parse($date->format('Y-m-d') . ' ' . $time);
                }
            }

            $data[] = [
                'department' => trim((string) ($values[0] ?? '')),
                'name' => trim((string) ($values[1] ?? '')),
                'no' => trim((string) ($values[2] ?? '')),
                'date_time' => $date->format('d/m/Y H:i:s'),
                'verify_code' => $values[$dateIndex + 2] ?? null,
            ];
        }

        return array_values(array_filter($data, fn($row) => !empty($row['name']) && !empty($row['date_time'])));
    }

    private function parseTimeValue($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $seconds = (int) round(((float) $value) * 86400);
            $hours = intdiv($seconds, 3600) % 24;
            $minutes = intdiv($seconds % 3600, 60);
            $secs = $seconds % 60;

            return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
        }

        $text = trim((string) $value);
        foreach (['H:i:s', 'H:i'] as $format) {
            try {
                return Carbon::createFromFormat($format, $text)->format('H:i:s');
            } catch (\Throwable) {
            }
        }

        if (preg_match('/(\d{1,2})[:h](\d{2})(?::(\d{2}))?/', $text, $match)) {
            return sprintf('%02d:%02d:%02d', (int) $match[1], (int) $match[2], (int) ($match[3] ?? 0));
        }

        return null;
    }

    private function readLegacyBiometrie(string $path): array
    {
        $content = file_get_contents($path);
        $cleanField = static function ($value): string {
            $clean = '';
            $value = (string) $value;

            for ($index = 0, $length = strlen($value); $index < $length; $index++) {
                $byte = ord($value[$index]);
                $clean .= ($byte >= 32 && $byte <= 126) || in_array($byte, [9, 10, 13], true)
                    ? $value[$index]
                    : ' ';
            }

            return trim(preg_replace('/\s+/', ' ', $clean));
        };

        $rows = [];
        foreach (explode('OUR COMPANY', $content) as $block) {
            $fields = explode('@', $block, 5);
            if (count($fields) < 4) {
                continue;
            }

            $name = $cleanField($fields[1]);
            $numberField = $cleanField($fields[2]);
            $dateField = $cleanField($fields[3]);

            if (!preg_match('/^(\d{1,10})(.*)$/', $numberField, $numberMatch)
                || !preg_match('/\d{2}\/\d{2}\/\d{4}\s+\d{2}:\d{2}:\d{2}/', $dateField, $dateMatch)
                || !preg_match('/[A-Za-z]/', $name)) {
                continue;
            }

            // The legacy format repeats a row marker after the name, number and date.
            $marker = trim($numberMatch[2]);
            if ($marker !== '' && str_ends_with($name, $marker)) {
                $name = trim(substr($name, 0, -strlen($marker)));
            }

            $rows[] = [
                'department' => 'OUR COMPANY',
                'name' => $name,
                'no' => $numberMatch[1],
                'date_time' => $dateMatch[0],
            ];
        }

        if (!empty($rows)) {
            return $rows;
        }

        preg_match_all('/@\s*([A-Z][A-Z ]{2,})\s+@\s*([A-Za-z .\'-]{2,}?)\s+@\s*(\d{1,10})\s+@\s*(\d{2}\/\d{2}\/\d{4}\s+\d{2}:\d{2}:\d{2})/is', $content, $matches, PREG_SET_ORDER);

        $rows = collect($matches)->map(fn($row) => [
            'department' => trim($row[1]),
            'name' => trim($row[2]),
            'no' => trim($row[3]),
            'date_time' => trim($row[4]),
        ])->all();

        if (!empty($rows)) {
            return $rows;
        }

        return [];
    }

    private function normalizeHeader($header): string
    {
        $header = strtolower(trim((string) $header));
        $header = str_replace(['/', ' ', '.', '-'], '_', $header);

        return match ($header) {
            'date_time', 'datetime' => 'date_time',
            'no' => 'no',
            'name' => 'name',
            'department' => 'department',
            'location_id' => 'location_id',
            'id_number' => 'id_number',
            'verifycode', 'verify_code' => 'verify_code',
            'cardno', 'card_no' => 'card_no',
            default => $header,
        };
    }

    private function parseDateTime($value): ?Carbon
    {
        if (!$value) {
            return null;
        }

        if (is_numeric($value)) {
            return Carbon::instance(ExcelDate::excelToDateTimeObject($value));
        }

        foreach (['d/m/Y H:i:s', 'd/m/Y H:i', 'Y-m-d H:i:s', 'Y-m-d H:i'] as $format) {
            try {
                return Carbon::createFromFormat($format, trim((string) $value));
            } catch (\Throwable) {
            }
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function reassocierPointages(BiometrieImport $import): void
    {
        $matcher = $this->personnelMatcher();
        foreach ($import->pointages()->get() as $pointage) {
            $personnelId = $matcher->match($pointage->nom_biometrie, $pointage->numero_biometrie)?->id;
            if ($pointage->id_personnel != $personnelId) {
                $pointage->update(['id_personnel' => $personnelId]);
            }
        }
        $import->unsetRelation('pointages');
        $import->update(['total_non_associees' => $import->pointages()->whereNull('id_personnel')->count()]);
    }

    private function personnelMatcher(): BiometriePersonnelMatcher
    {
        return new BiometriePersonnelMatcher(personnel::all(), DB::table('biometrie_personnel_mappings')->get());
    }

    private function heuresQuery(Request $request)
    {
        return HeureRealiseeEnseignant::with(['personnel', 'cours.programme.matiere', 'seance.plage'])
            ->whereHas('import', fn($q) => $q->pourEnseignants())
            ->when($request->id_biometrie_import, fn($q) => $q->where('id_biometrie_import', $request->id_biometrie_import))
            ->when($request->id_personnel, fn($q) => $q->where('id_personnel', $request->id_personnel))
            ->when($request->search_personnel, function ($q) use ($request) {
                $term = '%' . $request->search_personnel . '%';
                $q->whereHas('personnel', fn($personnel) => $personnel->where('nom', 'like', $term));
            })
            ->when($request->date_debut, fn($q) => $q->whereDate('date_seance', '>=', $request->date_debut))
            ->when($request->date_fin, fn($q) => $q->whereDate('date_seance', '<=', $request->date_fin))
            ->orderBy('id_personnel')
            ->orderByDesc('date_seance');
    }

    private function heuresRealiseesCollection(Request $request)
    {
        return $this->heuresQuery($request)
            ->get()
            ->unique(fn($heure) => implode('|', [
                $heure->id_biometrie_import,
                $heure->id_personnel,
                optional($heure->date_seance)->format('Y-m-d'),
                $heure->id_plage_horaire,
                $heure->id_programme_specialite,
            ]))
            ->values();
    }

    private function presencesPermanentsQuery(Request $request)
    {
        return PresencePermanent::with(['personnel', 'plage', 'emploi', 'entite', 'annee_academique'])
            ->when($request->id_biometrie_import, fn($q) => $q->where('id_biometrie_import', $request->id_biometrie_import))
            ->when($request->id_personnel, fn($q) => $q->where('id_personnel', $request->id_personnel))
            ->when($request->search_personnel, function ($q) use ($request) {
                $term = '%' . $request->search_personnel . '%';
                $q->whereHas('personnel', fn($personnel) => $personnel->where('nom', 'like', $term));
            })
            ->when($request->date_debut, fn($q) => $q->whereDate('date_presence', '>=', $request->date_debut))
            ->when($request->date_fin, fn($q) => $q->whereDate('date_presence', '<=', $request->date_fin))
            ->orderBy('id_personnel')
            ->orderByDesc('date_presence');
    }

    private function presencesPermanentsCollection(Request $request)
    {
        return $this->presencesPermanentsQuery($request)
            ->get()
            ->unique(fn($presence) => implode('|', [
                $presence->id_biometrie_import,
                $presence->id_personnel,
                optional($presence->date_presence)->format('Y-m-d'),
                $presence->id_plage_horaire ?: 'jour-paye',
                $presence->id_emploi_permanent,
            ]))
            ->values();
    }

    private function totauxParEnseignant($heures)
    {
        return (new DecompteHeuresEnseignant())->totaux($heures);
    }

    private function totauxPermanents($presences)
    {
        return $presences->groupBy('id_personnel')->map(function ($items) {
            $totauxMensuels = $items
                ->groupBy(fn($presence) => optional($presence->date_presence)->format('Y-m'))
                ->map(function ($monthItems) {
                    $presence = $monthItems->first();
                    $date = $presence->date_presence ? Carbon::parse($presence->date_presence) : now();
                    $salaire = $this->salairePermanentForDate(
                        $presence->id_personnel,
                        $date,
                        $presence->id_annee_academique,
                        $presence->id_entite
                    );
                    $salaireMensuel = (float) ($salaire->montant_mensuel ?? 0);
                    $tauxHoraire = $date->daysInMonth > 0
                        ? round(($salaireMensuel / $date->daysInMonth) / 8, 6)
                        : 0;
                    $heuresNonRealisees = $monthItems
                        ->where('statut', '!=', 'jour_paye')
                        ->sum(fn($item) => max(($item->duree_prevue ?? 0) - ($item->duree_realisee ?? 0), 0));

                    $penalite = round($heuresNonRealisees * $tauxHoraire, 2);

                    return [
                        'salaire' => $salaireMensuel,
                        'penalite' => min($penalite, $salaireMensuel),
                    ];
                });
            $salaireTheorique = $totauxMensuels->sum('salaire');
            $penaliteMontant = $totauxMensuels->sum('penalite');

            return [
                'personnel' => $items->first()->personnel->nom ?? '-',
                'jours' => $items->count(),
                'heures_prevues' => $items->sum('duree_prevue'),
                'heures_realisees' => $items->sum('duree_realisee'),
                'heures_non_comptabilisees' => max($items->sum('duree_prevue') - $items->sum('duree_realisee'), 0),
                'salaire_theorique' => $salaireTheorique,
                'montant_du' => max($salaireTheorique - $penaliteMontant, 0),
                'penalite_montant' => $penaliteMontant,
            ];
        })->values();
    }
}
