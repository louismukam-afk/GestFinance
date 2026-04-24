<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class entite extends Model
{
    use HasFactory;

    protected $table='entites';
    public $timestamps=true;
    protected $fillable = [
        'nom_entite',
        'localisation',
        'telephone',
        'email',
        'description',
        'logo',
        'id_user'
    ];
    public function users()
    {
        return $this->belongsTo(\Encore\Admin\Auth\Database\Administrator::class, 'id_user');
    }
    public function bon_commandeok()
    {
        return $this->hasMany(bon_commandeok::class,'id_entite');

    }
    public function user()
    {
        return $this->belongsTo(User::class,'id_user');

    }
}
