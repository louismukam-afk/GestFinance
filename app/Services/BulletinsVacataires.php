<?php

namespace App\Services;

use App\Models\{BiometrieImport, entite, HeureRealiseeEnseignant, LigneVirementVacataire, TauxImpot};
use Illuminate\Support\Collection;

class BulletinsVacataires
{
    public function pourImport(BiometrieImport $import): Collection
    {
        $archives = LigneVirementVacataire::where('id_biometrie_import', $import->id)->with('virement')->orderBy('id')->get();
        $clesArchivees = $archives->mapWithKeys(fn($l) => [$l->id_entite.'|'.$l->id_personnel => true]);
        $heures = HeureRealiseeEnseignant::where('id_biometrie_import', $import->id)
            ->with(['personnel.taux_impot', 'personnel.banque'])->orderBy('id')->get();
        // Un bulletin deja valide ne reprend jamais des heures ou taux modifies depuis.
        $personnelsARecalculer = $heures->filter(fn($h) => !$clesArchivees->has($h->id_entite.'|'.$h->id_personnel))->pluck('id_personnel')->unique();
        $source = $heures->whereIn('id_personnel', $personnelsARecalculer);
        abort_if($source->contains(fn($h) => !$h->personnel), 422, 'Un enseignant consolide est introuvable.');
        $provisoires = $source->isEmpty() ? collect() : (new PreparationVirementVacataire())->lignes($source, TauxImpot::where('par_defaut', true)->firstOrFail());
        $nomsEntites = entite::pluck('nom_entite', 'id');
        $lignes = $archives->map(fn($l) => [
            'id_personnel' => $l->id_personnel, 'nom' => $l->nom, 'id_entite' => $l->id_entite,
            'entite_nom' => $l->virement->entite_nom, 'compte' => $l->compte,
            'banque' => $l->virement->banque_nom, 'id_virement' => $l->id_virement,
            'valide' => true, 'details' => $l->details ?? [], 'brut_fiscal' => (float) $l->brut_fiscal,
            'impot' => (float) $l->impot, 'net_avant_frais' => (float) $l->net_avant_frais,
            'frais_transfert' => (float) $l->frais_transfert, 'net_a_payer' => (float) $l->net_a_payer,
        ]);
        foreach ($provisoires as $ligne) {
            if ($clesArchivees->has($ligne['id_entite'].'|'.$ligne['id_personnel'])) { continue; }
            $lignes->push($ligne + [
                'entite_nom' => $nomsEntites[$ligne['id_entite']] ?? 'Entite #'.$ligne['id_entite'],
                'banque' => $ligne['banque_personnel'], 'id_virement' => null, 'valide' => false,
                'frais_transfert' => 0, 'net_a_payer' => $ligne['net_avant_frais'],
            ]);
        }

        return $lignes->groupBy('id_personnel')->map(function ($items) {
            $premiere = $items->first();
            return [
                'id_personnel' => $premiere['id_personnel'], 'nom' => $premiere['nom'],
                'statut' => $items->every('valide') ? 'Valide' : ($items->contains('valide', true) ? 'Partiellement valide' : 'Provisoire'),
                'entites' => $items->sortBy('entite_nom')->values(),
                'brut_fiscal' => round($items->sum('brut_fiscal'), 2), 'impot' => round($items->sum('impot'), 2),
                'net_avant_frais' => round($items->sum('net_avant_frais'), 2),
                'frais_transfert' => round($items->sum('frais_transfert'), 2), 'net_a_payer' => round($items->sum('net_a_payer'), 2),
            ];
        })->sortBy('nom')->values();
    }
}
