<?php

namespace App\Exports;

use App\Models\bon_commandeok;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BonsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return bon_commandeok::with(['personnels','entites'])->get()->map(function($bon){
            return [
                'Nom' => $bon->nom_bon_commande,
                'Description' => $bon->description_bon_commande,
                'Date début' => $bon->date_debut,
                'Date fin' => $bon->date_fin,
                'Date entrée signature' => $bon->date_entree_signature,
                'Date validation' => $bon->date_validation,
                'Montant total' => $bon->montant_total,
                'Montant réalisé' => $bon->montant_realise,
                'Reste' => $bon->reste,
                'Montant lettre' => $bon->montant_lettre,
                'Personnel' => $bon->personnels->nom ?? 'N/A',
                'Entité' => $bon->entites->nom_entite ?? 'N/A',
                'Statut' => $bon->statuts ? 'Validé' : 'En attente'
            ];
        });
    }

    public function headings(): array
    {
        return [
            '#','Nom',
            'Description',
            'Date début','Date fin',
            'Date entrée signature',
            'Date validation',
            'Montant Total',
            'Montant Réalisé',
            'Reste',
            'Montant Lettre',
            'Personnel',
            'Entité',
            'Statut'
        ];
    }
}
