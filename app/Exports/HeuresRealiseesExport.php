<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class HeuresRealiseesExport implements FromCollection, WithHeadings
{
    public function __construct(private Collection $heures)
    {
    }

    public function headings(): array
    {
        return [
            'Date',
            'Enseignant',
            'Matiere',
            'Plage prevue',
            'Heure debut realisee',
            'Heure fin realisee',
            'Heures prevues',
            'Heures realisees',
            'Heures non comptabilisees',
            'Taux horaire',
            'Montant',
            'Statut',
            'Observation',
        ];
    }

    public function collection(): Collection
    {
        return $this->heures->map(function ($heure) {
            return [
                optional($heure->date_seance)->format('d/m/Y'),
                $heure->personnel->nom ?? '',
                $heure->cours->programme->matiere->nom_matiere ?? '',
                substr($heure->heure_debut_prevue, 0, 5) . ' - ' . substr($heure->heure_fin_prevue, 0, 5),
                $heure->heure_debut_reelle ? substr($heure->heure_debut_reelle, 0, 5) : '',
                $heure->heure_fin_reelle ? substr($heure->heure_fin_reelle, 0, 5) : '',
                $heure->duree_prevue,
                $heure->duree_realisee,
                max(($heure->duree_prevue ?? 0) - ($heure->duree_realisee ?? 0), 0),
                $heure->montant_taux,
                $heure->montant_total,
                $heure->statut,
                $heure->observation,
            ];
        });
    }
}
