<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_super_admin',
        'statut_utilisateur',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    public function facture_etudiants()
    {
        return $this->hasMany(facture_etudiant::class,'id_user');

    }
    public function reglement_etudiants()
    {
        return $this->hasMany(reglement_etudiant::class,'id_user');

    }
    public function  ligne_budgetaire_Entree()
    {
        return $this->hasMany(ligne_budgetaire_Entree::class,'id_user');
    }
    public function  ligne_budgetaire_sortie()
    {
        return $this->hasMany(ligne_budgetaire_sortie::class,'id_user');
    }

    public function specialites(){
    return $this->hasMany(specialite::class,'id_user');
}
    public function cycles(){
        return $this->hasMany(cycle::class,'id_user');
    }
    public function filieres(){
        return $this->hasMany(filiere::class,'id_user');
    }
    public function niveaux()
    {
        return $this->hasMany(niveau::class,'id_user');
    }
    public function scolarite(){
        return $this->hasMany(scolarite::class,'id_user');
    }
    public function tranche_scolarite(){
        return $this->hasMany(tranche_scolarite::class,'id_user');
    }
    public function role_utilisateurs(){
        return $this->hasMany(role_utilisateur::class,'id_user');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user')->withTimestamps();
    }

    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_super_admin || (int) $this->id === 1;
    }

    public function isActive(): bool
    {
        return $this->statut_utilisateur === 'actif' || (int) $this->id === 1;
    }

    public function canAccessRoute(?string $routeName): bool
    {
        if (!$routeName || $this->isSuperAdmin()) {
            return true;
        }

        if (!Schema::hasTable('roles') || !Schema::hasTable('role_user') || !Schema::hasTable('route_permissions')) {
            return true;
        }

        if (!$this->roles()->exists()) {
            return true;
        }

        return $this->roles()
            ->whereHas('permissions', function ($query) use ($routeName) {
                $query->where('route_name', $routeName)
                    ->where('is_active', true);
            })
            ->exists();
    }
    public function personnels(){
        return $this->hasMany(personnel::class,'id_user');
    }

    public function ligne_budgetaire__entrees(){
        return $this->hasMany(ligne_budgetaire_Entree::class,'id_user');
    }
    public function frais()
    {
        return $this->hasMany(frais::class,'id_user');

    }
    public function fonction()
    {
        return $this->hasMany(fonction::class,'id_user');

    }
    public function element_ligne_budgetaire_sorties()
    {
        return $this->hasMany(element_ligne_budgetaire_sortie::class,'id_user');

    }
    public function element_ligne_budgetaire_entrees()
    {
        return $this->hasMany(element_ligne_budgetaire_entree::class,'id_user');

    }

    public function budgets()
    {
        return $this->hasMany(budget::class,'id_user');

    }
    public function decaissements(){
        return $this->hasMany(User::class,'id_user');
    }

    public function donnee_budgetaire_entrees()
    {
        return $this->hasMany(donnee_ligne_budgetaire_entree::class,'id_user');

    }
    public function banques()
    {
        return $this->hasMany(banque::class,'id_user');

    }
    public function caisses()
    {
        return $this->hasMany(caisse::class,'id_user');

    }
    public function donnee_budgetaire_sorties()
    {
        return $this->hasMany(donnee_budgetaire_sortie::class,'id_user');

    }
    public function donnee_ligne_budgetaire_entrees()
    {
        return $this->hasMany(donnee_ligne_budgetaire_entree::class,'id_user');
    }

    public function element_bon_commandes()
    {
        return $this->hasMany(element_bon_commande::class,'id_user');
    }

    public function bon_commandeoks()
    {
        return $this->hasMany(bon_commandeok::class,'id_user');
    }
    public function entites()
    {
        return $this->hasMany(entite::class,'id_user');
    }











    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_super_admin' => 'boolean',
    ];
}
