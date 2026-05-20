<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\annee_academique;
use App\Models\CoursEnseignant;
use App\Models\cycle;
use App\Models\entite;
use App\Models\filiere;
use App\Models\HeureRealiseeEnseignant;
use App\Models\niveau;
use App\Models\PlageHoraire;
use App\Models\ProgrammeSpecialite;
use App\Models\Salle;
use App\Models\scolarite;
use App\Models\SeanceCours;
use App\Models\specialite;
use App\Models\TauxHoraire;
use App\Models\personnel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CoursEnseignantController extends Controller
{
    private array $jours = [
        1 => 'Lundi',
        2 => 'Mardi',
        3 => 'Mercredi',
        4 => 'Jeudi',
        5 => 'Vendredi',
        6 => 'Samedi',
        7 => 'Dimanche',
    ];

    public function index(Request $request)
    {
        $query = CoursEnseignant::with([
            'personnel',
            'programme.matiere',
            'programme.specialite',
            'programme.cycle',
            'programme.filiere',
            'programme.niveau',
            'programme.annee_academique',
            'programme.entite',
            'taux_horaire',
            'salle',
            'seances.plage',
        ]);

        if ($request->id_personnel) {
            $query->where('id_personnel', $request->id_personnel);
        }

        if ($request->statut) {
            $query->where('statut', $request->statut);
        }

        if ($request->date_debut) {
            $query->whereDate('date_fin', '>=', $request->date_debut);
        }

        if ($request->date_fin) {
            $query->whereDate('date_debut', '<=', $request->date_fin);
        }

        $cours = $query->latest()->get();

        return view('Admin.EmploiTemps.cours_enseignants.index', [
            'title' => 'Cours par enseignant',
            'cours' => $cours,
            'personnels' => personnel::orderBy('nom')->get(),
        ]);
    }

    public function create()
    {
        return redirect()->route('cours_enseignants.contextes');
    }

    public function contextes()
    {
        $scolarites = scolarite::with(['cycles', 'filiere', 'niveaux', 'specialites', 'annee_academique'])
            ->orderBy('id_cycle')
            ->orderBy('id_filiere')
            ->orderBy('id_specialite')
            ->get()
            ->groupBy(fn($s) => ($s->cycles->nom_cycle ?? 'Cycle non defini') . ' / ' . ($s->filiere->nom_filiere ?? 'Filiere non definie'));

        return view('Admin.EmploiTemps.cours_enseignants.contextes', [
            'title' => 'Choisir la specialite',
            'scolaritesGrouped' => $scolarites,
        ]);
    }

    public function configure(Request $request, specialite $specialite)
    {
        $baseContext = $this->baseContextFromRequest($request);

        if (!$baseContext) {
            return redirect()
                ->route('cours_enseignants.contextes')
                ->with('error', 'Choisis d\'abord une specialite, un cycle et un niveau dans la liste.');
        }

        return view('Admin.EmploiTemps.cours_enseignants.configure', [
            'title' => 'Contexte du cours enseignant',
            'specialite' => $specialite->load('filiere'),
            'baseContext' => $baseContext,
            'baseContextLabels' => $this->baseContextLabels($baseContext),
            'annees' => annee_academique::orderBy('nom', 'desc')->get(),
            'entites' => entite::orderBy('nom_entite')->get(),
        ]);
    }

    public function createForContext(Request $request, specialite $specialite)
    {
        $context = $this->contextFromRequest($request);

        if (!$context) {
            $baseContext = $this->baseContextFromRequest($request);

            return redirect()->route('cours_enseignants.configure', array_merge([
                'specialite' => $specialite->id,
            ], $baseContext ?? []));
        }

        $programmes = ProgrammeSpecialite::with(['matiere', 'specialite', 'cycle', 'filiere', 'niveau', 'annee_academique', 'entite'])
            ->where('id_specialite', $specialite->id)
            ->where($context)
            ->orderBy('semestre')
            ->orderBy('id')
            ->get();

        return view('Admin.EmploiTemps.cours_enseignants.create', $this->formData([
            'title' => 'Programmer un cours enseignant',
            'cours' => null,
            'specialite' => $specialite,
            'context' => $context,
            'contextLabels' => $this->contextLabels($context),
            'programmes' => $programmes,
        ]));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $programme = ProgrammeSpecialite::with('matiere')->findOrFail($data['id_programme_specialite']);
        $this->assertNoTeacherConflict($data, $programme);

        DB::transaction(function () use ($data, $programme) {
            $cours = CoursEnseignant::create([
                'id_personnel' => $data['id_personnel'],
                'id_programme_specialite' => $programme->id,
                'id_taux_horaire' => $data['id_taux_horaire'] ?? null,
                'id_salle' => $data['id_salle'],
                'id_cycle' => $programme->id_cycle,
                'id_filiere' => $programme->id_filiere,
                'id_niveau' => $programme->id_niveau,
                'id_specialite' => $programme->id_specialite,
                'id_annee_academique' => $programme->id_annee_academique,
                'id_entite' => $programme->id_entite,
                'date_debut' => $data['date_debut'],
                'date_fin' => $data['date_fin'],
                'statut' => $data['statut'],
                'semestre' => $data['semestre'],
                'volume_horaire_prevu' => $programme->volume_horaire ?? 0,
                'id_user' => auth()->id() ?? 0,
            ]);

            $this->syncSeances($cours, $data['seances']);
        });

        return redirect()->route('cours_enseignants.create_context', [
            'specialite' => $programme->id_specialite,
            'id_cycle' => $programme->id_cycle,
            'id_filiere' => $programme->id_filiere,
            'id_niveau' => $programme->id_niveau,
            'id_annee_academique' => $programme->id_annee_academique,
            'id_entite' => $programme->id_entite,
        ])->with('success', 'Cours programme avec succes. Tu peux enregistrer un nouveau cours.');
    }

    public function edit(CoursEnseignant $cours_enseignant)
    {
        $cours_enseignant->load('seances');

        return view('Admin.EmploiTemps.cours_enseignants.create', $this->formData([
            'title' => 'Modifier le cours enseignant',
            'cours' => $cours_enseignant,
        ]));
    }

    public function update(Request $request, CoursEnseignant $cours_enseignant)
    {
        $data = $this->validatedData($request);
        $programme = ProgrammeSpecialite::with('matiere')->findOrFail($data['id_programme_specialite']);
        $this->assertNoTeacherConflict($data, $programme, $cours_enseignant->id);

        DB::transaction(function () use ($data, $programme, $cours_enseignant) {
            $cours_enseignant->update([
                'id_personnel' => $data['id_personnel'],
                'id_programme_specialite' => $programme->id,
                'id_taux_horaire' => $data['id_taux_horaire'] ?? null,
                'id_salle' => $data['id_salle'],
                'id_cycle' => $programme->id_cycle,
                'id_filiere' => $programme->id_filiere,
                'id_niveau' => $programme->id_niveau,
                'id_specialite' => $programme->id_specialite,
                'id_annee_academique' => $programme->id_annee_academique,
                'id_entite' => $programme->id_entite,
                'date_debut' => $data['date_debut'],
                'date_fin' => $data['date_fin'],
                'statut' => $data['statut'],
                'semestre' => $data['semestre'],
                'volume_horaire_prevu' => $programme->volume_horaire ?? 0,
            ]);

            $cours_enseignant->seances()->delete();
            $this->syncSeances($cours_enseignant, $data['seances']);
        });

        return redirect()->route('cours_enseignants.index')->with('success', 'Cours modifie avec succes.');
    }

    public function destroy(CoursEnseignant $cours_enseignant)
    {
        DB::transaction(function () use ($cours_enseignant) {
            $cours_enseignant->seances()->delete();
            $cours_enseignant->delete();
        });

        return back()->with('success', 'Cours supprime.');
    }

    public function export(Request $request)
    {
        $rows = CoursEnseignant::with(['personnel', 'programme.matiere', 'salle', 'seances.plage'])
            ->when($request->id_personnel, fn($q) => $q->where('id_personnel', $request->id_personnel))
            ->get()
            ->flatMap(function ($cours) {
                return $cours->seances->map(function ($seance) use ($cours) {
                    return [
                        $cours->personnel->nom ?? '',
                        $cours->programme->matiere->nom_matiere ?? '',
                        $this->jours[$seance->jour_semaine] ?? '',
                        optional($seance->plage)->heure_debut,
                        optional($seance->plage)->heure_fin,
                        $seance->duree_heures,
                        $cours->salle->nom_salle ?? '',
                        $cours->date_debut,
                        $cours->date_fin,
                    ];
                });
            });

        $headers = ['Enseignant', 'Matiere', 'Jour', 'Heure debut', 'Heure fin', 'Duree', 'Salle', 'Date debut', 'Date fin'];
        $content = implode(';', $headers) . "\n";
        foreach ($rows as $row) {
            $content .= implode(';', array_map(fn($value) => '"' . str_replace('"', '""', (string) $value) . '"', $row)) . "\n";
        }

        return response($content, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="emplois_temps_enseignants.csv"',
        ]);
    }

    public function emploiSpecialite(Request $request)
    {
        return view('Admin.EmploiTemps.cours_enseignants.emploi_specialite', $this->emploiSpecialiteData($request));
    }

    public function emploiSpecialitePdf(Request $request)
    {
        $data = $this->emploiSpecialiteData($request);
        $pdf = Pdf::loadView('Admin.EmploiTemps.cours_enseignants.emploi_specialite_pdf', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download('emploi_du_temps_specialite.pdf');
    }

    public function emploiEnseignant(Request $request)
    {
        return view('Admin.EmploiTemps.cours_enseignants.emploi_enseignant', $this->emploiEnseignantData($request));
    }

    public function emploiEnseignantPdf(Request $request)
    {
        $data = $this->emploiEnseignantData($request);
        $pdf = Pdf::loadView('Admin.EmploiTemps.cours_enseignants.emploi_enseignant_pdf', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download('emploi_du_temps_enseignant.pdf');
    }

    public function volumesSpecialite(Request $request)
    {
        $rows = $this->volumeRows($request);

        return view('Admin.EmploiTemps.cours_enseignants.volumes_specialite', array_merge($this->filterData(), [
            'title' => 'Suivi des volumes horaires',
            'rows' => $rows,
        ]));
    }

    public function volumesSpecialitePdf(Request $request)
    {
        $rows = $this->volumeRows($request);

        $pdf = Pdf::loadView('Admin.EmploiTemps.cours_enseignants.volumes_specialite_pdf', array_merge($this->filterData(), [
            'title' => 'Suivi des volumes horaires',
            'rows' => $rows,
        ]))->setPaper('a4', 'landscape');

        return $pdf->download('suivi_volumes_horaires.pdf');
    }

    private function emploiSpecialiteData(Request $request): array
    {
        $query = CoursEnseignant::with(['personnel', 'programme.matiere', 'programme.specialite', 'programme.cycle', 'programme.filiere', 'programme.niveau', 'programme.annee_academique', 'programme.entite', 'salle', 'seances.plage'])
            ->where('statut', 'actif')
            ->when($request->type_personnel, fn($q) => $q->whereHas('personnel', fn($sub) => $sub->where('type_personnel', $request->type_personnel)))
            ->when($request->id_cycle, fn($q) => $q->where('id_cycle', $request->id_cycle))
            ->when($request->id_filiere, fn($q) => $q->where('id_filiere', $request->id_filiere))
            ->when($request->id_niveau, fn($q) => $q->where('id_niveau', $request->id_niveau))
            ->when($request->id_specialite, fn($q) => $q->where('id_specialite', $request->id_specialite))
            ->when($request->semestre, fn($q) => $q->where('semestre', $request->semestre))
            ->when($request->id_annee_academique, fn($q) => $q->where('id_annee_academique', $request->id_annee_academique))
            ->when($request->id_entite, fn($q) => $q->where('id_entite', $request->id_entite))
            ->when($request->periode_journee, fn($q) => $q->whereHas('seances.plage', fn($sub) => $sub->where('periode_journee', $request->periode_journee)))
            ->when($request->format_plage, fn($q) => $q->whereHas('seances.plage', fn($sub) => $sub->where('format_plage', $request->format_plage)))
            ->when($request->date_debut, fn($q) => $q->whereDate('date_fin', '>=', $request->date_debut))
            ->when($request->date_fin, fn($q) => $q->whereDate('date_debut', '<=', $request->date_fin));

        $cours = $query->orderBy('id_specialite')->orderBy('date_debut')->get();

        $rows = $cours->flatMap(function ($item) use ($request) {
            return $item->seances
                ->when($request->periode_journee, fn($items) => $items->filter(fn($seance) => ($seance->plage->periode_journee ?? null) === $request->periode_journee))
                ->when($request->format_plage, fn($items) => $items->filter(fn($seance) => ($seance->plage->format_plage ?? null) === $request->format_plage))
                ->sortBy(['jour_semaine', 'id_plage_horaire'])
                ->map(function ($seance) use ($item) {
                return [
                    'jour_index' => $seance->jour_semaine,
                    'plage_id' => $seance->id_plage_horaire,
                    'specialite' => $item->programme->specialite->nom_specialite ?? '-',
                    'specialite_code' => $item->programme->specialite->code_specialite ?? null,
                    'cycle' => $item->programme->cycle->nom_cycle ?? '-',
                    'filiere' => $item->programme->filiere->nom_filiere ?? '-',
                    'niveau' => $item->programme->niveau->nom_niveau ?? '-',
                    'matiere' => $item->programme->matiere->nom_matiere ?? '-',
                    'code' => $item->programme->code_matiere_specialite,
                    'type_matiere' => $item->programme->type_matiere,
                    'semestre' => $this->displaySemestre($item->semestre ?: $item->programme->semestre),
                    'enseignant' => $item->personnel->nom ?? '-',
                    'salle' => $item->salle->nom_salle ?? '-',
                    'entite' => $item->programme->entite->nom_entite ?? '-',
                    'annee' => $item->programme->annee_academique->nom ?? '-',
                    'jour' => $this->jours[$seance->jour_semaine] ?? '-',
                    'plage' => optional($seance->plage)->libelle . ' (' . substr((string) optional($seance->plage)->heure_debut, 0, 5) . ' - ' . substr((string) optional($seance->plage)->heure_fin, 0, 5) . ')',
                    'volume' => $seance->duree_heures,
                    'volume_total' => (float) ($item->volume_horaire_prevu ?? 0),
                    'periode' => optional($item->date_debut)->format('d/m/Y') . ' - ' . optional($item->date_fin)->format('d/m/Y'),
                ];
            });
        });

        $matrix = [];
        foreach ($rows as $row) {
            $matrix[$row['jour_index']][$row['plage_id']][] = $row;
        }

        $selectedEntite = $request->id_entite ? entite::find($request->id_entite) : null;
        $selectedAnnee = $request->id_annee_academique ? annee_academique::find($request->id_annee_academique) : null;

        return array_merge($this->filterData(), [
            'title' => 'Emplois du temps de specialite',
            'rows' => $rows,
            'matrix' => $matrix,
            'jours' => $this->jours,
            'plages' => PlageHoraire::where('statut', 'actif')
                ->when($request->type_personnel, fn($q) => $q->whereIn('type_personnel', ['tous', $request->type_personnel]))
                ->when($request->periode_journee, fn($q) => $q->where('periode_journee', $request->periode_journee))
                ->when($request->format_plage, fn($q) => $q->where('format_plage', $request->format_plage))
                ->orderBy('ordre')
                ->orderBy('heure_debut')
                ->get(),
            'selectedEntite' => $selectedEntite,
            'selectedAnnee' => $selectedAnnee,
            'logoSrc' => $this->logoDataUri($selectedEntite),
            'hideContextInCells' => $this->hasContextFilter($request),
        ]);
    }

    private function volumeRows(Request $request)
    {
        $programmes = ProgrammeSpecialite::with(['matiere', 'specialite', 'cycle', 'filiere', 'niveau', 'annee_academique', 'entite'])
            ->when($request->id_cycle, fn($q) => $q->where('id_cycle', $request->id_cycle))
            ->when($request->id_filiere, fn($q) => $q->where('id_filiere', $request->id_filiere))
            ->when($request->id_niveau, fn($q) => $q->where('id_niveau', $request->id_niveau))
            ->when($request->id_specialite, fn($q) => $q->where('id_specialite', $request->id_specialite))
            ->when($request->semestre, fn($q) => $q->whereIn('semestre', $this->semestreValues($request->semestre)))
            ->when($request->id_annee_academique, fn($q) => $q->where('id_annee_academique', $request->id_annee_academique))
            ->when($request->id_entite, fn($q) => $q->where('id_entite', $request->id_entite))
            ->orderBy('id_cycle')
            ->orderBy('id_filiere')
            ->orderBy('id_niveau')
            ->orderBy('id_specialite')
            ->orderBy('semestre')
            ->get();

        return $programmes->map(function ($programme) use ($request) {
            $realise = $this->realisedHoursForProgramme($programme, $request);

            return [
                'specialite' => $programme->specialite->nom_specialite ?? '-',
                'cycle' => $programme->cycle->nom_cycle ?? '-',
                'filiere' => $programme->filiere->nom_filiere ?? '-',
                'niveau' => $programme->niveau->nom_niveau ?? '-',
                'annee' => $programme->annee_academique->nom ?? '-',
                'entite' => $programme->entite->nom_entite ?? '-',
                'matiere' => $programme->matiere->nom_matiere ?? '-',
                'code' => $programme->code_matiere_specialite,
                'semestre' => $this->displaySemestre($programme->semestre),
                'type' => $programme->type_matiere,
                'prevu' => (float) ($programme->volume_horaire ?? 0),
                'realise' => (float) $realise,
            ];
        });
    }

    private function realisedHoursForProgramme(ProgrammeSpecialite $programme, Request $request): float
    {
        $cours = CoursEnseignant::with('seances')
            ->where('id_programme_specialite', $programme->id)
            ->get();

        $heures = collect();

        foreach ($cours as $coursEnseignant) {
            $query = $this->heuresRealiseesForCoursQuery($coursEnseignant, $request);

            if ($programme->type_matiere !== 'transversale') {
                $heures = $heures->merge($query->where('id_cours_enseignant', $coursEnseignant->id)->get());
                continue;
            }

            $plageIds = $coursEnseignant->seances->pluck('id_plage_horaire')->filter()->unique()->values();

            $heures = $heures->merge(
                $query->where(function ($q) use ($programme, $coursEnseignant, $plageIds) {
                    $q->where('id_cours_enseignant', $coursEnseignant->id);

                    if ($plageIds->isNotEmpty()) {
                        $q->orWhere(function ($subQuery) use ($programme, $coursEnseignant, $plageIds) {
                            $subQuery->where('id_personnel', $coursEnseignant->id_personnel)
                            ->whereIn('id_plage_horaire', $plageIds)
                            ->whereHas('cours.programme', function ($programmeQuery) use ($programme) {
                                $programmeQuery->where('type_matiere', 'transversale')
                                    ->where('id_matiere', $programme->id_matiere);
                            });
                        });
                    }
                })->get()
            );
        }

        return (float) $heures->unique('id')->sum('duree_realisee');
    }

    private function heuresRealiseesForCoursQuery(CoursEnseignant $cours, Request $request)
    {
        return HeureRealiseeEnseignant::query()
            ->whereDate('date_seance', '>=', $cours->date_debut)
            ->whereDate('date_seance', '<=', $cours->date_fin)
            ->when($request->date_debut, fn($q) => $q->whereDate('date_seance', '>=', $request->date_debut))
            ->when($request->date_fin, fn($q) => $q->whereDate('date_seance', '<=', $request->date_fin))
            ->whereHas('import', function ($q) use ($cours) {
                $q->whereDate('date_debut', '<=', $cours->date_debut)
                    ->whereDate('date_fin', '>=', $cours->date_fin);
            });
    }

    private function emploiEnseignantData(Request $request): array
    {
        $query = CoursEnseignant::with([
            'personnel',
            'programme.matiere',
            'programme.specialite',
            'programme.cycle',
            'programme.filiere',
            'programme.niveau',
            'programme.annee_academique',
            'programme.entite',
            'salle',
            'seances.plage',
        ])
            ->where('statut', 'actif')
            ->when($request->id_personnel, fn($q) => $q->where('id_personnel', $request->id_personnel))
            ->when($request->id_cycle, fn($q) => $q->where('id_cycle', $request->id_cycle))
            ->when($request->id_filiere, fn($q) => $q->where('id_filiere', $request->id_filiere))
            ->when($request->id_niveau, fn($q) => $q->where('id_niveau', $request->id_niveau))
            ->when($request->id_specialite, fn($q) => $q->where('id_specialite', $request->id_specialite))
            ->when($request->semestre, fn($q) => $q->where('semestre', $request->semestre))
            ->when($request->id_entite, fn($q) => $q->where('id_entite', $request->id_entite))
            ->when($request->id_annee_academique, fn($q) => $q->where('id_annee_academique', $request->id_annee_academique))
            ->when($request->date_debut, fn($q) => $q->whereDate('date_fin', '>=', $request->date_debut))
            ->when($request->date_fin, fn($q) => $q->whereDate('date_debut', '<=', $request->date_fin));

        $cours = $query->orderBy('id_personnel')->orderBy('date_debut')->get();

        $rows = $cours->flatMap(function ($item) {
            return $item->seances->sortBy(['jour_semaine', 'id_plage_horaire'])->map(function ($seance) use ($item) {
                return [
                    'jour_index' => $seance->jour_semaine,
                    'plage_id' => $seance->id_plage_horaire,
                    'enseignant' => $item->personnel->nom ?? '-',
                    'matiere' => $item->programme->matiere->nom_matiere ?? '-',
                    'code' => $item->programme->code_matiere_specialite,
                    'semestre' => $this->displaySemestre($item->semestre ?: $item->programme->semestre),
                    'specialite' => $item->programme->specialite->nom_specialite ?? '-',
                    'specialite_code' => $item->programme->specialite->code_specialite ?? null,
                    'cycle' => $item->programme->cycle->nom_cycle ?? '-',
                    'filiere' => $item->programme->filiere->nom_filiere ?? '-',
                    'niveau' => $item->programme->niveau->nom_niveau ?? '-',
                    'type_matiere' => $item->programme->type_matiere,
                    'salle' => $item->salle->nom_salle ?? '-',
                    'entite' => $item->programme->entite->nom_entite ?? '-',
                    'annee' => $item->programme->annee_academique->nom ?? '-',
                    'volume' => $seance->duree_heures,
                    'volume_total' => (float) ($item->volume_horaire_prevu ?? 0),
                    'periode' => optional($item->date_debut)->format('d/m/Y') . ' - ' . optional($item->date_fin)->format('d/m/Y'),
                ];
            });
        });

        $matrix = [];
        foreach ($rows as $row) {
            $matrix[$row['jour_index']][$row['plage_id']][] = $row;
        }

        $selectedEntite = $request->id_entite ? entite::find($request->id_entite) : null;
        $selectedAnnee = $request->id_annee_academique ? annee_academique::find($request->id_annee_academique) : null;

        return array_merge($this->filterData(), [
            'title' => 'Emploi du temps enseignant',
            'rows' => $rows,
            'matrix' => $matrix,
            'jours' => $this->jours,
            'plages' => PlageHoraire::where('statut', 'actif')
                ->orderBy('ordre')
                ->orderBy('heure_debut')
                ->get(),
            'personnels' => personnel::orderBy('nom')->get(),
            'selectedEntite' => $selectedEntite,
            'selectedAnnee' => $selectedAnnee,
            'logoSrc' => $this->logoDataUri($selectedEntite),
            'hideContextInCells' => $this->hasContextFilter($request),
        ]);
    }

    private function formData(array $extra): array
    {
        $defaultProgrammes = ProgrammeSpecialite::with(['matiere', 'specialite', 'cycle', 'filiere', 'niveau', 'annee_academique', 'entite'])
            ->orderBy('id_specialite')
            ->orderBy('semestre')
            ->get();

        return array_merge([
            'personnels' => personnel::orderBy('nom')->get(),
            'programmes' => $defaultProgrammes,
            'tauxHoraires' => TauxHoraire::where('statut', 'actif')->orderByDesc('par_defaut')->orderBy('libelle')->get(),
            'salles' => Salle::where('statut', 'actif')->orderBy('nom_salle')->get(),
            'plages' => PlageHoraire::where('statut', 'actif')
                ->where('type_plage', 'cours')
                ->orderBy('ordre')
                ->orderBy('heure_debut')
                ->get(),
            'jours' => $this->jours,
            'contextLabels' => null,
        ], $extra);
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'id_personnel' => 'required|integer|exists:personnels,id',
            'id_programme_specialite' => 'required|integer|exists:programme_specialites,id',
            'id_taux_horaire' => 'nullable|integer|exists:taux_horaires,id',
            'id_salle' => 'required|integer|exists:salles,id',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'statut' => 'required|in:actif,inactif',
            'semestre' => 'required|integer|in:1,2',
            'seances' => 'required|array|min:1',
            'seances.*.jour_semaine' => 'required|integer|min:1|max:7',
            'seances.*.id_plage_horaire' => 'required|integer|exists:plage_horaires,id',
        ]);
    }

    private function assertNoTeacherConflict(array $data, ProgrammeSpecialite $programme, ?int $exceptId = null): void
    {
        foreach ($data['seances'] as $seance) {
            $conflicts = CoursEnseignant::with('programme')
                ->where('id_personnel', $data['id_personnel'])
                ->where('statut', 'actif')
                ->when($exceptId, fn($q) => $q->where('id', '<>', $exceptId))
                ->whereDate('date_debut', '<=', $data['date_fin'])
                ->whereDate('date_fin', '>=', $data['date_debut'])
                ->whereHas('seances', function ($q) use ($seance) {
                    $q->where('jour_semaine', $seance['jour_semaine'])
                        ->where('id_plage_horaire', $seance['id_plage_horaire']);
                })
                ->get();

            $hasBlockingConflict = $conflicts->contains(function ($cours) use ($programme) {
                $existingProgramme = $cours->programme;
                $currentIsTransversal = $programme->type_matiere === 'transversale';
                $existingIsTransversal = $existingProgramme?->type_matiere === 'transversale';
                $sameMatiere = (int) ($existingProgramme?->id_matiere ?? 0) === (int) ($programme->id_matiere ?? 0);
                $differentSpecialite = (int) ($existingProgramme?->id_specialite ?? 0) !== (int) ($programme->id_specialite ?? 0);

                return !($currentIsTransversal && $existingIsTransversal && $sameMatiere && $differentSpecialite);
            });

            if ($hasBlockingConflict) {
                throw ValidationException::withMessages([
                    'seances' => "Cet enseignant est deja occupe sur cette plage horaire. Seule la meme matiere transversale peut etre partagee sur plusieurs specialites.",
                ]);
            }
        }
    }

    private function syncSeances(CoursEnseignant $cours, array $seances): void
    {
        $saved = [];

        foreach ($seances as $seance) {
            $key = ($seance['jour_semaine'] ?? '') . '|' . ($seance['id_plage_horaire'] ?? '');
            if (isset($saved[$key])) {
                continue;
            }
            $saved[$key] = true;

            $plage = PlageHoraire::findOrFail($seance['id_plage_horaire']);
            $debut = Carbon::parse($plage->heure_debut);
            $fin = Carbon::parse($plage->heure_fin);

            SeanceCours::create([
                'id_cours_enseignant' => $cours->id,
                'id_plage_horaire' => $plage->id,
                'jour_semaine' => $seance['jour_semaine'],
                'duree_heures' => max($debut->diffInMinutes($fin) / 60, 0),
                'statut' => 'programme',
                'id_user' => auth()->id() ?? 0,
            ]);
        }
    }

    private function filterData(): array
    {
        return [
            'specialites' => specialite::orderBy('nom_specialite')->get(),
            'cycles' => cycle::orderBy('nom_cycle')->get(),
            'filieres' => filiere::orderBy('nom_filiere')->get(),
            'niveaux' => niveau::orderBy('nom_niveau')->get(),
            'semestres' => [1 => 'Semestre 1', 2 => 'Semestre 2'],
            'typesPersonnel' => ['vacataire' => 'Vacataire', 'permanent' => 'Permanent'],
            'periodesJournee' => ['jour' => 'Jour', 'soir' => 'Soir'],
            'formatsPlage' => ['bloc_4h' => 'Bloc 4h', 'bloc_8h' => 'Bloc 8h', 'bloc_6h' => 'Bloc 6h', 'bloc_5h' => 'Bloc 5h', 'mixte' => 'Mixte'],
            'annees' => annee_academique::orderBy('nom', 'desc')->get(),
            'entites' => entite::orderBy('nom_entite')->get(),
        ];
    }

    private function semestreValues($semestre): array
    {
        return [
            (string) $semestre,
            (int) $semestre,
            'S' . $semestre,
            's' . $semestre,
            'Semestre ' . $semestre,
            'semestre ' . $semestre,
        ];
    }

    private function displaySemestre($semestre): string
    {
        if (in_array((string) $semestre, ['1', 'S1', 's1', 'Semestre 1', 'semestre 1'], true)) {
            return 'Semestre 1';
        }

        if (in_array((string) $semestre, ['2', 'S2', 's2', 'Semestre 2', 'semestre 2'], true)) {
            return 'Semestre 2';
        }

        return $semestre ? (string) $semestre : '-';
    }

    private function logoDataUri(?entite $entite): ?string
    {
        $logoPath = $entite->logo ?? null;
        $fallback = 'uploads/images/1759420569_logo.jpg';
        $relativePath = $logoPath ?: $fallback;
        $fullPath = public_path($relativePath);

        if (!$relativePath || !file_exists($fullPath)) {
            return null;
        }

        $mime = mime_content_type($fullPath) ?: 'image/png';

        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fullPath));
    }

    private function hasContextFilter(Request $request): bool
    {
        return $request->filled('id_cycle')
            || $request->filled('id_filiere')
            || $request->filled('id_niveau')
            || $request->filled('id_specialite');
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
