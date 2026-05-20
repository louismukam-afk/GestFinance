@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="text-primary">{{ $title }}</h3>

    <div class="row text-center pad-top">
        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{ route('personnel_management') }}">
                    <i class="fa fa-users fa-5x"></i>
                    <h4>Personnel</h4>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{ route('matieres.index') }}">
                    <i class="fa fa-book fa-5x"></i>
                    <h4>Matieres</h4>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{ route('programmes_specialites.index') }}">
                    <i class="fa fa-book fa-5x"></i>
                    <h4>Programmes de specialite</h4>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{ route('cours_enseignants.index') }}">
                    <i class="fa fa-calendar fa-5x"></i>
                    <h4>Cours enseignants</h4>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{ route('cours_enseignants.emploi_specialite') }}">
                    <i class="fa fa-table fa-5x"></i>
                    <h4>Emplois specialites</h4>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{ route('cours_enseignants.emploi_enseignant') }}">
                    <i class="fa fa-user fa-5x"></i>
                    <h4>Emplois enseignants</h4>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{ route('emplois_permanents.index') }}">
                    <i class="fa fa-briefcase fa-5x"></i>
                    <h4>Emplois permanents</h4>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{ route('cours_enseignants.volumes_specialite') }}">
                    <i class="fa fa-bar-chart fa-5x"></i>
                    <h4>Volumes horaires</h4>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{ route('biometrie_heures.index') }}">
                    <i class="fa fa-calculator fa-5x"></i>
                    <h4>Decompte heures</h4>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{ route('biometrie_permanents.index') }}">
                    <i class="fa fa-id-card fa-5x"></i>
                    <h4>Biometrie permanents</h4>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{ route('salles.index') }}">
                    <i class="fa fa-building fa-5x"></i>
                    <h4>Salles</h4>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{ route('plages_horaires.index') }}">
                    <i class="fa fa-clock-o fa-5x"></i>
                    <h4>Plages horaires</h4>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{ route('taux_horaires.index') }}">
                    <i class="fa fa-money fa-5x"></i>
                    <h4>Taux horaires</h4>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{ route('salaires_permanents.index') }}">
                    <i class="fa fa-credit-card fa-5x"></i>
                    <h4>Salaires permanents</h4>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{ route('paie_permanents.index') }}">
                    <i class="fa fa-file-text fa-5x"></i>
                    <h4>Paie permanents</h4>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{ route('paie_permanents.index') }}#bulletins-paie">
                    <i class="fa fa-list-alt fa-5x"></i>
                    <h4>Bulletins de paie</h4>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{ route('paie_permanents.index') }}#etats-paie">
                    <i class="fa fa-table fa-5x"></i>
                    <h4>Etats de paie</h4>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{ route('paie_permanents.index') }}#acomptes-sanctions">
                    <i class="fa fa-minus-circle fa-5x"></i>
                    <h4>Acomptes et sanctions</h4>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{ route('discipline_personnels.index') }}">
                    <i class="fa fa-exclamation-triangle fa-5x"></i>
                    <h4>Discipline personnel</h4>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <div class="div-square">
                <a href="{{ route('personnel_entites.index') }}">
                    <i class="fa fa-sitemap fa-5x"></i>
                    <h4>Affectation entites</h4>
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
