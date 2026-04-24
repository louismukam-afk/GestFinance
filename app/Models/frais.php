<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class frais extends Model
{
    use HasFactory;

    protected $table='frais';
    public $timestamps=true;
    protected $fillable = [
        'nom_frais',
        'description',
        'type_frais',   // 0 = Nature, 1 = Espèce
        'montant',
        'id_user',
    ];

    public function facture_etudiants()
    {
        return $this->hasMany(facture_etudiant::class,'id_frais');

    }
    public function reglement_etudiants()
    {
        return $this->hasMany(reglement_etudiant::class,'id_frais');

    }

    public function user()
    {
        return $this->belongsTo(User::class,'id_user');

    }
    // Petit helper pratique dans les vues
    public function getTypeLabelAttribute1(): string
    {
        return ((int)$this->type_frais === 1) ? 'Espèce' : 'Nature';
    }


    const CASH_FEES_TYPE = 0;
    const KIND_FEES_TYPE = 1;

    public function getPrintedNom() {
        if($this->nom_frais) {
            return $this->nom_frais;
        }
        return $this->nom_frais;
    }

    public function isKind() {
        return $this->type == static::KIND_FEES_TYPE;
    }

    public function isCash() {
        return $this->type == static::CASH_FEES_TYPE;
    }
    // Constantes cohérentes avec les formulaires
    const TYPE_NATURE = 0;
    const TYPE_ESPECE = 1;

    // Helpers lisibles
    public function isNature(): bool { return (int)$this->type_frais === self::TYPE_NATURE; }
    public function isEspece(): bool { return (int)$this->type_frais === self::TYPE_ESPECE; }

    public function getTypeLabelAttribute(): string
    {
        return $this->isEspece() ? 'Espèce' : 'Nature';
    }
}
