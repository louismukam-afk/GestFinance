<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Matiere extends Model
{
    use HasFactory;

    protected $table = 'matieres';

    protected $fillable = [
        'nom_matiere',
        'code_matiere',
        'description',
        'id_user',
    ];

    public function lignes_rattrapage()
    {
        return $this->hasMany(FactureRattrapageLigne::class, 'id_matiere');
    }
}
