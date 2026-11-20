<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VirementVacataire extends Model
{
    protected $table = 'virements_vacataires';
    protected $guarded = ['id'];
    protected $casts = ['date_debut' => 'date', 'date_fin' => 'date', 'date_ordre' => 'date', 'valide_at' => 'datetime', 'mois_requete' => 'date'];

    public function lignes()
    {
        return $this->hasMany(LigneVirementVacataire::class, 'id_virement');
    }
}
