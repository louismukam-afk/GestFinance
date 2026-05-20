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
        'refus_pdg',
        'motif_refus_pdg',
        'date_refus_pdg',
        'validation_daf',
        'refus_daf',
        'motif_refus_daf',
        'date_refus_daf',
        'validation_achats',
        'refus_achats',
        'motif_refus_achats',
        'date_refus_achats',
        'validation_emetteur',
        'refus_emetteur',
        'motif_refus_emetteur',
        'date_refus_emetteur',
    ];
    protected $casts = [
        'validation_pdg' => 'boolean',
        'validation_daf' => 'boolean',
        'validation_achats' => 'boolean',
        'validation_emetteur' => 'boolean',
        'refus_pdg' => 'boolean',
        'refus_daf' => 'boolean',
        'refus_achats' => 'boolean',
        'refus_emetteur' => 'boolean',
        'date_refus_pdg' => 'datetime',
        'date_refus_daf' => 'datetime',
        'date_refus_achats' => 'datetime',
        'date_refus_emetteur' => 'datetime',
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
        if (
            $this->refus_pdg ||
            $this->refus_daf ||
            $this->refus_achats ||
            $this->refus_emetteur ||
            $this->statuts === 2
        ) {
            return 2;
        }

        if (
            $this->statuts === 1 ||
            (
                $this->validation_pdg &&
                $this->validation_daf &&
                $this->validation_achats &&
                $this->validation_emetteur
            )
        ) {
            return 1;
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

    public function getMotifRefusAttribute(): ?string
    {
        return $this->motif_refus_pdg
            ?: $this->motif_refus_daf
            ?: $this->motif_refus_achats
            ?: $this->motif_refus_emetteur;
    }

    public function validationState(string $niveau): string
    {
        $validationField = "validation_{$niveau}";
        $refusField = "refus_{$niveau}";

        if ((bool) ($this->{$refusField} ?? false)) {
            return 'refuse';
        }

        if ((bool) ($this->{$validationField} ?? false)) {
            return 'valide';
        }

        return 'attente';
    }

}
