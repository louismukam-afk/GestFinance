<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BaremeRetenueFixe extends Model
{
    protected $table = 'baremes_retenues_fixes';
    protected $guarded = [];
    protected $casts = ['montant_min' => 'float', 'montant_max' => 'float', 'montant' => 'float', 'date_debut' => 'date', 'date_fin' => 'date', 'actif' => 'boolean'];

    public function scopeApplicable(Builder $query, Carbon|string $date): Builder
    {
        $date = Carbon::parse($date)->toDateString();
        return $query->where('actif', true)->whereDate('date_debut', '<=', $date)->where(fn($q) => $q->whereNull('date_fin')->orWhereDate('date_fin', '>=', $date));
    }
}
