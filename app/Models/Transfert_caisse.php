<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfert_caisse extends Model
{
    use HasFactory;


    protected $table='transfert_caisses';
    public $timestamps=true;
    protected $fillable = [
        'observation',
        'code_transfert',
        'id_caisse_arrivee',
        'montant_transfert',
        'sode_caisse',
        'type_transfert',
        'id_caisse_depart',
        'id_entree_speciale',
        'date_transfert',
        'statut_caisse_transfert',
        'id_user',
        'id_last_editor',
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'id_user');
    }
    public function userLast()
    {
        return $this->belongsTo(User::class,'id_last_editor');
    }
    public function caisseDepart()
    {
        return $this->belongsTo(caisse::class, 'id_caisse_depart', 'id');
    }

    public function caisseArrivee()
    {
        return $this->belongsTo(caisse::class, 'id_caisse_arrivee', 'id');
    }
    public function entree_speciale()
    {
        return $this->belongsTo(entree_speciale::class, 'id_entree_speciale');
    }
    public function decaissements(){
        return $this->hasMany(decaissement::class,'id_caisse');
    }
}
