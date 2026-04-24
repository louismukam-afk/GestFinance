{{--
@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>⚙️ Gestion des données ligne budgétaire sortie :
            <span class="text-primary">{{ $donnee->donnee_ligne_budgetaire_sortie }}</span>
        </h3>

        <a href="{{ route('donnee_ligne_sorties.index', $donnee->id) }}" class="btn btn-primary">📄 Voir les données</a>
    </div>
@endsection
--}}
@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>
            Gestion des données ligne budgétaire sortie :
            <span class="text-primary">{{ $donnee->donnee_ligne_budgetaire_sortie }}</span>
        </h3>

        <div class="alert alert-info">
            Cette donnée contient déjà <strong>{{ $lignes->count() }}</strong> enregistrements.
            Que souhaitez-vous faire ?
        </div>

        <a href="{{ route('donnee_ligne_sorties.create', $donnee->id) }}" class="btn btn-success">➕ Ajouter des données ligne budgétaire</a>
        <a href="{{ route('donnee_ligne_sorties.index', $donnee->id) }}" class="btn btn-primary">📄 Lister / Imprimer</a>
    </div>
@endsection
@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li><a href="{{ route('budget') }}"><strong>budget</strong></a></li>
        <li><a href="{{ route('donnee_sorties.index',$donnee->id)}}"><strong>Donnée Budgétaire sorties </strong></a></li>
        <li><a href="{{ route('donnee_ligne_sorties.index',$donnee->id)}}"><strong>Donnée </strong></a></li>

        <li class="active"><strong>{{ $title }}</strong></li>
    </ol>
@endsection