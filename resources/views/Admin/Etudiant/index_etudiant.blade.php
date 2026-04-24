@extends('layouts.app')
@section('content')

    <div class="row text-center pad-top">


        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{route('etudiant_management')}}" >
                    <i class="fa fa-vimeo-square fa-5x"></i>
                    <h4>Enregistrer un étudiant </h4>
                </a>
            </div>

        </div>

      {{--  <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{route('etat_bons.index')}}" >
                    <i class="fa fa-vimeo-square fa-5x"></i>
                    <h4>Listes des bons de commandes </h4>
                </a>
            </div>

        </div>


        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{route('cycle_management')}}" >
                    <i class="fa fa-circle fa-5x"></i>
                    <h4>Gestion des cycles </h4>
                </a>
            </div>

        </div>

        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{route('filiere_management')}}" >
                    <i class="fa fa-desktop fa-5x"></i>
                    <h4>Gestion des filières </h4>
                </a>
            </div>

        </div>--}}

    </div>








@endsection
@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li><a href="{{ route('budget') }}"><strong>Gestion des Budgets</strong></a></li>
        {{--<li><a href="{{ route('budget') }}"><strong>budget</strong></a></li>--}}
        <li class="active"><strong>{{ $title }}</strong></li>
    </ol>
@endsection