<?php

namespace App\Services;

use App\Models\{BiometrieImport, HeureRealiseeEnseignant, LigneVirementVacataire, TauxImpot, VirementVacataire};
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class PaieImportVacataire
{
    public function lignes(BiometrieImport $import, ?Collection $heures = null): Collection
    {
        $heures ??= $import->heures_realisees()->with(['personnel.taux_impot', 'personnel.banque'])->orderBy('id')->get();
        abort_if($heures->contains(fn($h) => !$h->personnel), 422, 'Un enseignant consolide est introuvable.');
        $defaut = TauxImpot::where('par_defaut', true)->firstOrFail();
        $initiales = collect();
        $precedentes = collect();
        $sources = collect();
        if ($import->estRequete()) {
            $initiales = LigneVirementVacataire::where('id_biometrie_import', $import->id_import_parent)->get();
            $precedentes = LigneVirementVacataire::whereHas('virement', fn($q) => $q->where('nature', 'requete')
                ->where('id_import_parent', $import->id_import_parent)->whereDate('mois_requete', $import->mois_requete)
                ->where('id_biometrie_import', '!=', $import->id))->get();
            $tauxArchives = $initiales->concat($precedentes)
                ->concat(LigneVirementVacataire::where('id_biometrie_import', $import->id)->get());
            $sources = $this->heuresInitiales($import)->groupBy('id_personnel');
            foreach ($heures->groupBy('id_personnel') as $items) {
                $personnel = $items->first()->id_personnel;
                $detail = $tauxArchives->where('id_personnel', $personnel)
                    ->flatMap(fn($l) => $l->details ?? [])->firstWhere('mois', $import->mois_requete->format('Y-m'));
                if (!$detail && ($sources->has($personnel) || $items->contains(fn($h) => $h->type_requete === 'presence_manuelle'))) {
                    $detail = ['taux' => ($items->first()->personnel->taux_impot ?? $defaut)->taux];
                }
                if (!$detail || !isset($detail['taux'])) {
                    throw ValidationException::withMessages(['import' => 'Le taux archive du mois est manquant pour un enseignant de cette requete.']);
                }
                $taux = new TauxImpot(['nom' => 'Taux de reference de la requete', 'taux' => $detail['taux']]);
                $items->each(fn($h) => $h->personnel->setRelation('taux_impot', $taux));
            }
        }
        $lignes = (new PreparationVirementVacataire())->lignes($heures, $defaut);
        if (!$import->estRequete()) { return $lignes; }

        $resultat = $lignes->map(function ($ligne) use ($import, $initiales, $precedentes, $sources, $heures) {
            $originale = $initiales->where('id_entite', $ligne['id_entite'])->where('id_personnel', $ligne['id_personnel'])->first();
            $mois = $import->mois_requete->format('Y-m');
            $base = collect($originale?->details ?? [])->firstWhere('mois', $mois);
            if (!$base && !$originale && ($sources->get($ligne['id_personnel'], collect())->contains('id_entite', $ligne['id_entite'])
                || $heures->where('id_personnel', $ligne['id_personnel'])->where('id_entite', $ligne['id_entite'])->contains(fn($h) => $h->type_requete === 'presence_manuelle'))) {
                $base = ['brut_fiscal' => 0, 'impot' => 0];
            }
            if (!$base) { return null; }
            $autres = $precedentes->where('id_entite', $ligne['id_entite'])->where('id_personnel', $ligne['id_personnel']);
            // Les frais deja retenus ne sont pas rembourses par une requete sur les heures.
            $couvertBrut = round($base['brut_fiscal'] + $autres->sum('brut_fiscal'), 2);
            $couvertImpot = round($base['impot'] + $autres->sum('impot'), 2);
            $brut = round($ligne['brut_fiscal'] - $couvertBrut, 2);
            $impot = round($ligne['impot'] - $couvertImpot, 2);
            $net = round($brut - $impot, 2);
            $detail = ['mois' => $mois, 'taux' => $ligne['details'][0]['taux'],
                'brut_fiscal' => $brut, 'impot' => $impot, 'net_avant_frais' => $net,
                'droit_revise' => $ligne['brut_fiscal'], 'deja_couvert' => $couvertBrut,
                'corrections' => $heures->where('id_personnel', $ligne['id_personnel'])->where('id_entite', $ligne['id_entite'])
                    ->filter(fn($h) => $h->type_requete !== null)->map(fn($h) => [
                        'date' => $h->date_seance->format('Y-m-d'), 'type' => $h->libelle_type_requete,
                        'heures_avant' => $h->duree_avant_requete, 'heures_apres' => $h->duree_realisee,
                        'montant_heures_avant' => $h->montant_avant_requete, 'montant_heures_apres' => $h->montant_total,
                        'sanction_avant' => $h->sanction_avant_requete,
                        'sanction_apres' => $h->sanction_annulee ? 0 : round(max($h->duree_prevue - $h->duree_realisee, 0) * $h->montant_taux, 2),
                        'justification' => $h->observation,
                    ])->values()->all()];
            return array_merge($ligne, ['brut_fiscal' => $brut, 'impot' => $impot, 'net_avant_frais' => $net,
                'details' => [$detail], 'droit_revise' => $ligne['brut_fiscal'], 'deja_couvert' => $couvertBrut]);
        })->filter()->values();
        $reprises = $resultat->where('net_avant_frais', '<', 0)->pluck('id_personnel');
        return $resultat->map(fn($l) => $l + ['reprise_a_verifier' => $reprises->contains($l['id_personnel'])]);
    }

    public function initialiserEnseignant(BiometrieImport $requete, int $personnel, bool $autoriserSansConsolidation = false): void
    {
        if (!$requete->estRequete() || $requete->heures_realisees()->where('id_personnel', $personnel)->exists()) { return; }
        $details = LigneVirementVacataire::where('id_biometrie_import', $requete->id_import_parent)->where('id_personnel', $personnel)
            ->get()->flatMap(fn($l) => $l->details ?? []);
        if (!$details->contains('mois', $requete->mois_requete->format('Y-m'))) {
            $initiales = $this->heuresInitiales($requete)->where('id_personnel', $personnel);
            if ($initiales->isEmpty() && !$autoriserSansConsolidation && $this->heuresReference($requete, $personnel)->isEmpty()) {
                throw ValidationException::withMessages(['id_cours_enseignant' => 'Cet enseignant ne possede aucune heure consolidee dans l import initial pour ce mois.']);
            }
        }
        $heures = $this->heuresReference($requete, $personnel);
        if ($heures->isEmpty() && !$autoriserSansConsolidation) {
            throw ValidationException::withMessages(['import' => 'Les heures source du mois ne sont plus disponibles.']);
        }
        foreach ($heures as $heure) {
            $copie = $heure->replicate();
            $copie->id_biometrie_import = $requete->id;
            $copie->type_requete = null;
            $copie->duree_avant_requete = null;
            $copie->montant_avant_requete = null;
            $copie->sanction_avant_requete = null;
            $copie->save();
        }
    }

    public function heuresReference(BiometrieImport $requete, int $personnel): Collection
    {
        $courantes = $requete->heures_realisees()->where('id_personnel', $personnel)->get();
        if ($courantes->isNotEmpty()) { return $courantes; }
        $precedent = VirementVacataire::where('nature', 'requete')->where('id_import_parent', $requete->id_import_parent)
            ->whereDate('mois_requete', $requete->mois_requete)->where('id_biometrie_import', '!=', $requete->id)
            ->whereHas('lignes', fn($q) => $q->where('id_personnel', $personnel))
            ->whereIn('id_biometrie_import', BiometrieImport::select('id'))->latest('id')->first();
        $source = $precedent?->id_biometrie_import ?? $requete->id_import_parent;
        return HeureRealiseeEnseignant::where('id_biometrie_import', $source)->where('id_personnel', $personnel)
            ->whereBetween('date_seance', [$requete->date_debut, $requete->date_fin])->get();
    }

    private function heuresInitiales(BiometrieImport $requete): Collection
    {
        return HeureRealiseeEnseignant::where('id_biometrie_import', $requete->id_import_parent)
            ->whereBetween('date_seance', [$requete->date_debut, $requete->date_fin])->with('personnel')->get();
    }
}
