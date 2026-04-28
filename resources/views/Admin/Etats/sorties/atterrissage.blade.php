@extends('layouts.app')

@section('content')
    <div class="container">

        {{-- TITRE --}}
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h3>📉 Atterrissage budgétaire - SORTIES</h3>
                <p class="text-muted">Analyse décisionnelle des dépenses</p>
            </div>

            {{-- 🔥 BOUTON IMPRESSION --}}
            <button onclick="window.print()" class="btn btn-dark">
                🖨 Imprimer
            </button>
        </div>

        {{-- FILTRES --}}
        <form method="GET" class="row g-3 mb-4">

            <div class="col-md-3">
                <label>Entité</label>
                <select name="id_entite" class="form-control">
                    <option value="">Toutes</option>
                    @foreach($entites as $e)
                        <option value="{{ $e->id }}" {{ request('id_entite') == $e->id ? 'selected' : '' }}>
                            {{ $e->nom_entite }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label>Année académique</label>
                <select name="id_annee_academique" class="form-control">
                    <option value="">Toutes</option>
                    @foreach($annees as $a)
                        <option value="{{ $a->id }}" {{ request('id_annee_academique') == $a->id ? 'selected' : '' }}>
                            {{ $a->nom }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label>Date début</label>
                <input type="date" name="date_debut" value="{{ request('date_debut') }}" class="form-control">
            </div>

            <div class="col-md-3">
                <label>Budget</label>
                <select name="id_budget" class="form-control">
                    <option value="">Tous</option>
                    @foreach($budgets as $b)
                        <option value="{{ $b->id }}" {{ request('id_budget') == $b->id ? 'selected' : '' }}>
                            {{ $b->libelle_ligne_budget }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label>Date fin</label>
                <input type="date" name="date_fin" value="{{ request('date_fin') }}" class="form-control">
            </div>

            <div class="col-md-12 text-center mt-3">
                <button class="btn btn-success">🔍 Filtrer</button>
                <a href="{{ route('etat_sorties.pdf', request()->query()) }}" class="btn btn-danger">
                    PDF
                </a>
            </div>
        </form>

        {{-- SOLDE GLOBAL --}}
        <div class="alert alert-info">
            💰 <strong>Disponibilité globale :</strong>
            {{ number_format($soldeGlobal,0,',',' ') }} FCFA
        </div>

        {{-- TABLE PAR ENTITÉ --}}
        @foreach($etatGrouped as $entite => $lignes)

            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    🏢 {{ $entite }}
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered">

                        <thead class="table-dark">
                        <tr>
                            <th>Budget</th>
                            <th>Ligne</th>
                            <th>Donnée</th>
                            <th class="text-end">Prévu</th>
                            <th class="text-end">Dépensé</th>
                            <th class="text-end">Reste</th>
                            <th class="text-end">Dispo caisse</th>
                            <th class="text-center">Décision</th>
                        </tr>
                        </thead>

                        <tbody>

                        @foreach($lignes as $e)

                            @php
                                $decision = '';
                                $badge = '';

                                if($e['reste'] <= 0){
                                    $decision = 'Budget épuisé';
                                    $badge = 'secondary';
                                }
                                elseif($e['solde'] <= 0){
                                    $decision = 'Bloqué (pas de trésorerie)';
                                    $badge = 'danger';
                                }
                                elseif($e['solde'] < $e['reste']){
                                    $decision = 'Financement partiel';
                                    $badge = 'warning';
                                }
                                else{
                                    $decision = 'Finançable';
                                    $badge = 'success';
                                }
                            @endphp

                            <tr>
                                <td>{{ $e['budget'] }}</td>
                                <td>{{ $e['ligne'] }}</td>
                                <td>{{ $e['donnee'] }}</td>

                                <td class="text-end">{{ number_format($e['prevu'],0,',',' ') }}</td>
                                <td class="text-end text-danger">{{ number_format($e['depense'],0,',',' ') }}</td>
                                <td class="text-end text-primary">{{ number_format($e['reste'],0,',',' ') }}</td>
                                <td class="text-end text-success">{{ number_format($e['solde'],0,',',' ') }}</td>

                                {{-- 🔥 COLONNE DECISION --}}
                                <td class="text-center">
                                <span class="badge bg-{{ $badge }}">
                                    {{ $decision }}
                                </span>
                                </td>
                            </tr>

                        @endforeach

                        </tbody>

                    </table>
                </div>
            </div>

        @endforeach

    </div>
@endsection


{{-- 🔥 STYLE IMPRESSION --}}
@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent; padding: 4px 10px">
        <li class="breadcrumb-item">
            <a href="{{ route('home') }}"><strong>Accueil</strong></a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ route('etat_sorties.index') }}"><strong>Etats budgetaires sorties</strong></a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ route('etat_sorties.pilotage') }}"><strong>Pilotage</strong></a>
        </li>
        <li class="breadcrumb-item active">
            <strong>Atterrissage</strong>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ route('etat_sorties.decaissements') }}"><strong>Decaissements</strong></a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ route('etat_sorties.global') }}"><strong>Global</strong></a>
        </li>
    </ol>
@endsection

@section('styles')
    <style>
        @media print {
            button, form, .btn {
                display: none !important;
            }
        }
    </style>
@endsection
