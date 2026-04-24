@extends('layouts.app')
@section('content')

    <div class="row text-center pad-top">


        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{route('bon_commande_management')}}" >
                    <i class="fa fa-vimeo-square fa-5x"></i>
                    <h4>Gestion des bons de commandes </h4>
                </a>
            </div>

        </div>

        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
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

        </div>





    </div>


    <div class="row text-center pad-top">
        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{route('specialite_management')}}" >
                    <i class="fa fa-ambulance fa-5x"></i>
                    <h4>Gestion des spécialités </h4>
                </a>
            </div>

        </div>
        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{route('niveau_management')}}" >
                    <i class="fa fa-signal fa-5x"></i>
                    <h4>Gestion des Niveaux</h4>
                </a>
            </div>

        </div>
        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{route('scolarite_management')}}" >
                    <i class="fa fa-android fa-5x"></i>
                    <h4>Gestion des scolarités</h4>
                </a>
            </div>

        </div>



        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{route('caisse_management')}}" >
                    <i class="fa fa-adjust fa-5x"></i>
                    <h4>Gestion des caisses</h4>
                </a>
            </div>

        </div>

    </div>

    <div class="row text-center pad-top">
        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{route('banque_management')}}" >
                    <i class="fa fa-anchor fa-5x"></i>
                    <h4>Gestion des banques</h4>
                </a>
            </div>

        </div>

        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{route('entite_management')}}" >
                    <i class="fa fa-fast-backward fa-5x"></i>
                    <h4>Gestion des entités</h4>
                </a>
            </div>

        </div>

        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{route('frais_management')}}" >
                    <i class="fa fa-hacker-news fa-5x"></i>
                    <h4>Gestion des frais</h4>
                </a>
            </div>

        </div>
        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{route('annee_academique_management')}}" >
                    <i class="fa fa-youtube-square fa-5x"></i>
                    <h4>Gestion des Années académiques</h4>
                </a>
            </div>

        </div>
        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{route('transfert_management')}}" >
                    <i class="fa fa-android fa-5x"></i>
                    <h4>Gestion des transferts</h4>
                </a>
            </div>

        </div>
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