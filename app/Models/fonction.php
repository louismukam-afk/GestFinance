<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class fonction extends Model
{
    use HasFactory;

    protected $table='fonctions';
    public $timestamps=true;
    protected $fillable = [
        'nom_fonction',
        'description',
        'id_user',
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'id_user');

    }
}
