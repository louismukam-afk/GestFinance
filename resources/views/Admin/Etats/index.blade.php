@extends('layouts.app')

@section('content')
    <div class="container">

        {{-- Titre --}}
        <div class="mb-4">
            <h3>📊 États comptables et budgétaires</h3>
            <p class="text-muted">
                Accédez aux différents états financiers, budgétaires et situations analytiques.
            </p>
        </div>

        {{-- Cartes de navigation --}}
        <div class="row g-4">

            {{-- Pilotage budgétaire --}}
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">📊 Pilotage budgétaire</h5>
                        <p class="card-text flex-grow-1">
                            Accéder à l’interface de pilotage des recettes :
                            filtres par année, entité et caisse.
                        </p>
                        <a href="{{ route('etat_budget') }}"
                           class="btn btn-primary mt-auto">
                            Accéder
                        </a>
                    </div>
                </div>
            </div>

            {{-- Atterrissage budgétaire --}}
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">📉 Atterrissage budgétaire</h5>
                        <p class="card-text flex-grow-1">
                            Suivi du prévu, facturé, encaissé et reste à recouvrer
                            par budget et ligne budgétaire.
                        </p>
                        <a href="{{ route('etat_atterrissage_budgetaire') }}"
                           class="btn btn-success mt-auto">
                            Accéder
                        </a>
                    </div>
                </div>
            </div>

            {{-- Factures & règlements --}}
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">🧾 Factures & règlements</h5>
                        <p class="card-text flex-grow-1">
                            Analyse détaillée des factures émises et des
                            règlements encaissés, avec regroupements comptables.
                        </p>
                        <a href="{{ route('etat_factures_reglements') }}"
                           class="btn btn-info mt-auto">
                            Accéder
                        </a>
                    </div>
                </div>
            </div>

            {{-- Situation étudiant --}}
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">🎓 Situation d’un étudiant</h5>
                        <p class="card-text flex-grow-1">
                            Consultation de la situation financière individuelle :
                            factures, paiements et soldes.
                        </p>
                        {{-- L’ID étudiant sera fourni depuis une liste ou un formulaire --}}
                        <a href="{{ route('etat_situation_etudiant', ['etudiant' => 1]) }}"
                           class="btn btn-warning mt-auto">
                            Accéder
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
            <strong>États comptables</strong>
        </li>
    </ol>
@endsection
