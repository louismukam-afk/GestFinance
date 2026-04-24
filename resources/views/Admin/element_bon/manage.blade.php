@extends('layouts.app')
@section('content')

    <div class="container">
        <h3>Éléments du Bon : {{ $bon->nom_bon_commande }}</h3>
        <p><strong>Montant du bon :</strong> {{ number_format($bon->montant_total, 0, ',', ' ') }} FCFA</p>

        <div class="alert alert-info">
            Ce bon contient déjà <strong>{{ $elements->count() }}</strong> élément(s).
            Que souhaitez-vous faire ?
        </div>

        <a href="{{ route('element_bon.create', $bon->id) }}" class="btn btn-success">➕ Ajouter d'autres éléments</a>
        <a href="{{ route('element_bon.editForm', $bon->id) }}" class="btn btn-warning">✏️ Modifier les éléments existants</a>
        <a href="{{ route('element_bon.index', $bon->id) }}" class="btn btn-primary">📄 Lister / Imprimer</a>
    </div>

@endsection
