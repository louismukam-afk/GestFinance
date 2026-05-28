@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="text-primary">{{ $title }}</h3>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning">{{ session('warning') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <strong>Erreur :</strong> {{ $errors->first() }}
            </div>
        @endif

        @if(session('import_errors') && count(session('import_errors')))
            <div class="alert alert-info">
                <strong>Details des lignes ignorees</strong>
                <ul class="mb-0">
                    @foreach(session('import_errors') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>Importer les personnels depuis Excel</strong>
            </div>
            <div class="panel-body">
                <p>
                    Telecharge le modele, renseigne les informations des personnels, puis importe le fichier.
                    Les colonnes du template correspondent au modele Personnel et a la table personnels.
                </p>

                <div class="mb-3" style="margin-bottom: 15px;">
                    <a href="{{ route('personnels.import.template') }}" class="btn btn-success">
                        Telecharger le template Excel
                    </a>
                    <a href="{{ route('personnel_management') }}" class="btn btn-default">
                        Retour a la liste
                    </a>
                </div>

                <form action="{{ route('personnels.import.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="fichier">Fichier Excel</label>
                        <input type="file" name="fichier" id="fichier" class="form-control" accept=".xlsx,.xls,.csv" required>
                        <small class="text-muted">Formats acceptes : xlsx, xls, csv. Taille max : 10 Mo.</small>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        Importer les personnels
                    </button>
                </form>
            </div>
        </div>

        <div class="panel panel-info">
            <div class="panel-heading">
                <strong>Colonnes attendues dans le fichier</strong>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>Colonne</th>
                            <th>Observation</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr><td>nom</td><td>Obligatoire</td></tr>
                        <tr><td>date_naissance</td><td>Obligatoire, format conseille : AAAA-MM-JJ</td></tr>
                        <tr><td>lieu_naissance</td><td>Obligatoire</td></tr>
                        <tr><td>adresse</td><td>Obligatoire</td></tr>
                        <tr><td>sexe</td><td>Obligatoire : Masculin, Feminin ou Autre</td></tr>
                        <tr><td>statut_matrimonial</td><td>Obligatoire : Celibataire, Marie(e), Divorce(e), Veuf(ve)</td></tr>
                        <tr><td>telephone</td><td>Obligatoire</td></tr>
                        <tr><td>date_recrutement</td><td>Obligatoire, format conseille : AAAA-MM-JJ</td></tr>
                        <tr><td>nationalite</td><td>Obligatoire</td></tr>
                        <tr><td>email</td><td>Optionnel, utilise pour eviter les doublons</td></tr>
                        <tr><td>type_personnel</td><td>Optionnel : permanent ou vacataire. Defaut : permanent</td></tr>
                        <tr><td>mode_horaire</td><td>Optionnel : strict ou souple. Defaut : strict</td></tr>
                        <tr><td>categorie_horaire</td><td>Optionnel : standard, conseil_administration, chef_service, coordination</td></tr>
                        <tr><td>horaire_travail</td><td>Optionnel : permanent_jour, permanent_soir, vacataire_jour, vacataire_soir</td></tr>
                        <tr><td>autres colonnes</td><td>Optionnelles, conformes au modele Personnel</td></tr>
                        </tbody>
                    </table>
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
