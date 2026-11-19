@extends('layouts.app')
@section('content')
<div class="container">
    <h3 class="text-primary">{{ $title }}</h3>
    <div style="margin-bottom: 15px;">
        <a href="{{ route('emploi_temps.index') }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Ressources humaines</a>
        <a href="{{ route('banque_management') }}" class="btn btn-default"><i class="fa fa-university"></i> Gestion des banques</a>
    </div>
    <p>Personnels sans banque : <strong>{{ $sansBanque }}</strong></p>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead><tr><th>Banque</th><th>Code</th><th>Personnels affectes</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($banques as $banque)
                    <tr>
                        <td>{{ $banque->nom_banque }}</td><td>{{ $banque->code }}</td><td>{{ $banque->personnels_count }}</td>
                        <td>
                            <a href="{{ route('personnels_banques.create', $banque->id) }}" class="btn btn-primary btn-sm"><i class="fa fa-user-plus"></i> Affecter des personnels</a>
                            <a href="{{ route('personnels_banques.show', $banque->id) }}" class="btn btn-default btn-sm"><i class="fa fa-list"></i> Liste du personnel</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center">Aucune banque enregistree.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
@section('breadcrumb')
<ol class="breadcrumb"><li><a href="{{ route('emploi_temps.index') }}">Ressources humaines</a></li><li class="active">Banques du personnel</li></ol>
@endsection
