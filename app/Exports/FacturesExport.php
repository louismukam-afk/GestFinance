<?php
/**
 * Created by PhpStorm.
 * User: Louis
 * Date: 28/12/2025
 * Time: 05:26
 */
class FacturesExport implements FromCollection, WithHeadings
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return [
            'Étudiant',
            'Spécialité',
            'Facture',
            'Montant',
            'Réglé',
            'Reste'
        ];
    }
}
