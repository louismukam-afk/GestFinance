@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>
            Données ligne budgétaire liées à :
            <span class="text-primary">{{ $donnee->donnee_ligne_budgetaire_entree }}</span>
        </h3>

        <div class="alert alert-info">
            Cette donnée contient déjà <strong>{{ $lignes->count() }}</strong> enregistrements.
            Que souhaitez-vous faire ?
        </div>

        <a href="{{ route('donnee_ligne_entrees.create', $donnee->id) }}" class="btn btn-success">➕ Ajouter des données ligne budgétaire</a>
        <a href="{{ route('donnee_ligne_entrees.index', $donnee->id) }}" class="btn btn-primary">📄 Lister / Imprimer</a>
    </div>
@endsection

@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li><a href="{{ route('budget') }}"><strong>budget</strong></a></li>
        <li><a href="{{ route('donnee_entrees.index',$donnee->id)}}"><strong>Données Budgétaires entrées </strong></a></li>
        <li><a href="{{ route('donnee_ligne_entrees.index',$donnee->id)}}"><strong>Données lignes budgétaires entrées </strong></a></li>

        <li class="active"><strong>{{ $title }}</strong></li>
    </ol>
@endsection