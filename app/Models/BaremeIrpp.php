<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaremeIrpp extends Model
{
    use HasFactory;

    protected $table = 'baremes_irpp';

    protected $fillable = [
        'montant_min',
        'montant_max',
        'taux',
        'date_debut',
        'date_fin',
        'actif',
        'ordre',
        'id_user',
    ];

    protected $casts = [
        'montant_min' => 'float',
        'montant_max' => 'float',
        'taux' => 'float',
        'date_debut' => 'date',
        'date_fin' => 'date',
        'actif' => 'boolean',
    ];

    public function scopeApplicable(Builder $query, Carbon|string $date): Builder
    {
        $date = Carbon::parse($date)->toDateString();

        return $query->where('actif', true)
            ->whereDate('date_debut', '<=', $date)
            ->where(function (Builder $sub) use ($date) {
                $sub->whereNull('date_fin')
                    ->orWhereDate('date_fin', '>=', $date);
            });
    }
}
