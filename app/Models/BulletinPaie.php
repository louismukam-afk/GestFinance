<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BulletinPaie extends Model
{
    use HasFactory;

    protected $table = 'bulletins_paie';

    protected $fillable = [
        'id_personnel',
        'id_biometrie_import',
        'periode_debut',
        'periode_fin',
        'salaire_base',
        'penalite_biometrie',
        'brut_mensuel',
        'salaire_taxable',
        'salaire_cotisable',
        'total_gains',
        'total_retenues',
        'total_acomptes',
        'total_sanctions',
        'net_a_payer',
        'solde_du',
        'statut',
        'observations',
        'id_user',
    ];

    protected $casts = [
        'periode_debut' => 'date',
        'periode_fin' => 'date',
        'salaire_base' => 'float',
        'penalite_biometrie' => 'float',
        'brut_mensuel' => 'float',
        'salaire_taxable' => 'float',
        'salaire_cotisable' => 'float',
        'total_gains' => 'float',
        'total_retenues' => 'float',
        'total_acomptes' => 'float',
        'total_sanctions' => 'float',
        'net_a_payer' => 'float',
        'solde_du' => 'float',
    ];

    public function personnel()
    {
        return $this->belongsTo(personnel::class, 'id_personnel');
    }

    public function import()
    {
        return $this->belongsTo(BiometrieImport::class, 'id_biometrie_import');
    }

    public function lignes()
    {
        return $this->hasMany(LigneBulletinPaie::class, 'id_bulletin')->orderBy('ordre');
    }
}
