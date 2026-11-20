<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{banque, entite, BiometrieImport, HeureRealiseeEnseignant, LigneVirementVacataire, VirementVacataire};
use App\Services\PaieImportVacataire;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VirementVacataireController extends Controller
{
    public function index()
    {
        return view('Admin.EmploiTemps.virements.index', [
            'title' => 'Etats de virement des enseignants',
            'imports' => BiometrieImport::pourEnseignants()
                ->whereHas('heures_realisees')->latest('id')->get(),
        ]);
    }

    public function historique(Request $request)
    {
        $request->validate([
            'date_debut' => 'nullable|date_format:Y-m-d',
            'date_fin' => 'nullable|date_format:Y-m-d'.($request->filled('date_debut') ? '|after_or_equal:date_debut' : ''),
            'id_entite' => 'nullable|integer|min:1', 'id_banque' => 'nullable|integer|min:1',
            'id_import' => 'nullable|integer|min:1', 'recherche' => 'nullable|string|max:255',
            'nature' => 'nullable|in:initial,requete', 'mois_requete' => 'nullable|date_format:Y-m',
        ]);
        $query = VirementVacataire::query()
            ->when($request->date_debut, fn($q, $v) => $q->whereDate('date_ordre', '>=', $v))
            ->when($request->date_fin, fn($q, $v) => $q->whereDate('date_ordre', '<=', $v))
            ->when($request->id_entite, fn($q, $v) => $q->where('id_entite', $v))
            ->when($request->id_banque, fn($q, $v) => $q->where('id_banque', $v))
            ->when($request->id_import, fn($q, $v) => $q->where(fn($s) => $s->where('id_biometrie_import', $v)->orWhere('id_import_parent', $v)))
            ->when($request->nature, fn($q, $v) => $q->where('nature', $v))
            ->when($request->mois_requete, fn($q, $v) => $q->whereDate('mois_requete', $v.'-01'))
            ->when($request->recherche, fn($q, $v) => $q->where(fn($s) => $s->where('motif', 'like', '%'.$v.'%')
                ->orWhereHas('lignes', fn($l) => $l->where('nom', 'like', '%'.$v.'%'))));
        $totaux = LigneVirementVacataire::whereIn('id_virement', (clone $query)->select('id'))
            ->selectRaw('COALESCE(SUM(net_a_payer),0) as net, COALESCE(SUM(impot),0) as impot, COALESCE(SUM(frais_transfert),0) as frais')->first();
        $etats = $query->withSum('lignes', 'net_a_payer')->withCount('lignes')->latest('id')->paginate(25)->withQueryString();

        return view('Admin.EmploiTemps.virements.historique', [
            'title' => 'Historique des ordres de virement', 'etats' => $etats, 'totaux' => $totaux,
            'entites' => VirementVacataire::select('id_entite', 'entite_nom')->distinct()->orderBy('entite_nom')->get(),
            'banques' => VirementVacataire::select('id_banque', 'banque_nom')->distinct()->orderBy('banque_nom')->get(),
        ]);
    }

    public function create(Request $request, BiometrieImport $import)
    {
        $request->validate([
            'id_entite' => 'nullable|integer|exists:entites,id',
            'id_banque' => 'nullable|integer|exists:banques,id',
            'inclure_autres' => 'nullable|boolean',
            'inclure_banque' => 'nullable|boolean',
            'inclure_sans_banque' => 'nullable|boolean',
        ]);
        $toutes = $this->lignesImport($import);
        $deja = LigneVirementVacataire::where('id_entite', $request->id_entite)
            ->where('id_biometrie_import', $import->id)
            ->pluck('id_personnel')->all();
        $lignes = $this->filtrer($toutes, $request)->whereNotIn('id_personnel', $deja)->values();
        $eligibles = $toutes->where('id_entite', (int) $request->id_entite)->where('net_avant_frais', '>', 0);

        return view('Admin.EmploiTemps.virements.create', [
            'title' => 'Preparer un etat de virement - import #'.$import->id,
            'import' => $import, 'lignes' => $lignes,
            'suivi' => ['total' => $eligibles->count(), 'valides' => $eligibles->whereIn('id_personnel', $deja)->count(),
                'restants' => $eligibles->whereNotIn('id_personnel', $deja)->count()],
            'empreinte' => $this->empreinte($lignes),
            'entites' => entite::whereIn('id', $toutes->pluck('id_entite'))->orderBy('nom_entite')->get(),
            'banques' => banque::orderBy('nom_banque')->get(),
            'entiteSelectionnee' => entite::find($request->id_entite),
            'modeleDefaut' => preg_match('/cef|centre de formation/i', entite::find($request->id_entite)?->nom_entite ?? '') ? 'cef' : 'ish',
        ]);
    }

    public function store(Request $request, BiometrieImport $import)
    {
        $data = $request->validate([
            'id_entite' => 'required|integer|exists:entites,id',
            'id_banque' => 'required|integer|exists:banques,id',
            'inclure_autres' => 'nullable|boolean',
            'inclure_banque' => 'nullable|boolean',
            'inclure_sans_banque' => 'nullable|boolean',
            'personnels' => 'required|array|min:1|max:2000',
            'personnels.*' => 'required|integer|distinct',
            'transfert' => 'nullable|array|max:2000', 'transfert.*' => 'boolean',
            'frais' => 'nullable|array|max:2000', 'frais.*' => ['nullable', 'numeric', 'min:0', 'max:10000000', 'regex:/^\d+(\.\d{1,2})?$/'],
            'comptes' => 'nullable|array|max:2000', 'comptes.*' => 'nullable|string|max:255',
            'sans_compte' => 'nullable|array|max:2000', 'sans_compte.*' => 'boolean',
            'modele' => 'required|in:ish,cef', 'agence' => 'required|string|max:255',
            'compte_debit' => 'required|string|max:255', 'motif' => 'required|string|max:3000',
            'date_ordre' => 'required|date_format:Y-m-d', 'lieu' => 'required|string|max:255',
            'signataire' => 'required|string|max:255', 'fonction_signataire' => 'required|string|max:255',
            'empreinte' => 'required|string|size:64', 'confirmation' => 'accepted',
        ]);

        $etat = DB::transaction(function () use ($request, $import, $data) {
            // Serialise les validations d'une entite et empeche les paiements en double.
            $entite = entite::whereKey($data['id_entite'])->lockForUpdate()->firstOrFail();
            $source = BiometrieImport::whereKey($import->id)->lockForUpdate()->firstOrFail();
            if ($source->estRequete()) { BiometrieImport::whereKey($source->id_import_parent)->lockForUpdate()->firstOrFail(); }
            $deja = LigneVirementVacataire::where('id_entite', $entite->id)
                ->where('id_biometrie_import', $source->id)->pluck('id_personnel')->all();
            if (count(array_intersect($data['personnels'], $deja)) > 0) {
                $this->erreur('personnels', 'Un enseignant selectionne figure deja dans un ordre valide pour cet import et cette entite.');
            }
            $lignes = $this->filtrer($this->lignesImport($source), $request)->whereNotIn('id_personnel', $deja)->values()->keyBy('id_personnel');
            if (!hash_equals($this->empreinte($lignes->values()), $data['empreinte'])) {
                $this->erreur('personnels', 'Les donnees ont change. Rechargez la preparation avant de valider.');
            }
            $selection = collect();
            foreach ($data['personnels'] as $id) {
                if (!$lignes->has($id)) {
                    $this->erreur('personnels', 'Un enseignant ne correspond pas a cette banque ou a cette entite.');
                }
                $ligne = $lignes[$id];
                if ($ligne['reprise_a_verifier'] ?? false) {
                    $this->erreur('personnels', 'Cette correction implique une diminution de droits dans une entite. Verification financiere requise avant un complement.');
                }
                if ($ligne['net_avant_frais'] <= 0) {
                    $this->erreur('personnels', 'Un enseignant selectionne ne dispose pas de net positif.');
                }
                $compte = trim((string) ($data['comptes'][$id] ?? $ligne['compte']));
                $sansCompte = (bool) ($data['sans_compte'][$id] ?? false);
                if ($compte === '' && !$sansCompte) {
                    $this->erreur('comptes.'.$id, 'Renseignez le compte de '.$ligne['nom'].' ou confirmez explicitement son absence.');
                }
                $transfert = (bool) ($data['transfert'][$id] ?? false);
                $frais = $transfert ? (float) ($data['frais'][$id] ?? 3500) : 0;
                if ($frais >= $ligne['net_avant_frais']) {
                    $this->erreur('frais.'.$id, 'Les frais de '.$ligne['nom'].' doivent etre inferieurs au net a payer.');
                }
                $selection->push(collect($ligne)->only(['id_personnel', 'nom', 'banque_personnel', 'brut_fiscal', 'impot', 'net_avant_frais', 'details'])->all() + [
                    'id_biometrie_import' => $source->id, 'id_entite' => $entite->id,
                    'compte' => $compte ?: null, 'sans_compte_confirme' => $compte === '' && $sansCompte,
                    'frais_transfert' => $frais, 'net_a_payer' => round($ligne['net_avant_frais'] - $frais, 2),
                ]);
            }
            $doublonAutreImport = LigneVirementVacataire::whereIn('id_personnel', $data['personnels'])->where('id_entite', $entite->id)
                ->where('id_biometrie_import', '!=', $source->id)
                ->whereHas('virement', function ($q) use ($source) {
                    $q->whereDate('date_debut', '<=', $source->date_fin)->whereDate('date_fin', '>=', $source->date_debut);
                    if ($source->estRequete()) {
                        $q->where('id_biometrie_import', '!=', $source->id_import_parent)
                            ->where(fn($s) => $s->whereNull('id_import_parent')->orWhere('id_import_parent', '!=', $source->id_import_parent));
                    }
                })->exists();
            if ($doublonAutreImport) {
                $this->erreur('personnels', 'Un enseignant a deja un ordre valide pour cette entite sur un autre import de periode chevauchante.');
            }
            $banque = banque::findOrFail($data['id_banque']);
            $meta = $source->estRequete() ? ['nature' => 'requete', 'id_import_parent' => $source->id_import_parent, 'mois_requete' => $source->mois_requete] : [];
            $etat = VirementVacataire::create(collect($data)->only([
                'id_entite', 'id_banque', 'modele', 'agence', 'compte_debit', 'motif', 'date_ordre', 'lieu', 'signataire', 'fonction_signataire',
            ])->all() + [
                'id_biometrie_import' => $source->id, 'entite_nom' => $entite->nom_entite,
                'banque_nom' => $banque->nom_banque, 'date_debut' => $source->date_debut,
                'date_fin' => $source->date_fin, 'id_user' => $request->user()?->id, 'valide_at' => now(),
            ] + $meta);
            $etat->lignes()->createMany($selection->all());

            return $etat;
        });

        return redirect()->route('virements_vacataires.show', $etat)->with('success', 'Etat de virement valide et archive.');
    }

    public function show(VirementVacataire $virement)
    {
        return view('Admin.EmploiTemps.virements.show', ['title' => 'Etat de virement #'.$virement->id, 'etat' => $virement->load('lignes')]);
    }

    public function pdf(VirementVacataire $virement)
    {
        $total = (float) $virement->lignes()->sum('net_a_payer');

        return Pdf::loadView('Admin.EmploiTemps.virements.pdf', [
            'etat' => $virement->load('lignes'), 'total' => $total,
            'totalLettres' => (new \App\Services\MontantEnLettres())->francs($total),
        ])->setPaper('a4')->stream('ordre_virement_'.$virement->id.'.pdf');
    }

    private function lignesImport(BiometrieImport $import)
    {
        abort_unless($import->concerneEnseignants(), 404);
        $heures = HeureRealiseeEnseignant::where('id_biometrie_import', $import->id)
            ->with(['personnel.taux_impot', 'personnel.banque'])->orderBy('id')->get();
        abort_if($heures->isEmpty(), 422, 'Aucune heure consolidee pour cet import.');
        abort_if($heures->contains(fn($h) => !$h->personnel), 422, 'Un enseignant consolide est introuvable.');

        return (new PaieImportVacataire())->lignes($import, $heures);
    }

    private function filtrer($lignes, Request $request)
    {
        if (!$request->filled('id_entite') || !$request->filled('id_banque')) { return collect(); }

        return $lignes->filter(function ($ligne) use ($request) {
            if ($ligne['id_entite'] !== (int) $request->id_entite) { return false; }
            if (!$ligne['id_banque'] || trim((string) $ligne['compte']) === '') {
                return $request->boolean('inclure_sans_banque');
            }
            return (int) $ligne['id_banque'] === (int) $request->id_banque
                ? $request->boolean('inclure_banque', true)
                : $request->boolean('inclure_autres');
        })->values();
    }

    private function empreinte($lignes): string
    {
        return hash('sha256', $lignes->toJson());
    }

    private function erreur(string $champ, string $message): void
    {
        throw ValidationException::withMessages([$champ => $message]);
    }
}
