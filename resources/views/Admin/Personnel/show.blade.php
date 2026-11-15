@extends('layouts.app')

@php
    $rows = [
        'Nom' => $personnel->nom,
        'Nom biometrie' => $personnel->nom_biometrie,
        'Date de naissance' => $personnel->date_naissance ? \Carbon\Carbon::parse($personnel->date_naissance)->format('d/m/Y') : '-',
        'Lieu de naissance' => $personnel->lieu_naissance,
        'Adresse' => $personnel->adresse,
        'Sexe' => $personnel->sexe,
        'Statut matrimonial' => $personnel->statut_matrimonial,
        'Email' => $personnel->email,
        'Telephone' => $personnel->telephone,
        'Telephone WhatsApp' => $personnel->telephone_whatsapp,
        'Numero CNPS' => $personnel->numero_cnps,
        'Numero contribuable' => $personnel->numero_contribuable,
        'Diplome' => $personnel->diplome,
        'Niveau d etude' => $personnel->niveau_etude,
        'Domaine de formation' => $personnel->domaine_formation,
        'Date de recrutement' => $personnel->date_recrutement ? \Carbon\Carbon::parse($personnel->date_recrutement)->format('d/m/Y') : '-',
        'Nationalite' => $personnel->nationalite,
        'Type personnel' => ucfirst($personnel->type_personnel ?? 'permanent'),
        'Mode horaire' => ucfirst($personnel->mode_horaire ?? 'strict'),
        'Categorie horaire' => str_replace('_', ' ', $personnel->categorie_horaire ?? 'standard'),
        'Horaire travail' => str_replace('_', ' ', $personnel->horaire_travail ?? '-'),
    ];
@endphp

@section('content')
    <div class="container">
        <div class="clearfix" style="margin-bottom: 15px;">
            <h3 class="text-primary pull-left"><i class="fa fa-id-card-o"></i> Fiche d'identification du personnel</h3>
            <div class="pull-right">
                <a href="{{ route('personnels.fiche_pdf', $personnel->id) }}" class="btn btn-danger">
                    <i class="fa fa-file-pdf-o"></i> Imprimer PDF
                </a>
                <a href="{{ route('personnels.edit', $personnel->id) }}" class="btn btn-warning">
                    <i class="fa fa-pencil"></i> Modifier
                </a>
                <a href="{{ route('personnel_management') }}" class="btn btn-default">
                    <i class="fa fa-arrow-left"></i> Retour
                </a>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>{{ $personnel->nom }}</strong>
            </div>
            <div class="panel-body">
                <div class="row">
                    @foreach($rows as $label => $value)
                        <div class="col-md-6">
                            <table class="table table-bordered table-condensed">
                                <tr>
                                    <th style="width: 40%;">{{ $label }}</th>
                                    <td>{{ $value ?: '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection

@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li><a href="{{ route('personnel_management') }}"><strong>Gestion du personnel</strong></a></li>
        <li class="active"><strong>{{ $title }}</strong></li>
    </ol>
@endsection
