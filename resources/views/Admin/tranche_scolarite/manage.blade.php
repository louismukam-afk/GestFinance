@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>Tranches de la Scolarité : {{ $scolarite->id }} </h3>
        <p><strong>Montant total attendu :</strong> {{ number_format($scolarite->montant_total, 0, ',', ' ') }} FCFA</p>

        <div class="alert alert-info">
            Cette scolarité contient déjà <strong>{{ $tranches->count() }}</strong> tranche(s).
            Que souhaitez-vous faire ?
        </div>

        <a href="{{ route('tranche_scolarite.create', $scolarite->id) }}" class="btn btn-success">
            ➕ Ajouter des tranches
        </a>
        <a href="{{ route('tranche_scolarite.editForm', $scolarite->id) }}" class="btn btn-warning">
            ✏️ Modifier les tranches existantes
        </a>
        <a href="{{ route('tranche_scolarite.index', $scolarite->id) }}" class="btn btn-primary">
            📄 Lister / Imprimer
        </a>
    </div>
@endsection
@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li><a href="{{ route('dashboard') }}"><strong>Administration</strong></a></li>
        <li><a href="{{ route('scolarite_management',$scolarite->id ) }}"><strong>Gestion des scolarités</strong></a></li>

        <li class="active"><strong>{{ $title }}</strong></li>
    </ol>
@endsection