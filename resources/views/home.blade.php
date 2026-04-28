@extends('layouts.app')
@section('content')

    <div class="row text-center pad-top">



        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{route('budget')}}" >
                <i class="fa fa-shopping-cart fa-5x"></i>
                <h4>Gestions budgétaires </h4>
                </a>
            </div>

        </div>


        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{route('etats.index')}}" >
                    <i class="fa fa-bar-chart-o fa-5x"></i>
                    <h4>Etats Budgetaires Entrées  </h4>
                </a>
            </div>

        </div>

        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                {{--<a href="{{route('index_rapport')}}" >--}}
                <i class="fa fa-book fa-5x"></i>
                <h4>Rapports </h4>
                </a>
            </div>

        </div>


        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{route('dashboard')}}" >
                    <i class="fa fa-cog fa-5x"></i>
                    <h4>Administration </h4>
                </a>
            </div>

        </div>


    </div>
    <li>
    </li>

    <div class="row text-center pad-top">

        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{route('etudiant')}}">
                    <i class="fa fa-maxcdn fa-5x"></i>
                    <h4>Gestion des étudiants </h4>
                </a>
            </div>

        </div>

        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{route('etat_sorties.index')}}" >
                    <i class="fa fa-bar-chart-o fa-5x"></i>
                    <h4>Etats Budgetaires sorties  </h4>
                </a>
            </div>

        </div>

        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{route('etat_sorties.etat_caisse')}}" >
                    <i class="fa fa-money fa-5x"></i>
                    <h4>Etats de caisse</h4>
                </a>
            </div>

        </div>

        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{route('etat_sorties.mon_etat_caisse')}}" >
                    <i class="fa fa-calculator fa-5x"></i>
                    <h4>Mon etat de caisse</h4>
                </a>
            </div>

        </div>

        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{route('etat_sorties.disponibilite_caisses')}}" >
                    <i class="fa fa-money fa-5x"></i>
                    <h4>Disponibilite des caisses</h4>
                </a>
            </div>

        </div>

        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{route('retour_caisses.create')}}" >
                    <i class="fa fa-reply fa-5x"></i>
                    <h4>Retour en caisse</h4>
                </a>
            </div>

        </div>

        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{ route('mes_bons.attente') }}">
                    <i class="fa fa-file-text-o fa-5x"></i>
                    <h4>Mes bons en attente</h4>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{ route('mes_bons.valides') }}">
                    <i class="fa fa-check-square-o fa-5x"></i>
                    <h4>Mes bons valides</h4>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{route('personnel')}}">
                    <i class="fa fa-stack-overflow fa-5x"></i>
                    <h4>Gestion du personnel </h4>
                </a>
            </div>

        </div>

        @if(auth()->user()?->isSuperAdmin())
            <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
                <div class="div-square">
                    <a href="{{ route('access.index') }}">
                        <i class="fa fa-lock fa-5x"></i>
                        <h4>Roles et permissions</h4>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
                <div class="div-square">
                    <a href="{{ route('audit.index') }}">
                        <i class="fa fa-history fa-5x"></i>
                        <h4>Journal des operations</h4>
                    </a>
                </div>
            </div>
        @endif



    </div>

@endsection
@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        {{--<li class="active"><strong>{{ $title }}</strong></li>--}}
    </ol>
@endsection
