@extends('layouts.app')
@section('content')

    <div class="row text-center pad-top">


        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{route('personnel_management')}}" >
                    <i class="fa fa-pagelines fa-5x"></i>
                    <h4>Enregistrer un personnel </h4>
                </a>
            </div>

        </div>
        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{route('fonction_management')}}" >
                    <i class="fa fa-file-archive-o fa-5x"></i>
                    <h4>Enregistrer une fonction </h4>
                </a>
            </div>

        </div>

    </div>








@endsection
@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        {{--<li><a href="{{ route('') }}"><strong>Gestion des Budgets</strong></a></li>--}}
        {{--<li><a href="{{ route('budget') }}"><strong>budget</strong></a></li>--}}
        <li class="active"><strong>{{ $title }}</strong></li>
    </ol>
@endsection