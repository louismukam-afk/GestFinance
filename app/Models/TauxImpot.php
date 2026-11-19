<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TauxImpot extends Model
{
    protected $table = 'taux_impots';

    protected $fillable = ['nom', 'taux', 'par_defaut'];

    protected $casts = ['taux' => 'decimal:2', 'par_defaut' => 'boolean'];

    public function personnels()
    {
        return $this->hasMany(personnel::class, 'id_taux_impot');
    }
}
