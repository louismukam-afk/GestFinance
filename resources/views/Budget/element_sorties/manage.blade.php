@extends('layouts.app')
@section('content')

    <div class="container">
        <h3>
            Gestion des éléments pour la ligne budgétaire :
            <span class="text-primary">{{ $ligne->libelle_ligne_budgetaire_sortie }}</span>
        </h3>

        <div class="alert alert-info">
            Cette ligne contient déjà <strong>{{ $elements->count() }}</strong> élément(s).
            Que souhaitez-vous faire ?
        </div>

        <a href="{{ route('element_sorties.create', $ligne->id) }}" class="btn btn-success">
            ➕ Ajouter des éléments
        </a>
        <a href="{{ route('element_sorties.index', $ligne->id) }}" class="btn btn-primary">
            📄 Lister / Imprimer
        </a>
    </div>

@endsection
@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li><a href="{{ route('budget') }}"><strong>budget</strong></a></li>
        {{--<li><a href="{{ route('budget') }}"><strong>budget</strong></a></li>--}}

        <li class="active"><strong>{{ $title }}</strong></li>
    </ol>
@endsection