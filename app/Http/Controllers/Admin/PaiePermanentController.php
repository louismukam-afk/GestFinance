<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcompteSalaire;
use App\Models\BaremeIrpp;
use App\Models\BiometrieImport;
use App\Models\BulletinPaie;
use App\Models\ConfigRubriquePersonnel;
use App\Models\EtatPaie;
use App\Models\LigneBulletinPaie;
use App\Models\LigneEtatPaie;
use App\Models\ParametreCac;
use App\Models\ParametrePvid;
use App\Models\PersonnelEntite;
use App\Models\PresencePermanent;
use App\Models\RubriquePaie;
use App\Models\SalairePermanent;
use App\Models\SanctionSalaire;
use App\Models\annee_academique;
use App\Models\entite;
use App\Models\personnel;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaiePermanentController extends Controller
{
    public function index(Request $request)
    {
        return view('Admin.EmploiTemps.paie_permanents', [
            'title' => 'Paie des permanents',
            'bulletins' => BulletinPaie::with(['personnel', 'import', 'lignes'])
                ->when($request->id_personnel, fn($q) => $q->where('id_personnel', $request->id_personnel))
                ->when($request->periode_debut, fn($q) => $q->whereDate('periode_debut', '>=', $request->periode_debut))
                ->when($request->periode_fin, fn($q) => $q->whereDate('periode_fin', '<=', $request->periode_fin))
                ->latest()
                ->get(),
            'personnels' => personnel::where('type_personnel', 'permanent')->orderBy('nom')->get(),
            'imports' => BiometrieImport::where('type_import', 'permanent')->latest()->get(),
            'rubriques' => RubriquePaie::orderBy('ordre')->orderBy('libelle')->get(),
            'configs' => ConfigRubriquePersonnel::with(['personnel', 'rubrique'])->latest()->limit(80)->get(),
            'acomptes' => AcompteSalaire::with('personnel')->latest()->limit(80)->get(),
            'sanctions' => SanctionSalaire::with('personnel')->latest()->limit(80)->get(),
            'baremesIrpp' => BaremeIrpp::orderByDesc('date_debut')->orderBy('ordre')->get(),
            'parametresCac' => ParametreCac::orderByDesc('date_debut')->get(),
            'parametresPvid' => ParametrePvid::orderByDesc('date_debut')->get(),
            'annees' => annee_academique::orderByDesc('id')->get(),
            'entites' => entite::orderBy('nom_entite')->get(),
            'etatsPaie' => EtatPaie::with(['annee_academique', 'entite'])
                ->orderByDesc('date_generation')
                ->limit(50)
                ->get(),
        ]);
    }

    public function storeRubrique(Request $request)
    {
        $data = $request->validate([
            'libelle' => 'required|string|max:255',
            'type' => 'required|in:gain,retenue',
            'mode_calcul' => 'required|in:fixe,pourcentage,kilometrage,bareme,manuel',
            'base_calcul' => 'nullable|in:salaire_base,brut,taxable,cotisable,net,irpp',
            'valeur_defaut' => 'nullable|numeric|min:0',
            'plafond' => 'nullable|numeric|min:0',
            'imposable' => 'nullable|boolean',
            'cotisable' => 'nullable|boolean',
            'actif' => 'nullable|boolean',
        ]);

        $data['code'] = Str::slug($data['libelle'], '_') . '_' . now()->format('His');
        $data['valeur_defaut'] = $data['valeur_defaut'] ?? 0;
        $data['imposable'] = $request->boolean('imposable');
        $data['cotisable'] = $request->boolean('cotisable');
        $data['actif'] = $request->boolean('actif', true);
        $data['systeme'] = false;
        $data['id_user'] = auth()->id() ?? 0;

        RubriquePaie::create($data);

        return back()->with('success', 'Rubrique de paie creee.');
    }

    public function storeConfig(Request $request)
    {
        $data = $request->validate([
            'id_personnel' => 'required|integer|exists:personnels,id',
            'id_rubrique_paie' => 'required|integer|exists:rubriques_paie,id',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'valeur' => 'required|numeric|min:0',
            'quantite' => 'nullable|numeric|min:0',
            'appliquer_ce_mois' => 'nullable|boolean',
            'observations' => 'nullable|string|max:1000',
        ]);

        $data['quantite'] = $data['quantite'] ?? 1;
        $data['appliquer_ce_mois'] = $request->boolean('appliquer_ce_mois', true);
        $data['statut'] = 'actif';
        $data['id_user'] = auth()->id() ?? 0;

        ConfigRubriquePersonnel::create($data);

        return back()->with('success', 'Rubrique affectee au personnel.');
    }

    public function storeAcompte(Request $request)
    {
        $data = $request->validate([
            'id_personnel' => 'required|integer|exists:personnels,id',
            'date_acompte' => 'required|date',
            'montant' => 'required|numeric|min:0',
            'periode_imputation' => 'required|date_format:Y-m',
            'motif' => 'nullable|string|max:255',
        ]);

        $data['statut'] = 'actif';
        $data['id_user'] = auth()->id() ?? 0;

        AcompteSalaire::create($data);

        return back()->with('success', 'Acompte sur salaire enregistre.');
    }

    public function storeSanction(Request $request)
    {
        $data = $request->validate([
            'id_personnel' => 'required|integer|exists:personnels,id',
            'date_sanction' => 'required|date',
            'montant' => 'required|numeric|min:0',
            'motif' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'mois_application' => 'nullable|date_format:Y-m',
            'periode_debut_application' => 'nullable|date',
            'periode_fin_application' => 'nullable|date|after_or_equal:periode_debut_application',
        ]);

        if (empty($data['mois_application']) && empty($data['periode_debut_application'])) {
            $data['mois_application'] = Carbon::parse($data['date_sanction'])->format('Y-m');
        }

        $data['statut'] = 'active';
        $data['id_user'] = auth()->id() ?? 0;

        SanctionSalaire::create($data);

        return back()->with('success', 'Sanction salariale enregistree.');
    }

    public function storeBaremeIrpp(Request $request)
    {
        $data = $request->validate([
            'montant_min' => 'required|numeric|min:0',
            'montant_max' => 'nullable|numeric|gt:montant_min',
            'taux' => 'required|numeric|min:0',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'ordre' => 'nullable|integer|min:0',
            'actif' => 'nullable|boolean',
        ]);

        $data['ordre'] = $data['ordre'] ?? 0;
        $data['actif'] = $request->boolean('actif', true);
        $data['id_user'] = auth()->id() ?? 0;

        BaremeIrpp::create($data);

        return back()->with('success', 'Tranche IRPP enregistree.');
    }

    public function updateBaremeIrpp(Request $request, BaremeIrpp $bareme)
    {
        $data = $request->validate([
            'montant_min' => 'required|numeric|min:0',
            'montant_max' => 'nullable|numeric|gt:montant_min',
            'taux' => 'required|numeric|min:0',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'ordre' => 'nullable|integer|min:0',
            'actif' => 'nullable|boolean',
        ]);

        $data['ordre'] = $data['ordre'] ?? 0;
        $data['actif'] = $request->boolean('actif');

        $bareme->update($data);

        return back()->with('success', 'Tranche IRPP modifiee.');
    }

    public function destroyBaremeIrpp(BaremeIrpp $bareme)
    {
        $bareme->delete();

        return back()->with('success', 'Tranche IRPP supprimee.');
    }

    public function storeParametreCac(Request $request)
    {
        $data = $request->validate([
            'taux' => 'required|numeric|min:0',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'actif' => 'nullable|boolean',
        ]);

        $data['actif'] = $request->boolean('actif', true);
        $data['id_user'] = auth()->id() ?? 0;

        ParametreCac::create($data);

        return back()->with('success', 'Parametre CAC enregistre.');
    }

    public function updateParametreCac(Request $request, ParametreCac $cac)
    {
        $data = $request->validate([
            'taux' => 'required|numeric|min:0',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'actif' => 'nullable|boolean',
        ]);

        $data['actif'] = $request->boolean('actif');

        $cac->update($data);

        return back()->with('success', 'Parametre CAC modifie.');
    }

    public function destroyParametreCac(ParametreCac $cac)
    {
        $cac->delete();

        return back()->with('success', 'Parametre CAC supprime.');
    }

    public function storeParametrePvid(Request $request)
    {
        $data = $request->validate([
            'taux' => 'required|numeric|min:0',
            'plafond' => 'nullable|numeric|min:0',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'actif' => 'nullable|boolean',
        ]);

        $data['actif'] = $request->boolean('actif', true);
        $data['id_user'] = auth()->id() ?? 0;

        ParametrePvid::create($data);

        return back()->with('success', 'Parametre PVID enregistre.');
    }

    public function updateParametrePvid(Request $request, ParametrePvid $pvid)
    {
        $data = $request->validate([
            'taux' => 'required|numeric|min:0',
            'plafond' => 'nullable|numeric|min:0',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'actif' => 'nullable|boolean',
        ]);

        $data['actif'] = $request->boolean('actif');

        $pvid->update($data);

        return back()->with('success', 'Parametre PVID modifie.');
    }

    public function destroyParametrePvid(ParametrePvid $pvid)
    {
        $pvid->delete();

        return back()->with('success', 'Parametre PVID supprime.');
    }

    public function generer(Request $request)
    {
        $data = $request->validate([
            'periode_debut' => 'required|date',
            'periode_fin' => 'required|date|after_or_equal:periode_debut',
            'id_biometrie_import' => 'nullable|integer|exists:biometrie_imports,id',
            'id_personnel' => 'nullable|integer|exists:personnels,id',
        ]);

        $debut = Carbon::parse($data['periode_debut'])->startOfDay();
        $fin = Carbon::parse($data['periode_fin'])->endOfDay();
        $personnels = $this->personnelsAPayer($debut, $fin, $data['id_personnel'] ?? null);
        $count = 0;

        DB::transaction(function () use ($personnels, $debut, $fin, $data, &$count) {
            foreach ($personnels as $personnel) {
                $this->genererBulletinPersonnel($personnel, $debut, $fin, $data['id_biometrie_import'] ?? null);
                $count++;
            }
        });

        return back()->with('success', "{$count} bulletin(s) brouillon genere(s).");
    }

    public function valider(BulletinPaie $bulletin)
    {
        $bulletin->update(['statut' => 'valide']);

        $this->affecterAcomptesEtSanctionsAuBulletin($bulletin);


        return back()->with('success', 'Bulletin valide.');
    }

    public function validerGlobal(Request $request)
    {
        $data = $request->validate([
            'periode_debut' => 'required|date',
            'periode_fin' => 'required|date|after_or_equal:periode_debut',
            'id_personnel' => 'nullable|integer|exists:personnels,id',
        ]);

        $bulletins = BulletinPaie::where('statut', 'brouillon')
            ->whereDate('periode_debut', '<=', $data['periode_fin'])
            ->whereDate('periode_fin', '>=', $data['periode_debut'])
            ->when($data['id_personnel'] ?? null, fn($q) => $q->where('id_personnel', $data['id_personnel']))
            ->get();

        if ($bulletins->isEmpty()) {
            return back()
                ->withInput()
                ->withErrors(['validation_global' => 'Aucun bulletin brouillon trouve pour cette periode.']);
        }

        DB::transaction(function () use ($bulletins) {
            foreach ($bulletins as $bulletin) {
                $bulletin->update(['statut' => 'valide']);
                $this->affecterAcomptesEtSanctionsAuBulletin($bulletin);
            }
        });

        return back()->with('success', $bulletins->count() . ' bulletin(s) valide(s) globalement.');
    }

    public function exportPdf(Request $request)
    {
        $data = $request->validate([
            'periode_debut' => 'required|date',
            'periode_fin' => 'required|date|after_or_equal:periode_debut',
            'id_personnel' => 'nullable|integer|exists:personnels,id',
            'statut' => 'nullable|in:brouillon,valide,paye',
        ]);

        $bulletins = BulletinPaie::with(['personnel', 'lignes', 'import'])
            ->whereDate('periode_debut', '>=', $data['periode_debut'])
            ->whereDate('periode_fin', '<=', $data['periode_fin'])
            ->when($data['id_personnel'] ?? null, fn($q) => $q->where('id_personnel', $data['id_personnel']))
            ->when($data['statut'] ?? null, fn($q) => $q->where('statut', $data['statut']))
            ->orderBy('id_personnel')
            ->get();

        if ($bulletins->isEmpty()) {
            return back()->withErrors(['bulletins' => 'Aucun bulletin trouve pour cette periode. Generez d abord les bulletins brouillon.']);
        }

        $pdf = Pdf::loadView('Admin.EmploiTemps.paie_permanents_pdf', [
            'bulletins' => $bulletins,
            'periodeDebut' => Carbon::parse($data['periode_debut']),
            'periodeFin' => Carbon::parse($data['periode_fin']),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('bulletins_paie_' . Carbon::parse($data['periode_debut'])->format('Y_m') . '.pdf');
    }

    public function genererEtatPaie(Request $request)
    {
        $data = $request->validate([
            'periode_debut' => 'required|date',
            'periode_fin' => 'required|date|after_or_equal:periode_debut',
            'id_annee_academique' => 'nullable|integer|exists:annee_academiques,id',
            'id_entite' => 'nullable|integer|exists:entites,id',
            'statut' => 'nullable|in:brouillon,valide,paye',
            'observations' => 'nullable|string|max:1000',
        ]);

        $debut = Carbon::parse($data['periode_debut'])->startOfDay();
        $fin = Carbon::parse($data['periode_fin'])->endOfDay();
        $personnelIds = $this->personnelIdsPourEtatPaie(
            $debut,
            $fin,
            $data['id_annee_academique'] ?? null,
            $data['id_entite'] ?? null
        );

        $bulletins = BulletinPaie::with(['personnel', 'lignes'])
            ->whereDate('periode_debut', '<=', $fin->toDateString())
            ->whereDate('periode_fin', '>=', $debut->toDateString())
            ->when($data['statut'] ?? null, fn($q) => $q->where('statut', $data['statut']))
            ->when($personnelIds !== null, fn($q) => $q->whereIn('id_personnel', $personnelIds))
            ->orderBy('id_personnel')
            ->get();

        if ($bulletins->isEmpty()) {
            return back()
                ->withInput()
                ->withErrors(['etat_paie' => 'Aucun bulletin trouve pour cette periode de paie. Les dates doivent correspondre aux bulletins deja generes, par exemple du 01 au 30/31 du mois. L export PDF des bulletins n est pas obligatoire avant de generer l etat de paie.']);
        }

        $etat = DB::transaction(function () use ($bulletins, $debut, $fin, $data) {
            $etat = EtatPaie::create([
                'reference' => 'ETP-' . now()->format('Ymd-His') . '-' . Str::upper(Str::random(4)),
                'periode_debut' => $debut->toDateString(),
                'periode_fin' => $fin->toDateString(),
                'id_annee_academique' => $data['id_annee_academique'] ?? null,
                'id_entite' => $data['id_entite'] ?? null,
                'date_generation' => now(),
                'statut' => 'genere',
                'observations' => $data['observations'] ?? null,
                'id_user' => auth()->id() ?? 0,
            ]);

            $this->remplirEtatPaieDepuisBulletins($etat, $bulletins);

            return $etat;
        });

        return redirect()
            ->route('paie_permanents.etats.show', $etat)
            ->with('success', 'Etat de paie genere et enregistre.');
    }

    public function showEtatPaie(EtatPaie $etat)
    {
        $etat->load(['lignes', 'annee_academique', 'entite']);

        return view('Admin.EmploiTemps.etat_paie_show', [
            'title' => 'Etat de paie',
            'etat' => $etat,
            'colonnesGains' => $this->colonnesEtatPaie($etat, 'detail_gains'),
            'colonnesRetenues' => $this->colonnesEtatPaie($etat, 'detail_retenues'),
        ]);
    }

    public function exportEtatPaiePdf(EtatPaie $etat)
    {
        $etat->load(['lignes', 'annee_academique', 'entite']);

        $pdf = Pdf::loadView('Admin.EmploiTemps.etat_paie_pdf', [
            'etat' => $etat,
            'colonnesGains' => $this->colonnesEtatPaie($etat, 'detail_gains'),
            'colonnesRetenues' => $this->colonnesEtatPaie($etat, 'detail_retenues'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('etat_paie_' . $etat->reference . '.pdf');
    }

    public function regenererEtatPaie(EtatPaie $etat)
    {
        $debut = $etat->periode_debut->copy()->startOfDay();
        $fin = $etat->periode_fin->copy()->endOfDay();
        $personnelIds = $this->personnelIdsPourEtatPaie($debut, $fin, $etat->id_annee_academique, $etat->id_entite);

        $bulletins = BulletinPaie::with(['personnel', 'lignes'])
            ->whereDate('periode_debut', '<=', $fin->toDateString())
            ->whereDate('periode_fin', '>=', $debut->toDateString())
            ->when($personnelIds !== null, fn($q) => $q->whereIn('id_personnel', $personnelIds))
            ->orderBy('id_personnel')
            ->get();

        if ($bulletins->isEmpty()) {
            return back()->withErrors(['etat_paie' => 'Aucun bulletin trouve pour regenerer cet etat.']);
        }

        DB::transaction(function () use ($etat, $bulletins) {
            $etat->lignes()->delete();
            $etat->update(['date_generation' => now()]);
            $this->remplirEtatPaieDepuisBulletins($etat, $bulletins);
        });

        return back()->with('success', 'Etat de paie regenere avec les bulletins actuels.');
    }

    public function destroyEtatPaie(EtatPaie $etat)
    {
        $etat->delete();

        return redirect()
            ->route('paie_permanents.index')
            ->with('success', 'Etat de paie supprime.');
    }

    private function personnelsAPayer(Carbon $debut, Carbon $fin, ?int $personnelId)
    {
        return personnel::where('type_personnel', 'permanent')
            ->when($personnelId, fn($q) => $q->where('id', $personnelId))
            ->whereHas('salaires_permanents', function ($q) use ($debut, $fin) {
                $q->where('statut', 'actif')
                    ->whereDate('date_debut', '<=', $fin->toDateString())
                    ->where(function ($sub) use ($debut) {
                        $sub->whereNull('date_fin')
                            ->orWhereDate('date_fin', '>=', $debut->toDateString());
                    });
            })
            ->orderBy('nom')
            ->get();
    }

    private function remplirEtatPaieDepuisBulletins(EtatPaie $etat, $bulletins): void
    {
        foreach ($bulletins as $bulletin) {
            $gains = $this->detailsLignesEtatPaie($bulletin, 'plus');
            $retenues = $this->detailsLignesEtatPaie($bulletin, 'moins');
            $retenuesHorsAcomptesSanctions = max(
                $bulletin->total_retenues - $bulletin->total_acomptes - $bulletin->total_sanctions - $bulletin->penalite_biometrie,
                0
            );

            LigneEtatPaie::create([
                'id_etat_paie' => $etat->id,
                'id_bulletin_paie' => $bulletin->id,
                'id_personnel' => $bulletin->id_personnel,
                'nom_personnel' => $bulletin->personnel->nom ?? '-',
                'salaire_base' => $bulletin->salaire_base,
                'total_gains' => $bulletin->total_gains,
                'total_retenues' => $retenuesHorsAcomptesSanctions,
                'penalite_biometrie' => $bulletin->penalite_biometrie,
                'total_sanctions' => $bulletin->total_sanctions,
                'total_acomptes' => $bulletin->total_acomptes,
                'net_a_payer' => $bulletin->net_a_payer,
                'detail_gains' => $gains,
                'detail_retenues' => $retenues,
            ]);
        }

        $etat->load('lignes');
        $etat->update([
            'nombre_employes' => $etat->lignes->count(),
            'total_gains' => $etat->lignes->sum('total_gains'),
            'total_retenues' => $etat->lignes->sum('total_retenues'),
            'total_penalites' => $etat->lignes->sum('penalite_biometrie'),
            'total_sanctions' => $etat->lignes->sum('total_sanctions'),
            'total_acomptes' => $etat->lignes->sum('total_acomptes'),
            'total_net_a_payer' => $etat->lignes->sum('net_a_payer'),
        ]);
    }

    private function colonnesEtatPaie(EtatPaie $etat, string $champ): array
    {
        return $etat->lignes
            ->flatMap(fn($ligne) => collect($ligne->{$champ} ?? []))
            ->filter(fn($detail) => !empty($detail['code']))
            ->groupBy('code')
            ->map(fn($details) => [
                'code' => $details->first()['code'],
                'libelle' => $details->first()['libelle'] ?? $details->first()['code'],
            ])
            ->values()
            ->all();
    }

    private function personnelIdsPourEtatPaie(Carbon $debut, Carbon $fin, ?int $anneeId, ?int $entiteId): ?array
    {
        if (!$anneeId && !$entiteId) {
            return null;
        }

        $idsAffectations = PersonnelEntite::where('statut', 'actif')
            ->when($anneeId, fn($q) => $q->where(function ($sub) use ($anneeId) {
                $sub->whereNull('id_annee_academique')->orWhere('id_annee_academique', $anneeId);
            }))
            ->when($entiteId, fn($q) => $q->where('id_entite', $entiteId))
            ->where(function ($query) use ($debut) {
                $query->whereNull('date_fin')
                    ->orWhereDate('date_fin', '>=', $debut->toDateString());
            })
            ->where(function ($query) use ($fin) {
                $query->whereNull('date_debut')
                    ->orWhereDate('date_debut', '<=', $fin->toDateString());
            })
            ->pluck('id_personnel')
            ->unique()
            ->values();

        $idsSalaires = SalairePermanent::where('statut', 'actif')
            ->when($anneeId, fn($q) => $q->where('id_annee_academique', $anneeId))
            ->when($entiteId, fn($q) => $q->where('id_entite', $entiteId))
            ->whereDate('date_debut', '<=', $fin->toDateString())
            ->where(function ($query) use ($debut) {
                $query->whereNull('date_fin')
                    ->orWhereDate('date_fin', '>=', $debut->toDateString());
            })
            ->pluck('id_personnel')
            ->unique()
            ->values();

        $idsPresences = PresencePermanent::query()
            ->when($anneeId, fn($q) => $q->where('id_annee_academique', $anneeId))
            ->when($entiteId, fn($q) => $q->where('id_entite', $entiteId))
            ->whereBetween('date_presence', [$debut->toDateString(), $fin->toDateString()])
            ->pluck('id_personnel')
            ->unique()
            ->values();

        return $idsAffectations
            ->merge($idsSalaires)
            ->merge($idsPresences)
            ->unique()
            ->values()
            ->all();
    }

    private function detailsLignesEtatPaie(BulletinPaie $bulletin, string $sens): array
    {
        return $bulletin->lignes
            ->where('sens', $sens)
            ->where('montant', '>', 0)
            ->when($sens === 'moins', fn($lignes) => $lignes->whereNotIn('code', ['acomptes', 'sanctions', 'penalite_biometrie']))
            ->map(fn($ligne) => [
                'code' => $ligne->code,
                'libelle' => $ligne->libelle,
                'montant' => (float) $ligne->montant,
            ])
            ->values()
            ->all();
    }

    private function affecterAcomptesEtSanctionsAuBulletin(BulletinPaie $bulletin): void
    {
        AcompteSalaire::where('id_personnel', $bulletin->id_personnel)
            ->where('statut', 'actif')
            ->whereNull('id_bulletin_paie')
            ->where('periode_imputation', $bulletin->periode_debut->format('Y-m'))
            ->update(['id_bulletin_paie' => $bulletin->id]);

        SanctionSalaire::where('id_personnel', $bulletin->id_personnel)
            ->actives()
            ->nonAffecteesBulletin()
            ->pourPeriode($bulletin->periode_debut, $bulletin->periode_fin)
            ->update(['id_bulletin_paie' => $bulletin->id]);
    }

    private function genererBulletinPersonnel(personnel $personnel, Carbon $debut, Carbon $fin, ?int $importId): BulletinPaie
    {
        $salaire = $this->salairePermanentPourPeriode($personnel->id, $debut, $fin);
        $salaireContrat = (float) ($salaire->montant_mensuel ?? 0);
        $consolidation = $this->consolidationBiometrie($personnel->id, $debut, $fin, $importId, $salaireContrat);
        $salaireTheorique = $consolidation['salaire_theorique'];
        $penaliteBiometrie = $consolidation['penalite_montant'];

        $bulletin = BulletinPaie::updateOrCreate(
            [
                'id_personnel' => $personnel->id,
                'periode_debut' => $debut->toDateString(),
                'periode_fin' => $fin->toDateString(),
            ],
            [
                'id_biometrie_import' => $importId,
                'salaire_base' => $salaireTheorique,
                'penalite_biometrie' => $penaliteBiometrie,
                'statut' => 'brouillon',
                'id_user' => auth()->id() ?? 0,
            ]
        );

        $bulletin->lignes()->delete();

        $this->ajouterLigne($bulletin, null, 'salaire_base_consolide', 'Salaire de base theorique', 'gain', 'plus', 'fixe', 'salaire_base', $salaireTheorique, 0, 1, $salaireTheorique, true, true, 1);

        $configs = ConfigRubriquePersonnel::with('rubrique')
            ->where('id_personnel', $personnel->id)
            ->activesPourPeriode($debut, $fin)
            ->whereHas('rubrique', fn($q) => $q->where('actif', true)->where('systeme', false))
            ->get();

        foreach ($configs as $config) {
            $rubrique = $config->rubrique;
            $base = $this->baseCalcul($rubrique->base_calcul, $salaireTheorique, $salaireTheorique, 0, 0, 0);
            $montant = $this->montantRubrique($rubrique, $config->valeur, $config->quantite, $base);
            if ($montant <= 0) {
                continue;
            }

            $this->ajouterLigne(
                $bulletin,
                $rubrique,
                $rubrique->code,
                $rubrique->libelle,
                $rubrique->type,
                $rubrique->type === 'gain' ? 'plus' : 'moins',
                $rubrique->mode_calcul,
                $rubrique->base_calcul,
                $base,
                $config->valeur,
                $config->quantite,
                $montant,
                $rubrique->imposable,
                $rubrique->cotisable,
                $rubrique->ordre
            );
        }

        $this->recalculerBulletin($bulletin, $debut, $fin);

        return $bulletin;
    }

    private function recalculerBulletin(BulletinPaie $bulletin, Carbon $debut, Carbon $fin): void
    {
        $bulletin->load('lignes');
        $gains = $bulletin->lignes->where('sens', 'plus')->sum('montant');
        $taxable = $bulletin->lignes->where('sens', 'plus')->where('imposable', true)->sum('montant');
        $cotisable = $bulletin->lignes->where('sens', 'plus')->where('cotisable', true)->sum('montant');

        $systemRetenues = $this->calculRetenuesSysteme($bulletin, $taxable, $cotisable);
        foreach ($systemRetenues as $line) {
            $this->ajouterLigne(...$line);
        }

        if ($bulletin->penalite_biometrie > 0) {
            $this->ajouterLigne($bulletin, null, 'penalite_biometrie', 'Penalite biometrie', 'retenue', 'moins', 'manuel', 'net', 0, 0, 1, $bulletin->penalite_biometrie, false, false, 890);
        }

        $acomptes = AcompteSalaire::where('id_personnel', $bulletin->id_personnel)
            ->where('statut', 'actif')
            ->where(function ($query) use ($bulletin) {
                $query->whereNull('id_bulletin_paie')
                    ->orWhere('id_bulletin_paie', $bulletin->id);
            })
            ->where('periode_imputation', $debut->format('Y-m'))
            ->sum('montant');

        $sanctions = SanctionSalaire::where('id_personnel', $bulletin->id_personnel)
            ->actives()
            ->where(function ($query) use ($bulletin) {
                $query->whereNull('id_bulletin_paie')
                    ->orWhere('id_bulletin_paie', $bulletin->id);
            })
            ->pourPeriode($debut, $fin)
            ->sum('montant');

        if ($acomptes > 0) {
            $this->ajouterLigne($bulletin, null, 'acomptes', 'Acomptes sur salaire', 'retenue', 'moins', 'manuel', 'net', 0, 0, 1, $acomptes, false, false, 900);
        }

        if ($sanctions > 0) {
            $this->ajouterLigne($bulletin, null, 'sanctions', 'Sanctions salariales', 'retenue', 'moins', 'manuel', 'net', 0, 0, 1, $sanctions, false, false, 910);
        }

        $bulletin->load('lignes');
        $totalRetenues = $bulletin->lignes->where('sens', 'moins')->sum('montant');
        $net = max($gains - $totalRetenues, 0);

        $bulletin->update([
            'brut_mensuel' => $gains,
            'salaire_taxable' => $taxable,
            'salaire_cotisable' => $cotisable,
            'total_gains' => $gains,
            'total_retenues' => $totalRetenues,
            'total_acomptes' => $acomptes,
            'total_sanctions' => $sanctions,
            'net_a_payer' => $net,
            'solde_du' => $net,
        ]);
    }

    private function calculRetenuesSysteme(BulletinPaie $bulletin, float $taxable, float $cotisable): array
    {
        $rubriques = RubriquePaie::where('actif', true)->where('systeme', true)->get()->keyBy('code');
        $lines = [];
        $cnpsRubrique = $rubriques->get('cnps_salarial');
        $parametrePvid = $this->parametrePvid($bulletin->periode_debut);
        $pvidTaux = (float) ($parametrePvid?->taux ?? $cnpsRubrique?->valeur_defaut ?? 0);
        $pvidPlafond = (float) ($parametrePvid?->plafond ?? $cnpsRubrique?->plafond ?? $cotisable);
        $cnpsBase = $pvidPlafond > 0 ? min($cotisable, $pvidPlafond) : $cotisable;
        $cnps = ($cnpsRubrique && $pvidTaux > 0) ? round($cnpsBase * ($pvidTaux / 100), 2) : 0;

        if ($cnpsRubrique && $cnps > 0) {
            $lines[] = [$bulletin, $cnpsRubrique, 'cnps_salarial', $cnpsRubrique->libelle, 'retenue', 'moins', 'pourcentage', 'cotisable', $cnpsBase, $pvidTaux, 1, $cnps, false, false, $cnpsRubrique->ordre];
        }

        $irppRubrique = $rubriques->get('irpp');
        $irpp = $irppRubrique ? $this->calculIrpp(max($taxable - $cnps, 0), $bulletin->periode_debut) : 0;
        if ($irppRubrique && $irpp > 0) {
            $lines[] = [$bulletin, $irppRubrique, 'irpp', $irppRubrique->libelle, 'retenue', 'moins', 'bareme', 'taxable', max($taxable - $cnps, 0), 0, 1, $irpp, false, false, $irppRubrique->ordre];
        }

        foreach (['cac', 'rav', 'ccf', 'tdl'] as $code) {
            $rubrique = $rubriques->get($code);
            if (!$rubrique) {
                continue;
            }
            $base = $rubrique->base_calcul === 'irpp' ? $irpp : $taxable;
            $taux = $code === 'cac'
                ? $this->tauxCac($bulletin->periode_debut)
                : (float) $rubrique->valeur_defaut;
            $montant = $rubrique->mode_calcul === 'fixe'
                ? (float) $rubrique->valeur_defaut
                : round($base * ($taux / 100), 2);

            if ($montant > 0) {
                $lines[] = [$bulletin, $rubrique, $code, $rubrique->libelle, 'retenue', 'moins', $rubrique->mode_calcul, $rubrique->base_calcul, $base, $taux, 1, $montant, false, false, $rubrique->ordre];
            }
        }

        return $lines;
    }

    private function calculIrpp(float $base, Carbon|string $date): float
    {
        $tranches = BaremeIrpp::applicable($date)
            ->orderBy('montant_min')
            ->orderBy('ordre')
            ->get();

        $impot = 0;

        foreach ($tranches as $tranche) {
            $min = (float) $tranche->montant_min;
            $max = $tranche->montant_max !== null ? (float) $tranche->montant_max : INF;

            if ($base <= $min) {
                continue;
            }

            $montantTranche = min($base, $max) - $min;
            $impot += max($montantTranche, 0) * ((float) $tranche->taux / 100);
        }

        return round($impot, 2);
    }

    private function tauxCac(Carbon|string $date): float
    {
        return (float) (ParametreCac::applicable($date)
            ->orderByDesc('date_debut')
            ->first()?->taux ?? 0);
    }

    private function parametrePvid(Carbon|string $date): ?ParametrePvid
    {
        return ParametrePvid::applicable($date)
            ->orderByDesc('date_debut')
            ->first();
    }

    private function montantRubrique(RubriquePaie $rubrique, float $valeur, float $quantite, float $base): float
    {
        return match ($rubrique->mode_calcul) {
            'pourcentage' => round($base * ($valeur / 100), 2),
            'kilometrage' => round($valeur * $quantite, 2),
            default => round($valeur * max($quantite, 1), 2),
        };
    }

    private function baseCalcul(?string $baseCalcul, float $salaireBase, float $brut, float $taxable, float $cotisable, float $net): float
    {
        return match ($baseCalcul) {
            'brut' => $brut,
            'taxable' => $taxable,
            'cotisable' => $cotisable,
            'net' => $net,
            default => $salaireBase,
        };
    }

    private function ajouterLigne(BulletinPaie $bulletin, ?RubriquePaie $rubrique, ?string $code, string $libelle, string $type, string $sens, ?string $modeCalcul, ?string $baseCalcul, float $base, float $taux, float $quantite, float $montant, bool $imposable, bool $cotisable, int $ordre): LigneBulletinPaie
    {
        return LigneBulletinPaie::create([
            'id_bulletin' => $bulletin->id,
            'id_rubrique_paie' => $rubrique?->id,
            'code' => $code,
            'libelle' => $libelle,
            'type' => $type,
            'sens' => $sens,
            'mode_calcul' => $modeCalcul,
            'base_calcul' => $baseCalcul,
            'base' => $base,
            'taux' => $taux,
            'quantite' => $quantite,
            'montant' => max(round($montant, 2), 0),
            'imposable' => $imposable,
            'cotisable' => $cotisable,
            'ordre' => $ordre,
        ]);
    }

    private function salairePermanentPourPeriode(int $personnelId, Carbon $debut, Carbon $fin): ?SalairePermanent
    {
        return SalairePermanent::where('id_personnel', $personnelId)
            ->where('statut', 'actif')
            ->whereDate('date_debut', '<=', $fin->toDateString())
            ->where(function ($query) use ($debut) {
                $query->whereNull('date_fin')
                    ->orWhereDate('date_fin', '>=', $debut->toDateString());
            })
            ->orderByDesc('date_debut')
            ->first();
    }

    private function consolidationBiometrie(int $personnelId, Carbon $debut, Carbon $fin, ?int $importId, float $salaireContrat): array
    {
        $presences = PresencePermanent::where('id_personnel', $personnelId)
            ->when($importId, fn($q) => $q->where('id_biometrie_import', $importId))
            ->whereBetween('date_presence', [$debut->toDateString(), $fin->toDateString()])
            ->get();

        if ($presences->isEmpty()) {
            return [
                'salaire_theorique' => $salaireContrat,
                'montant_du' => $salaireContrat,
                'penalite_montant' => 0,
            ];
        }

        $totauxMensuels = $presences
            ->groupBy(fn($presence) => optional($presence->date_presence)->format('Y-m'))
            ->map(function ($monthItems) {
                $presence = $monthItems->first();
                $date = $presence->date_presence ? Carbon::parse($presence->date_presence) : now();
                $salaire = $this->salairePermanentPourConsolidation(
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

        $salaireTheorique = (float) $totauxMensuels->sum('salaire');
        $penaliteMontant = (float) $totauxMensuels->sum('penalite');

        return [
            'salaire_theorique' => $salaireTheorique,
            'montant_du' => max($salaireTheorique - $penaliteMontant, 0),
            'penalite_montant' => $penaliteMontant,
        ];
    }

    private function salairePermanentPourConsolidation(int $personnelId, Carbon $date, ?int $anneeId, ?int $entiteId): ?SalairePermanent
    {
        return SalairePermanent::where('id_personnel', $personnelId)
            ->where('statut', 'actif')
            ->when($anneeId, fn($q) => $q->where(function ($sub) use ($anneeId) {
                $sub->whereNull('id_annee_academique')->orWhere('id_annee_academique', $anneeId);
            }))
            ->when($entiteId, fn($q) => $q->where(function ($sub) use ($entiteId) {
                $sub->whereNull('id_entite')->orWhere('id_entite', $entiteId);
            }))
            ->whereDate('date_debut', '<=', $date->toDateString())
            ->where(function ($query) use ($date) {
                $query->whereNull('date_fin')
                    ->orWhereDate('date_fin', '>=', $date->toDateString());
            })
            ->orderByDesc('date_debut')
            ->first();
    }
}
