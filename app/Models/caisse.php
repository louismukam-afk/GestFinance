<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class caisse extends Model
{
    use HasFactory;

    protected $table='caisses';
    public $timestamps=true;
    protected $fillable = [
        'nom_caisse',
        'description',
        'code_caisse',
        'type_caisse',
        'status_caisse',
        'id_user',
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'id_user');
    }
    public function reglement_etudiants()
    {
        return $this->hasMany(reglement_etudiant::class,'id_caisse');

    }
    public function decaissements(){
        return $this->hasMany(decaissement::class,'id_caisse');
    }
    public function transfertsSortants()
    {
        return $this->hasMany(Transfert_caisse::class, 'id_caisse_depart', 'id');
    }

    public function transfertsEntrants()
    {
        return $this->hasMany(Transfert_caisse::class, 'id_caisse_arrivee', 'id');
    }
}
