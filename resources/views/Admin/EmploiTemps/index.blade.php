@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="text-primary">{{ $title }}</h3>

    <div class="row text-center pad-top">
        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{ route('programmes_specialites.index') }}">
                    <i class="fa fa-book fa-5x"></i>
                    <h4>Programmes de specialite</h4>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('breadcrumb')
<ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
    <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
    <li class="active"><strong>{{ $title }}</strong></li>
</ol>
@endsection
