<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RubriquePaie extends Model
{
    use HasFactory;

    protected $table = 'rubriques_paie';

    protected $fillable = [
        'code',
        'libelle',
        'type',
        'mode_calcul',
        'base_calcul',
        'valeur_defaut',
        'plafond',
        'imposable',
        'cotisable',
        'systeme',
        'actif',
        'ordre',
        'id_user',
    ];

    protected $casts = [
        'valeur_defaut' => 'float',
        'plafond' => 'float',
        'imposable' => 'boolean',
        'cotisable' => 'boolean',
        'systeme' => 'boolean',
        'actif' => 'boolean',
    ];

    public function configs_personnel()
    {
        return $this->hasMany(ConfigRubriquePersonnel::class, 'id_rubrique_paie');
    }
}
