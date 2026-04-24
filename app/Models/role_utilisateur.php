<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class role_utilisateur extends Model
{
    use HasFactory;
    protected $table='role_utilisateurs';
    public $timestamps=true;

    protected  $fillable = [
        'id_user',
         'value',
        'id_personnel'
        ];
    public function user(){
        return $this->belongsTo(User::class,'id_user');
    }
    public function personnels(){
        return $this->belongsTo(personnel::class,'id_personnel');
    }

}
