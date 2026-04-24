<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;


class FacturesReglementsExport implements FromCollection, WithHeadings
{
    protected $factures;

    public function __construct($factures)
    {
        $this->factures = $factures;
    }

    public function collection()
    {
        return $this->factures->map(function ($f) {

            $verse = $f->reglement_etudiants->sum('montant_reglement');
            $reste = $f->montant_total_facture - $verse;

            return [
                'specialite' => optional($f->specialites)->nom_specialite,
                'ligne_budgetaire' => optional($f->ligne_budgetaire_entree)->libelle_ligne_budgetaire_entree,
                'etudiant' => optional($f->etudiants)->nom . ' ' . optional($f->etudiants)->prenom,
                'facture' => $f->numero_facture,
                'montant_facture' => $f->montant_total_facture,
                'montant_regle' => $verse,
                'reste' => max(0, $reste),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Spécialité',
            'Ligne budgétaire',
            'Étudiant',
            'N° Facture',
            'Montant Facture',
            'Montant Réglé',
            'Reste à payer',
        ];
    }
}
