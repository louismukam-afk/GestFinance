@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>✏️ Modifier une tranche</h3>

        <form method="POST" action="{{ route('tranche_scolarite.update', $tranche->id) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Nom tranche</label>
                <input type="text" name="nom_tranche" class="form-control" value="{{ $tranche->nom_tranche }}" required>
            </div>

            <div class="form-group">
                <label>Date limite</label>
                <input type="date" name="date_limite" class="form-control" value="{{ $tranche->date_limite }}" required>
            </div>

            <div class="form-group">
                <label>Montant</label>
                <input type="number" step="0.01" name="montant_tranche" class="form-control" value="{{ $tranche->montant_tranche }}" required>
            </div>

            <button type="submit" class="btn btn-primary">✔ Mettre à jour</button>
            <a href="{{ route('tranche_scolarite.index', $tranche->id_scolarite) }}" class="btn btn-secondary">↩ Retour</a>
        </form>
    </div>
@endsection
@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li><a href="{{ route('dashboard') }}"><strong>Administration</strong></a></li>


        <li class="active"><strong>{{ $title }}</strong></li>
    </ol>
@endsection