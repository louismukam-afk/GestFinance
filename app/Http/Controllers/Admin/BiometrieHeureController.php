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
use App\Models\personnel;
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
        $imports = BiometrieImport::where(function ($query) {
            $query->whereNull('type_import')
                ->orWhereIn('type_import', ['cours', 'manuel']);
        })->latest()->get();

        $heures = $this->heuresRealiseesCollection($request);
        $totaux = $this->totauxParEnseignant($heures);

        $unmatchedPointages = BiometriePointage::whereNull('id_personnel')
            ->select('nom_biometrie', 'numero_biometrie')
            ->whereNotNull('nom_biometrie')
            ->groupBy('nom_biometrie', 'numero_biometrie')
            ->orderBy('nom_biometrie')
            ->get();

        $biometrieIdentites = BiometriePointage::query()
            ->select('nom_biometrie', 'numero_biometrie', DB::raw('COUNT(*) as total'))
            ->whereNotNull('nom_biometrie')
            ->groupBy('nom_biometrie', 'numero_biometrie')
            ->orderBy('nom_biometrie')
            ->get();

        return view('Admin.EmploiTemps.biometrie.index', [
            'title' => 'Decompte des heures realisees',
            'imports' => $imports,
            'heures' => $heures,
            'totaux' => $totaux,
            'personnels' => personnel::orderBy('nom')->get(),
            'unmatchedPointages' => $unmatchedPointages,
            'biometrieIdentites' => $biometrieIdentites,
        ]);
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new HeuresRealiseesExport($this->heuresRealiseesCollection($request)), 'heures_realisees_enseignants.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $heures = $this->heuresRealiseesCollection($request);
        $pdf = Pdf::loadView('Admin.EmploiTemps.biometrie.pdf', [
            'title' => 'Decompte des heures realisees',
            'heures' => $heures,
            'totaux' => $this->totauxParEnseignant($heures),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('heures_realisees_enseignants.pdf');
    }

    public function permanentIndex(Request $request)
    {
        $imports = BiometrieImport::where('type_import', 'permanent')->latest()->get();
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
                    if ($date->dayOfWeekIso === (int) $emploi->jour_semaine && !$this->isPaidNonWorkingDay($date)) {
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
            'statut' => 'importe',
            'total_consolidees' => 0,
        ]);

        return back()->with('success', "{$deleted} consolidation(s) permanente(s) supprimee(s).");
    }

    public function manualCreate()
    {
        $selectedImportId = request('id_biometrie_import');
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
            ->where('statut', 'actif')
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
            'imports' => BiometrieImport::where('statut', 'manuel')->latest()->get(),
            'selectedImport' => $selectedImportId ? BiometrieImport::find($selectedImportId) : null,
            'heuresManuelles' => HeureRealiseeEnseignant::with(['personnel', 'cours.programme.matiere', 'seance.plage', 'import'])
                ->whereHas('import', fn($q) => $q->where('statut', 'manuel'))
                ->when($selectedImportId, fn($q) => $q->where('id_biometrie_import', $selectedImportId))
                ->latest()
                ->get(),
            'cours' => $cours,
            'coursData' => $coursData,
        ]);
    }

    public function manualStore(Request $request)
    {
        $data = $request->validate([
            'id_biometrie_import' => 'nullable|integer|exists:biometrie_imports,id',
            'libelle' => 'nullable|string|max:255',
            'date_debut_import' => 'required_without:id_biometrie_import|nullable|date',
            'date_fin_import' => 'required_without:id_biometrie_import|nullable|date|after_or_equal:date_debut_import',
            'id_cours_enseignant' => 'required|integer|exists:cours_enseignants,id',
            'id_seance_cours' => 'required|integer|exists:seance_cours,id',
            'date_seance' => 'required|date',
            'heure_debut_reelle' => 'required|date_format:H:i',
            'heure_fin_reelle' => 'required|date_format:H:i|after:heure_debut_reelle',
            'observation' => 'nullable|string|max:1000',
        ]);

        $cours = CoursEnseignant::with(['programme', 'taux_horaire'])->findOrFail($data['id_cours_enseignant']);
        $seance = SeanceCours::with('plage')
            ->where('id_cours_enseignant', $cours->id)
            ->findOrFail($data['id_seance_cours']);

        $import = !empty($data['id_biometrie_import'])
            ? BiometrieImport::findOrFail($data['id_biometrie_import'])
            : BiometrieImport::create([
                'libelle' => $data['libelle'] ?: 'Saisie manuelle du ' . now()->format('d/m/Y H:i'),
                'date_debut' => $data['date_debut_import'],
                'date_fin' => $data['date_fin_import'],
                'statut' => 'manuel',
                'type_import' => 'manuel',
                'observations' => 'Import cree depuis la saisie manuelle des heures realisees.',
                'id_user' => auth()->id() ?? 0,
            ]);

        $dateSeance = Carbon::parse($data['date_seance']);
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

        $plage = $seance->plage;
        $plannedStart = Carbon::parse($dateSeance->format('Y-m-d') . ' ' . $plage->heure_debut);
        $plannedEnd = Carbon::parse($dateSeance->format('Y-m-d') . ' ' . $plage->heure_fin);
        $realStartInput = Carbon::parse($dateSeance->format('Y-m-d') . ' ' . $data['heure_debut_reelle']);
        $realEndInput = Carbon::parse($dateSeance->format('Y-m-d') . ' ' . $data['heure_fin_reelle']);

        $realStart = $realStartInput->greaterThan($plannedStart) ? $realStartInput : $plannedStart;
        $realEnd = $realEndInput->lessThan($plannedEnd) ? $realEndInput : $plannedEnd;
        $plannedDuration = round($plannedStart->diffInMinutes($plannedEnd) / 60, 2);
        $duration = $realEnd->greaterThan($realStart)
            ? min($plannedDuration, round($realStart->diffInMinutes($realEnd) / 60, 2))
            : 0;

        $taux = $cours->taux_horaire
            ?: TauxHoraire::where('statut', 'actif')->where('par_defaut', true)->first()
            ?: TauxHoraire::where('statut', 'actif')->orderByDesc('id')->first();
        $montantTaux = (float) ($taux->montant ?? 0);
        $missing = round($plannedDuration - $duration, 2);
        $observation = $data['observation'] ?? null;
        if ($missing > 0) {
            $observation = trim(($observation ? $observation . ' ' : '') . 'Saisie manuelle partielle : ' . number_format($missing, 2, ',', ' ') . ' h non comptabilisee(s).');
        }

        $heureRealisee = HeureRealiseeEnseignant::updateOrCreate(
            [
                'id_biometrie_import' => $import->id,
                'id_seance_cours' => $seance->id,
                'date_seance' => $dateSeance->toDateString(),
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
                'heure_debut_reelle' => $realStart->format('H:i:s'),
                'heure_fin_reelle' => $realEnd->format('H:i:s'),
                'duree_prevue' => $plannedDuration,
                'duree_realisee' => $duration,
                'montant_taux' => $montantTaux,
                'montant_total' => round($duration * $montantTaux, 2),
                'statut' => $duration > 0 ? 'realise' : 'non_realise',
                'observation' => $observation,
                'id_user' => auth()->id() ?? 0,
            ]
        );

        $import->update([
            'total_consolidees' => $import->heures_realisees()->count(),
            'total_lignes' => $import->heures_realisees()->count(),
        ]);

        return redirect()
            ->route('biometrie_heures.manual.create', ['id_biometrie_import' => $import->id])
            ->with('success', 'Heure realisee enregistree manuellement.');
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

        $rows = $this->readBiometrieFile(public_path($path));
        $personnels = personnel::all();
        $created = 0;
        $unmatched = 0;

        DB::transaction(function () use ($rows, $import, $personnels, &$created, &$unmatched) {
            foreach ($rows as $row) {
                $date = $this->parseDateTime($row['date_time'] ?? null);
                if (!$date) {
                    continue;
                }

                $personnel = $this->matchPersonnel($row['name'] ?? '', $personnels);
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

        $import->update([
            'total_lignes' => $created,
            'total_non_associees' => $unmatched,
        ]);

        return redirect()->route('biometrie_heures.index')
            ->with('success', "{$created} pointage(s) importe(s). {$unmatched} non associe(s) a un enseignant.");
    }

    public function consolider(BiometrieImport $import)
    {
        if ($import->statut === 'manuel') {
            return back()->withErrors(['import' => 'Cet import est manuel : utilise la saisie manuelle pour ajouter ou modifier les heures.']);
        }

        $this->reassocierPointages($import);
        HeureRealiseeEnseignant::where('id_biometrie_import', $import->id)->delete();

        $start = Carbon::parse($import->date_debut)->startOfDay();
        $end = Carbon::parse($import->date_fin)->endOfDay();
        $defaultTaux = TauxHoraire::where('statut', 'actif')->where('par_defaut', true)->first()
            ?: TauxHoraire::where('statut', 'actif')->orderByDesc('id')->first();

        $cours = CoursEnseignant::with(['personnel', 'programme', 'seances.plage', 'taux_horaire'])
            ->where('statut', 'actif')
            ->whereDate('date_debut', '>=', $start->toDateString())
            ->whereDate('date_fin', '<=', $end->toDateString())
            ->get();

        $count = 0;

        $processedSeances = [];

        DB::transaction(function () use ($cours, $import, $start, $end, $defaultTaux, &$count, &$processedSeances) {
            foreach ($cours as $coursEnseignant) {
                $periodeStart = max(Carbon::parse($coursEnseignant->date_debut)->startOfDay(), $start);
                $periodeEnd = min(Carbon::parse($coursEnseignant->date_fin)->endOfDay(), $end);

                foreach ($coursEnseignant->seances as $seance) {
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

        return back()->with('success', "{$count} plage(s) consolidee(s).");
    }

    public function clearConsolidation(BiometrieImport $import)
    {
        $deleted = HeureRealiseeEnseignant::where('id_biometrie_import', $import->id)->delete();
        $import->update([
            'statut' => $import->statut === 'manuel' ? 'manuel' : 'importe',
            'total_consolidees' => 0,
            'total_lignes' => $import->statut === 'manuel' ? 0 : $import->total_lignes,
        ]);

        return back()->with('success', "{$deleted} consolidation(s) supprimee(s).");
    }

    public function destroy(BiometrieImport $import)
    {
        $import->delete();

        return back()->with('success', 'Import biometrie supprime.');
    }

    public function storeMapping(Request $request)
    {
        $data = $request->validate([
            'id_personnel' => 'required|integer|exists:personnels,id',
            'nom_biometrie' => 'nullable|string|max:255',
            'numero_biometrie' => 'nullable|string|max:255',
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
        $personnels = personnel::all();
        $created = 0;
        $unmatched = 0;

        DB::transaction(function () use ($rows, $import, $personnels, &$created, &$unmatched) {
            foreach ($rows as $row) {
                $date = $this->parseDateTime($row['date_time'] ?? null);
                if (!$date) {
                    continue;
                }

                $personnel = $this->matchPersonnel($row['name'] ?? '', $personnels);
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

        return [$created, $unmatched];
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
            $realStart = Carbon::parse($pointages->first()->date_heure_pointage);
            $observation = 'Pointage unique detecte : entree ou sortie manquante.';
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

            $plage = $this->defaultPermanentPlageForPersonnel($personnel);
            if (!$plage) {
                continue;
            }

            $date = max(Carbon::parse($salaire->date_debut)->startOfDay(), $start);
            $periodeEnd = $salaire->date_fin
                ? min(Carbon::parse($salaire->date_fin)->endOfDay(), $end)
                : $end->copy();

            while ($date->lte($periodeEnd)) {
                $dateKey = $date->toDateString();
                if (!$this->isPaidNonWorkingDay($date) && empty($processedDatesByPersonnel[$personnel->id][$dateKey])) {
                    $emploi = new EmploiPermanent([
                        'id_personnel' => $personnel->id,
                        'id_plage_horaire' => $plage->id,
                        'id_annee_academique' => $salaire->id_annee_academique,
                        'id_entite' => $salaire->id_entite,
                        'jour_semaine' => $date->dayOfWeekIso,
                        'date_debut' => $dateKey,
                        'date_fin' => $dateKey,
                        'statut' => 'actif',
                    ]);
                    $emploi->id = -1000000 - (int) $personnel->id;
                    $emploi->setRelation('personnel', $personnel);
                    $emploi->setRelation('plage', $plage);

                    $this->consoliderPresencePermanent($import, $emploi, $date);
                    $processedDatesByPersonnel[$personnel->id][$dateKey] = true;
                    $count++;
                }

                $date->addDay();
            }
        }

        return $count;
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
                            'observation' => $this->paidDayLabel($date),
                            'id_user' => auth()->id() ?? 0,
                        ]
                    );
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
            2026 => ['2026-03-20', '2026-05-27', '2026-08-25'],
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

        if (!$hasPointageOnDay || !$hasPointageInPlage) {
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
            ->where(function ($q) use ($cours) {
                $q->where('id_personnel', $cours->id_personnel)
                    ->orWhereIn('numero_biometrie', $this->mappedBiometrieNumbers($cours->id_personnel))
                    ->orWhereIn('nom_biometrie', $this->mappedBiometrieNames($cours->id_personnel));
            })
            ->whereBetween('date_heure_pointage', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])
            ->orderBy('date_heure_pointage')
            ->get();
    }

    private function dailyPointagesForPersonnel(BiometrieImport $import, int $personnelId, Carbon $date)
    {
        return BiometriePointage::where('id_biometrie_import', $import->id)
            ->where(function ($q) use ($personnelId) {
                $q->where('id_personnel', $personnelId)
                    ->orWhereIn('numero_biometrie', $this->mappedBiometrieNumbers($personnelId))
                    ->orWhereIn('nom_biometrie', $this->mappedBiometrieNames($personnelId));
            })
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
            return $this->readWithSpreadsheet($path);
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
        $text = preg_replace('/[^\x20-\x7E\r\n\t\/:-]/', ' ', $content);
        preg_match_all('/@\s*([A-Z][A-Z ]{2,})\s+@\s*([A-Za-z .\'-]{2,}?)\s+@\s*(\d{1,10})\s+@\s*(\d{2}\/\d{2}\/\d{4}\s+\d{2}:\d{2}:\d{2})/s', $text, $matches, PREG_SET_ORDER);

        return collect($matches)->map(fn($row) => [
            'department' => trim($row[1]),
            'name' => trim($row[2]),
            'no' => trim($row[3]),
            'date_time' => trim($row[4]),
        ])->all();
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

    private function matchPersonnel(string $name, $personnels): ?personnel
    {
        $needle = $this->normalizeName($name);
        if ($needle === '') {
            return null;
        }

        return $personnels->first(function ($personnel) use ($needle) {
            $haystack = $this->normalizeName($personnel->nom);

            return $haystack === $needle
                || str_contains($haystack, $needle)
                || str_contains($needle, $haystack);
        });
    }

    private function normalizeName(string $name): string
    {
        $name = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name) ?: $name);
        $name = preg_replace('/[^a-z0-9]/', '', $name);

        return trim($name ?? '');
    }

    private function reassocierPointages(BiometrieImport $import): void
    {
        $personnels = personnel::all();
        $mappings = DB::table('biometrie_personnel_mappings')->get();

        foreach ($import->pointages as $pointage) {
            if ($pointage->id_personnel) {
                continue;
            }

            $mapping = $mappings->first(function ($mapping) use ($pointage) {
                return ($mapping->numero_biometrie && $mapping->numero_biometrie === $pointage->numero_biometrie)
                    || ($mapping->nom_biometrie && $mapping->nom_biometrie === $pointage->nom_biometrie);
            });

            $personnelId = $mapping->id_personnel ?? $this->matchPersonnel($pointage->nom_biometrie ?? '', $personnels)?->id;

            if ($personnelId) {
                $pointage->update(['id_personnel' => $personnelId]);
            }
        }
    }

    private function mappedBiometrieNumbers(int $personnelId): array
    {
        return DB::table('biometrie_personnel_mappings')
            ->where('id_personnel', $personnelId)
            ->whereNotNull('numero_biometrie')
            ->pluck('numero_biometrie')
            ->all();
    }

    private function mappedBiometrieNames(int $personnelId): array
    {
        return DB::table('biometrie_personnel_mappings')
            ->where('id_personnel', $personnelId)
            ->whereNotNull('nom_biometrie')
            ->pluck('nom_biometrie')
            ->all();
    }

    private function heuresQuery(Request $request)
    {
        return HeureRealiseeEnseignant::with(['personnel', 'cours.programme.matiere', 'seance.plage'])
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
        return $heures->groupBy('id_personnel')->map(function ($items) {
            return [
                'enseignant' => $items->first()->personnel->nom ?? '-',
                'heures_prevues' => $items->sum('duree_prevue'),
                'heures_realisees' => $items->sum('duree_realisee'),
                'heures_non_comptabilisees' => max($items->sum('duree_prevue') - $items->sum('duree_realisee'), 0),
                'montant_total' => $items->sum('montant_total'),
            ];
        })->values();
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
