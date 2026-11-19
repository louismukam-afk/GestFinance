@extends('layouts.app')
@section('content')
<div class="container">
    <h3 class="text-primary">{{ $title }}</h3>
    <div style="margin-bottom: 15px;">
        <a href="{{ route('personnels_banques.index') }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Banques</a>
        <a href="{{ route('personnels_banques.create', $banque->id) }}" class="btn btn-primary"><i class="fa fa-user-plus"></i> Affecter des personnels</a>
        <a href="{{ route('personnels_banques.show', array_merge(['banque' => $banque->id], request()->only('recherche'), ['imprimer' => 1])) }}" target="_blank" rel="noopener" class="btn btn-default"><i class="fa fa-print"></i> Imprimer la liste</a>
    </div>
    <form method="GET" action="{{ route('personnels_banques.show', $banque->id) }}" style="margin-bottom: 15px;">
        <label for="rechercheBanque">Personnel ou numero de compte</label>
        <div class="input-group">
            <input id="rechercheBanque" type="search" name="recherche" value="{{ request('recherche') }}" class="form-control">
            <span class="input-group-btn"><button class="btn btn-default" type="submit" title="Rechercher" aria-label="Rechercher"><i class="fa fa-search"></i></button></span>
        </div>
    </form>
    <p><strong>{{ $personnels->count() }}</strong> personnel(s)</p>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead><tr><th>Personnel</th><th>Numero de compte (niveau d'etude)</th><th>Changer de banque</th><th>Retirer</th></tr></thead>
            <tbody>
                @forelse($personnels as $personnel)
                    <tr>
                        <td>{{ $personnel->nom }}</td><td>{{ $personnel->niveau_etude ?: '-' }}</td>
                        <td>
                            <form method="POST" action="{{ route('personnels_banques.update', $personnel->id) }}" style="display: flex; gap: 6px; min-width: 230px;">
                                @csrf @method('PUT')
                                <select name="id_banque" class="form-control" required aria-label="Nouvelle banque pour {{ $personnel->nom }}">
                                    @foreach($banques as $destination)
                                        <option value="{{ $destination->id }}" {{ (int)$destination->id === (int)$banque->id ? 'selected' : '' }}>{{ $destination->nom_banque }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-default" title="Changer de banque" aria-label="Changer de banque pour {{ $personnel->nom }}"><i class="fa fa-exchange"></i></button>
                            </form>
                        </td>
                        <td>
                            <form method="POST" action="{{ route('personnels_banques.destroy', ['banque' => $banque->id, 'personnel' => $personnel->id]) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger" title="Retirer de cette banque" aria-label="Retirer {{ $personnel->nom }} de cette banque" onclick="return confirm('Retirer ce personnel de la banque ?')"><i class="fa fa-unlink"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center">Aucun personnel affecte pour ces criteres.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
@section('breadcrumb')
<ol class="breadcrumb"><li><a href="{{ route('personnels_banques.index') }}">Banques du personnel</a></li><li class="active">{{ $banque->nom_banque }}</li></ol>
@endsection
