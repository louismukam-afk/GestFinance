<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class bon_commandeok extends Model
{
    use HasFactory;

    protected $table='bon_commandeoks';
    public $timestamps=true;
    protected $fillable = [
        'nom_bon_commande',
        'description_bon_commande',
        'date_debut',
        'date_fin',
        'date_entree_signature',
        'date_validation',
        'montant_total',
        'montant_realise',
        'reste',
        'montant_lettre',
        'id_personnel',
        'id_user',
        'statuts',
        'id_entite',
        'validation_pdg',
        'validation_daf',
        'validation_achats',
        'validation_emetteur',
    ];
    protected $casts = [
        'validation_pdg' => 'boolean',
        'validation_daf' => 'boolean',
        'validation_achats' => 'boolean',
        'validation_emetteur' => 'boolean',
        'statuts' => 'integer',    ];
    public function entites()
{
    return $this->belongsTo(entite::class,'id_entite');

}
    public function personnels()
    {
        return $this->belongsTo(personnel::class,'id_personnel');

    }
    public function user()
    {
        return $this->belongsTo(User::class,'id_user');

    }
    public function getStatutFinancementAttribute()
    {
        $total = $this->decaissements()->sum('montant');

        return $total >= $this->montant_total
            ? 'Réalisé'
            : 'En cours';
    }
    public function element_bon_commandes(){
        return $this->hasMany(element_bon_commande::class,'id_bon_commande');
    }

    public function decaissements(){
        return $this->hasMany(decaissement::class,'id_bon_commande');
    }

    public function retour_caisses(){
        return $this->hasMany(retour_caisse::class,'id_bon_commande');
    }
    public function getMontantRealiseAttribute()
    {
        return $this->decaissements()->sum('montant');
    }
    public function getStatutFinancierAttribute()
    {
        if ($this->montant_realise == 0) {
            return 'Non financé';
        }

        if ($this->montant_realise < $this->montant_total) {
            return 'En cours';
        }

        return 'Totalement financé';
    }
    public function getResteAttribute()
    {
        return $this->montant_total - $this->montant_realise;
    }
    public function users()
    {
        return $this->belongsTo(\Encore\Admin\Auth\Database\Administrator::class, 'id_user');
    }
    public function Useres()
    {
        return $this->belongsTo(Administrator::class,'id_user');
    }
    // ✅ Accessor pour afficher le badge directement
    public function getStatutBadgeAttribute()
    {
        switch ($this->statut_bon_code) {
            case 1:
                return '<span class="badge bg-success">Validé</span>';
            case 0:
                return '<span class="badge bg-warning">En attente</span>';
            case 2:
                return '<span class="badge bg-danger">Rejeté</span>';
            default:
                return '<span class="badge bg-secondary">Inconnu</span>';
        }
    }

    public function getStatutBonCodeAttribute(): int
    {
        if ($this->validation_pdg || $this->statuts === 1) {
            return 1;
        }

        if ($this->statuts === 2) {
            return 2;
        }

        if ($this->statuts === 0) {
            return 0;
        }

        return -1;
    }

    public function getStatutBonLibelleAttribute(): string
    {
        return match ($this->statut_bon_code) {
            1 => 'Validé',
            0 => 'En attente',
            2 => 'Rejeté',
            default => 'Inconnu',
        };
    }

}
