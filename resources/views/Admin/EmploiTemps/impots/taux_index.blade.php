@extends('layouts.app')
@section('content')
<div class="container">
    <h3 class="text-primary">{{ $title }}</h3>
    <div style="margin-bottom: 15px;">
        <a href="{{ route('emploi_temps.index') }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Ressources humaines</a>
        <a href="{{ route('taux_impots.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Nouveau taux</a>
        <a href="{{ route('biometrie_heures.historique') }}" class="btn btn-default"><i class="fa fa-history"></i> Historique des consolidations</a>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead><tr><th>Nom</th><th>Taux (%)</th><th>Par defaut</th><th>Action</th></tr></thead>
            <tbody>
                @forelse($taux as $item)
                    <tr><td>{{ $item->nom }}</td><td>{{ number_format((float)$item->taux, 2, ',', ' ') }} %</td><td>{{ $item->par_defaut ? 'Oui' : '-' }}</td><td><a class="btn btn-default btn-sm" href="{{ route('taux_impots.edit', $item->id) }}"><i class="fa fa-pencil"></i> Modifier</a></td></tr>
                @empty
                    <tr><td colspan="4">Aucun taux enregistre.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
@section('breadcrumb')
<ol class="breadcrumb"><li><a href="{{ route('emploi_temps.index') }}">Ressources humaines</a></li><li class="active">Taux d'impots</li></ol>
@endsection
