@extends('layouts.app')
@section('content')

    <div class="row text-center pad-top">


        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{route('budgets.index')}}" >
                    <i class="fa fa-vimeo-square fa-5x"></i>
                    <h4> </h4>
                </a>
            </div>

        </div>
        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{route('ligne_budgetaire_entrees.index')}}" >
                    <i class="fa fa-behance fa-5x"></i>
                    <h4>Ligne budgétaire Entrée </h4>
                </a>
            </div>

        </div>
        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{route('ligne_budgetaire_sorties.index')}}" >
                    <i class="fa fa-maxcdn fa-5x"></i>
                    <h4>Ligne budgétaire sortie </h4>
                </a>
            </div>

        </div>

        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{route('donnee_sorties.index')}}" >
                    <i class="fa fa-database fa-5x"></i>
                    <h4>Données budgétaires sorties </h4>
                </a>
            </div>

        </div>

        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{route('donnee_entrees.index')}}" >
                    <i class="fa fa-laptop fa-5x"></i>
                    <h4>Données budgétaires Entrées </h4>
                </a>
            </div>

        </div>

        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{route('decaissements.index')}}" >
                    <i class="fa fa-vimeo-square fa-5x"></i>
                    <h4>Gestion des decaissements </h4>
                </a>
            </div>

        </div>

    </div>
@endsection
@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li><a href="{{ route('budget') }}"><strong>budget</strong></a></li>
        {{--<li><a href="{{ route('budget') }}"><strong>budget</strong></a></li>--}}

        {{--<li class="active"><strong>{{ $title }}</strong></li>--}}
    </ol>
@endsection