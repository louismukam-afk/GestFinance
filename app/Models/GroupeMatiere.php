<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupeMatiere extends Model
{
    use HasFactory;

    protected $table = 'groupe_matieres';

    protected $fillable = [
        'id_specialite',
        'id_matiere_parent',
        'libelle_groupe',
        'description',
        'id_user',
    ];

    public function specialite()
    {
        return $this->belongsTo(specialite::class, 'id_specialite');
    }

    public function matiere_parent()
    {
        return $this->belongsTo(Matiere::class, 'id_matiere_parent');
    }

    public function lignes()
    {
        return $this->hasMany(GroupeMatiereLigne::class, 'id_groupe_matiere');
    }
}
