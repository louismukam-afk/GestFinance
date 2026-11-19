<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ParametreChargePatronale extends Model
{
    protected $table = 'parametres_charges_patronales';
    protected $guarded = [];
    protected $casts = ['taux' => 'float', 'plafond' => 'float', 'date_debut' => 'date', 'date_fin' => 'date', 'actif' => 'boolean'];

    public function scopeApplicable(Builder $query, Carbon|string $date): Builder
    {
        $date = Carbon::parse($date)->toDateString();
        return $query->where('actif', true)->whereDate('date_debut', '<=', $date)->where(fn($q) => $q->whereNull('date_fin')->orWhereDate('date_fin', '>=', $date));
    }
}
