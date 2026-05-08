<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupeMatiereLigne extends Model
{
    use HasFactory;

    protected $table = 'groupe_matiere_lignes';

    protected $fillable = [
        'id_groupe_matiere',
        'id_programme_specialite',
        'id_user',
    ];

    public function groupe()
    {
        return $this->belongsTo(GroupeMatiere::class, 'id_groupe_matiere');
    }

    public function programme()
    {
        return $this->belongsTo(ProgrammeSpecialite::class, 'id_programme_specialite');
    }
}
