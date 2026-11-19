@extends('layouts.app')
@section('content')
<div class="container">
    <h3 class="text-primary">{{ $title }}</h3>
    <a class="btn btn-default" href="{{ route('emploi_temps.index') }}"><i class="fa fa-arrow-left"></i> Ressources humaines</a>
    <a class="btn btn-default" href="{{ route('virements_vacataires.historique') }}"><i class="fa fa-history"></i> Historique des ordres</a>
    <h4>Nouvel etat par import</h4>
    <div class="table-responsive"><table class="table table-bordered table-striped">
        <thead><tr><th>Import</th><th>Periode</th><th></th></tr></thead>
        <tbody>@forelse($imports as $import)
            <tr><td>#{{ $import->id }} - {{ $import->libelle }}</td><td>{{ $import->date_debut->format('d/m/Y') }} au {{ $import->date_fin->format('d/m/Y') }}</td><td><a class="btn btn-primary btn-sm" href="{{ route('virements_vacataires.create', $import) }}"><i class="fa fa-plus"></i> Preparer</a></td></tr>
        @empty<tr><td colspan="3">Aucun import consolide.</td></tr>@endforelse</tbody>
    </table></div>
</div>
@endsection
