<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BanqueUser extends Model
{
    protected $table = 'banque_user';

    protected $fillable = [
        'id_banque',
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

    public function banque()
    {
        return $this->belongsTo(banque::class, 'id_banque');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
