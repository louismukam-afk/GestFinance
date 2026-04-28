@extends('skeleton')
@section('content')

    <div class="row text-center pad-top">



                <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
                    <div class="div-square">
                        {{--<a href="{{route('ventes')}}" >--}}
                            <i class="fa fa-shopping-cart fa-5x"></i>
                            <h4>Gestion des ventes </h4>
                        </a>
                    </div>

                </div>


        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                {{--<a href="{{route('stocks')}}" >--}}
                    <i class="fa fa-square fa-5x"></i>
                    <h4>Gestion des stocks </h4>
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
                <a href="{{route('etat_budget')}}" >
                    <i class="fa fa-bar-chart-o fa-5x"></i>
                    <h4>Etats Budgetaires Entrées  </h4>
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


    </div>
@endsection
@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li><a href="{{ route('ventes') }}"><strong>Ventes</strong></a></li>
        <li class="active"><strong>{{ $title }}</strong></li>
    </ol>
@endsection