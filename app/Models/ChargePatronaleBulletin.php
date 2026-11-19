<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChargePatronaleBulletin extends Model
{
    protected $table = 'charges_patronales_bulletin';
    protected $guarded = [];
    protected $casts = ['base' => 'float', 'taux' => 'float', 'montant' => 'float'];
}
