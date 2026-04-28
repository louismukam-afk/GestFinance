@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Pilotage des sorties</h5>
                        <p class="card-text flex-grow-1">
                            Suivi global des decaissements par caisse et periode.
                        </p>
                        <a href="{{ route('etat_sorties.pilotage') }}" class="btn btn-primary mt-auto">
                            Acceder
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Atterrissage sorties</h5>
                        <p class="card-text flex-grow-1">
                            Prevu, depense, reste et disponibilite caisse.
                        </p>
                        <a href="{{ route('etat_sorties.atterrissage') }}" class="btn btn-success mt-auto">
                            Acceder
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Decaissements</h5>
                        <p class="card-text flex-grow-1">
                            Liste detaillee des decaissements par filtres.
                        </p>
                        <a href="{{ route('etat_sorties.decaissements') }}" class="btn btn-info mt-auto">
                            Acceder
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Atterrissage global</h5>
                        <p class="card-text flex-grow-1">
                            Visibilite consolidee sur toutes les entrees et sorties.
                        </p>
                        <a href="{{ route('etat_sorties.global') }}" class="btn btn-info mt-auto">
                            Acceder
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Etat des caisses</h5>
                        <p class="card-text flex-grow-1">
                            Synthese des entrees, sorties et soldes par caisse, utilisateur et periode.
                        </p>
                        <a href="{{ route('etat_sorties.etat_caisse') }}" class="btn btn-primary mt-auto">
                            Acceder
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Mon etat de caisse</h5>
                        <p class="card-text flex-grow-1">
                            Etat de caisse de l'utilisateur connecte pour la cloture journaliere.
                        </p>
                        <a href="{{ route('etat_sorties.mon_etat_caisse') }}" class="btn btn-success mt-auto">
                            Acceder
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Disponibilite des caisses</h5>
                        <p class="card-text flex-grow-1">
                            Montant exact disponible dans chaque caisse avant et apres transfert.
                        </p>
                        <a href="{{ route('etat_sorties.disponibilite_caisses') }}" class="btn btn-dark mt-auto">
                            Acceder
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Retour en caisse</h5>
                        <p class="card-text flex-grow-1">
                            Retourner en caisse centrale les montants non utilises sur un bon finance.
                        </p>
                        <a href="{{ route('retour_caisses.create') }}" class="btn btn-warning mt-auto">
                            Nouveau retour
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent; padding: 4px 10px">
        <li class="breadcrumb-item">
            <a href="{{ route('home') }}"><strong>Accueil</strong></a>
        </li>
        <li class="breadcrumb-item active">
            <a href="{{ route('etat_sorties.index') }}"><strong>Etats budgetaires sorties</strong></a>
        </li>
    </ol>
@endsection
