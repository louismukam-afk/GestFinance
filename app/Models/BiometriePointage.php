<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiometriePointage extends Model
{
    use HasFactory;

    protected $table = 'biometrie_pointages';

    protected $fillable = [
        'id_biometrie_import',
        'id_personnel',
        'departement',
        'nom_biometrie',
        'numero_biometrie',
        'date_heure_pointage',
        'location_id',
        'id_number',
        'verify_code',
        'card_no',
        'raw_data',
        'id_user',
    ];

    protected $casts = [
        'date_heure_pointage' => 'datetime',
        'raw_data' => 'array',
    ];

    public function import()
    {
        return $this->belongsTo(BiometrieImport::class, 'id_biometrie_import');
    }

    public function personnel()
    {
        return $this->belongsTo(personnel::class, 'id_personnel');
    }
}
