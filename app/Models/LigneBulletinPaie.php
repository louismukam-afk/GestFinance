<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LigneBulletinPaie extends Model
{
    use HasFactory;

    protected $table = 'lignes_bulletin_paie';

    protected $fillable = [
        'id_bulletin',
        'id_rubrique_paie',
        'code',
        'libelle',
        'type',
        'sens',
        'mode_calcul',
        'base_calcul',
        'base',
        'taux',
        'quantite',
        'montant',
        'imposable',
        'cotisable',
        'ordre',
        'observations',
    ];

    protected $casts = [
        'base' => 'float',
        'taux' => 'float',
        'quantite' => 'float',
        'montant' => 'float',
        'imposable' => 'boolean',
        'cotisable' => 'boolean',
    ];

    public function bulletin()
    {
        return $this->belongsTo(BulletinPaie::class, 'id_bulletin');
    }

    public function rubrique()
    {
        return $this->belongsTo(RubriquePaie::class, 'id_rubrique_paie');
    }
}
