<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LigneEtatPaie extends Model
{
    use HasFactory;

    protected $table = 'lignes_etat_paie';

    protected $fillable = [
        'id_etat_paie',
        'id_bulletin_paie',
        'id_personnel',
        'nom_personnel',
        'salaire_base',
        'total_gains',
        'total_retenues',
        'penalite_biometrie',
        'total_sanctions',
        'total_acomptes',
        'net_a_payer',
        'detail_gains',
        'detail_retenues',
    ];

    protected $casts = [
        'salaire_base' => 'float',
        'total_gains' => 'float',
        'total_retenues' => 'float',
        'penalite_biometrie' => 'float',
        'total_sanctions' => 'float',
        'total_acomptes' => 'float',
        'net_a_payer' => 'float',
        'detail_gains' => 'array',
        'detail_retenues' => 'array',
    ];

    public function etat()
    {
        return $this->belongsTo(EtatPaie::class, 'id_etat_paie');
    }

    public function bulletin()
    {
        return $this->belongsTo(BulletinPaie::class, 'id_bulletin_paie');
    }

    public function personnel()
    {
        return $this->belongsTo(personnel::class, 'id_personnel');
    }
}
