@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-primary">{{ $title }}</h3>
        <div class="no-print">
            <a href="{{ route('cours_enseignants.emploi_enseignant_pdf', request()->query()) }}" class="btn btn-danger">PDF</a>
            <button type="button" onclick="window.print()" class="btn btn-dark">Imprimer</button>
            <a href="{{ route('emploi_temps.index') }}" class="btn btn-secondary">Retour</a>
        </div>
    </div>

    <form method="GET" class="card p-3 mb-4 no-print">
        <div class="row">
            <div class="col-md-3">
                <label>Enseignant</label>
                <select name="id_personnel" class="form-control">
                    <option value="">Tous</option>
                    @foreach($personnels as $personnel)
                        <option value="{{ $personnel->id }}" {{ request('id_personnel') == $personnel->id ? 'selected' : '' }}>{{ $personnel->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label>Cycle</label>
                <select name="id_cycle" class="form-control">
                    <option value="">Tous</option>
                    @foreach($cycles as $cycle)
                        <option value="{{ $cycle->id }}" {{ request('id_cycle') == $cycle->id ? 'selected' : '' }}>{{ $cycle->nom_cycle }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label>Filiere</label>
                <select name="id_filiere" class="form-control">
                    <option value="">Toutes</option>
                    @foreach($filieres as $filiere)
                        <option value="{{ $filiere->id }}" {{ request('id_filiere') == $filiere->id ? 'selected' : '' }}>{{ $filiere->nom_filiere }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label>Niveau</label>
                <select name="id_niveau" class="form-control">
                    <option value="">Tous</option>
                    @foreach($niveaux as $niveau)
                        <option value="{{ $niveau->id }}" {{ request('id_niveau') == $niveau->id ? 'selected' : '' }}>{{ $niveau->nom_niveau }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label>Specialite</label>
                <select name="id_specialite" class="form-control">
                    <option value="">Toutes</option>
                    @foreach($specialites as $specialite)
                        <option value="{{ $specialite->id }}" {{ request('id_specialite') == $specialite->id ? 'selected' : '' }}>{{ $specialite->nom_specialite }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mt-2">
                <label>Semestre</label>
                <select name="semestre" class="form-control">
                    <option value="">Tous</option>
                    @foreach($semestres as $value => $label)
                        <option value="{{ $value }}" {{ request('semestre') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mt-2">
                <label>Annee academique</label>
                <select name="id_annee_academique" class="form-control">
                    <option value="">Toutes</option>
                    @foreach($annees as $annee)
                        <option value="{{ $annee->id }}" {{ request('id_annee_academique') == $annee->id ? 'selected' : '' }}>{{ $annee->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mt-2">
                <label>Entite</label>
                <select name="id_entite" class="form-control">
                    <option value="">Toutes</option>
                    @foreach($entites as $entite)
                        <option value="{{ $entite->id }}" {{ request('id_entite') == $entite->id ? 'selected' : '' }}>{{ $entite->nom_entite }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mt-2"><label>Date debut</label><input type="date" name="date_debut" value="{{ request('date_debut') }}" class="form-control"></div>
            <div class="col-md-2 mt-2"><label>Date fin</label><input type="date" name="date_fin" value="{{ request('date_fin') }}" class="form-control"></div>
            <div class="col-md-2 mt-2 d-flex align-items-end"><button class="btn btn-primary w-100">Filtrer</button></div>
        </div>
    </form>

    @if($rows->isNotEmpty())
        @php
            $first = $rows->first();
            $enseignants = $rows->pluck('enseignant')->unique()->filter()->implode(', ');
            $entiteLabel = optional($selectedEntite)->nom_entite ?: ($first['entite'] ?? '-');
            $anneeLabel = optional($selectedAnnee)->nom ?: ($first['annee'] ?? '-');
        @endphp
        <div class="emploi-header mb-3">
            @if($logoSrc)
                <img src="{{ $logoSrc }}" class="emploi-logo" alt="">
            @endif
            <div class="text-center">
                <h3 class="text-uppercase mb-1">Emploi du temps enseignant</h3>
                <strong>Entite :</strong> {{ $entiteLabel }}
                <span class="mx-2">|</span>
                <strong>Annee academique :</strong> {{ $anneeLabel }}<br>
                <strong>Enseignant :</strong> {{ $enseignants ?: '-' }}
                <span class="mx-2">|</span>
                <strong>Periode :</strong> {{ request('date_debut') ?: '-' }} - {{ request('date_fin') ?: '-' }}
            </div>
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered" style="background:#fff">
            <thead class="table-dark">
                <tr>
                    <th style="width:110px">Jour</th>
                    @foreach($plages as $plage)
                        <th class="text-center">{{ substr($plage->heure_debut, 0, 5) }} - {{ substr($plage->heure_fin, 0, 5) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($jours as $jourIndex => $jourLabel)
                    <tr>
                        <th class="table-secondary text-uppercase align-middle">{{ $jourLabel }}</th>
                        @foreach($plages as $plage)
                            <td class="{{ $plage->type_plage === 'pause' ? 'pause-cell' : '' }}" style="min-width:180px; vertical-align:top">
                                @if($plage->type_plage === 'pause')
                                    <strong>Pause</strong>
                                @else
                                    @php
                                        $cellItems = collect($matrix[$jourIndex][$plage->id] ?? [])->groupBy(function ($item) {
                                            return implode('|', [
                                                $item['type_matiere'] ?? '',
                                                $item['code'] ?? '',
                                                $item['matiere'] ?? '',
                                                $item['salle'] ?? '',
                                                $item['periode'] ?? '',
                                                $item['volume_total'] ?? '',
                                            ]);
                                        });
                                    @endphp
                                    @foreach($cellItems as $items)
                                        @php
                                            $item = $items->first();
                                            $codesSpecialites = $items->pluck('specialite_code')->filter()->unique()->implode(', ');
                                            $showCodes = $codesSpecialites && (($item['type_matiere'] ?? '') === 'transversale' || $items->pluck('specialite_code')->filter()->unique()->count() > 1);
                                        @endphp
                                        <div class="mb-2 p-2 border rounded">
                                            <strong>{{ $item['code'] ? $item['code'].' : ' : '' }}{{ $item['matiere'] }}</strong><br>
                                            @if($showCodes)
                                                <small>Specialites : {{ $codesSpecialites }}</small><br>
                                            @endif
                                            <span>Seance : {{ number_format($item['volume'], 1, ',', ' ') }}H / Volume total : {{ number_format($item['volume_total'], 1, ',', ' ') }}H</span><br>
                                            @unless($hideContextInCells)
                                                <small>{{ $item['cycle'] }} / {{ $item['filiere'] }} / {{ $item['specialite'] }} / {{ $item['niveau'] }}</small><br>
                                            @endunless
                                            <small>Salle : {{ $item['salle'] }}</small><br>
                                            <small>{{ $item['periode'] }}</small>
                                        </div>
                                    @endforeach
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($rows->isEmpty())
        <div class="alert alert-info mt-3">Aucun emploi du temps trouve pour cette periode.</div>
    @endif
</div>
@endsection

@section('styles')
<style>
@page {
    size: A4 landscape;
    margin: 1cm;
}
.emploi-header {
    position: relative;
    min-height: 70px;
}
.emploi-logo {
    position: absolute;
    left: 0;
    top: 0;
    max-width: 70px;
    max-height: 70px;
}
.pause-cell {
    text-align: center;
    vertical-align: middle !important;
    background: #f7f7f7;
}
@media print {
    .no-print, nav, .navbar, .breadcrumb, header, footer {
        display: none !important;
    }
    .container {
        width: 100% !important;
        max-width: 100% !important;
    }
    table {
        font-size: 9px;
    }
}
</style>
@endsection

@section('breadcrumb')
<ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
    <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
    <li><a href="{{ route('emploi_temps.index') }}"><strong>Ressources humaines</strong></a></li>
    <li class="active"><strong>{{ $title }}</strong></li>
</ol>
@endsection
