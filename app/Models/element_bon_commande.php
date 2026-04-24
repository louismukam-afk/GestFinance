<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class element_bon_commande extends Model
{
    use HasFactory;

    protected $table='element_bon_commandes';
    public $timestamps=true;
    protected $fillable = [
        'nom_element_bon_commande',
        'description_elements_bon_commande',
        'quantite_element_bon_commande',
        'id_user',
        'id_bon_commande',
        'prix_unitaire_element_bon_commande',
        'montant_total_element_bon_commande',
        'date_realisation',
    ];

    public function bon_commandeok()
    {
        return $this->belongsTo(bon_commandeok::class,'id_bon_commande');

    }
    public function user()
    {
        return $this->belongsTo(User::class,'id_user');

    }
}
