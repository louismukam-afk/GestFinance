<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaisseUser extends Model
{
    protected $table = 'caisse_user';

    protected $fillable = [
        'id_caisse',
        'id_user',
        'peut_encaisser',
        'peut_decaisser',
        'actif',
        'date_debut',
        'date_fin',
        'observation',
    ];

    protected $casts = [
        'peut_encaisser' => 'boolean',
        'peut_decaisser' => 'boolean',
        'actif' => 'boolean',
        'date_debut' => 'date',
        'date_fin' => 'date',
    ];

    public function caisse()
    {
        return $this->belongsTo(caisse::class, 'id_caisse');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
