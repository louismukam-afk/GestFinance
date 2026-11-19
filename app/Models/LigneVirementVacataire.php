<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LigneVirementVacataire extends Model
{
    protected $table = 'lignes_virement_vacataires';
    protected $guarded = ['id'];
    protected $casts = [
        'details' => 'array', 'sans_compte_confirme' => 'boolean',
        'brut_fiscal' => 'decimal:2', 'impot' => 'decimal:2', 'net_avant_frais' => 'decimal:2',
        'frais_transfert' => 'decimal:2', 'net_a_payer' => 'decimal:2',
    ];

    public function virement()
    {
        return $this->belongsTo(VirementVacataire::class, 'id_virement');
    }
}
