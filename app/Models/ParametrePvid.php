<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParametrePvid extends Model
{
    use HasFactory;

    protected $table = 'parametres_pvid';

    protected $fillable = [
        'taux',
        'plafond',
        'date_debut',
        'date_fin',
        'actif',
        'id_user',
    ];

    protected $casts = [
        'taux' => 'float',
        'plafond' => 'float',
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
