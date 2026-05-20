<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigRubriquePersonnel extends Model
{
    use HasFactory;

    protected $table = 'config_rubriques_personnel';

    protected $fillable = [
        'id_personnel',
        'id_rubrique_paie',
        'date_debut',
        'date_fin',
        'valeur',
        'quantite',
        'appliquer_ce_mois',
        'statut',
        'observations',
        'id_user',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'valeur' => 'float',
        'quantite' => 'float',
        'appliquer_ce_mois' => 'boolean',
    ];

    public function personnel()
    {
        return $this->belongsTo(personnel::class, 'id_personnel');
    }

    public function rubrique()
    {
        return $this->belongsTo(RubriquePaie::class, 'id_rubrique_paie');
    }

    public function scopeActivesPourPeriode(Builder $query, Carbon|string $debut, Carbon|string $fin): Builder
    {
        $debut = Carbon::parse($debut)->startOfDay();
        $fin = Carbon::parse($fin)->endOfDay();

        return $query->where('statut', 'actif')
            ->where('appliquer_ce_mois', true)
            ->whereDate('date_debut', '<=', $fin->toDateString())
            ->where(function (Builder $sub) use ($debut) {
                $sub->whereNull('date_fin')
                    ->orWhereDate('date_fin', '>=', $debut->toDateString());
            });
    }
}
