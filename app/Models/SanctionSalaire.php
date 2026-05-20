<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SanctionSalaire extends Model
{
    use HasFactory;

    protected $table = 'sanction_salaires';

    protected $fillable = [
        'id_personnel',
        'date_sanction',
        'montant',
        'motif',
        'description',
        'mois_application',
        'periode_debut_application',
        'periode_fin_application',
        'statut',
        'id_bulletin_paie',
        'id_user',
    ];

    protected $casts = [
        'date_sanction' => 'date',
        'periode_debut_application' => 'date',
        'periode_fin_application' => 'date',
        'montant' => 'float',
    ];

    public function personnel()
    {
        return $this->belongsTo(personnel::class, 'id_personnel');
    }

    public function scopeActives(Builder $query): Builder
    {
        return $query->where('statut', 'active');
    }

    public function scopePourPeriode(Builder $query, Carbon|string $debut, Carbon|string $fin): Builder
    {
        $debut = Carbon::parse($debut)->startOfDay();
        $fin = Carbon::parse($fin)->endOfDay();
        $mois = $debut->format('Y-m');

        return $query->where(function (Builder $sub) use ($debut, $fin, $mois) {
            $sub->where('mois_application', $mois)
                ->orWhere(function (Builder $interval) use ($debut, $fin) {
                    $interval->whereNotNull('periode_debut_application')
                        ->whereNotNull('periode_fin_application')
                        ->whereDate('periode_debut_application', '<=', $fin->toDateString())
                        ->whereDate('periode_fin_application', '>=', $debut->toDateString());
                });
        });
    }

    public function scopeNonAffecteesBulletin(Builder $query): Builder
    {
        return $query->whereNull('id_bulletin_paie');
    }
}
