@extends('layouts.app')

@section('content')
    <div class="container">

        <h3 class="mb-4">💸 Pilotage des sorties</h3>

        {{-- 🔍 FILTRES --}}
        <form method="GET" class="row g-3 mb-4">

            <div class="col-md-3">
                <label>Date début</label>
                <input type="date" name="date_debut" class="form-control" value="{{ request('date_debut') }}">
            </div>

            <div class="col-md-3">
                <label>Date fin</label>
                <input type="date" name="date_fin" class="form-control" value="{{ request('date_fin') }}">
            </div>

            <div class="col-md-12 text-center mt-3">
                <button class="btn btn-primary">🔍 Filtrer</button>
            </div>

        </form>

        {{-- TABLE --}}
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
            <tr>
                <th>Date</th>
                <th>Montant</th>
                <th>Caisse</th>
                <th>Utilisateur</th>
                <th>Personnel</th>
            </tr>
            </thead>

            <tbody>
            @foreach($data as $d)
                <tr>
                    <td>{{ $d->date_depense }}</td>
                    <td>{{ number_format($d->montant,0,',',' ') }} FCFA</td>
                    <td>{{ $d->caisses->nom_caisse ?? 'N/A' }}</td>
                    <td>{{ $d->user->name ?? 'N/A' }}</td>
                    <td>{{ $d->personnels->nom ?? 'N/A' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

    </div>
@endsection
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
        <li class="breadcrumb-item">
            <a href="{{ route('etat_sorties.atterrissage') }}"><strong>Atterrissage</strong></a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ route('etat_sorties.decaissements') }}"><strong>Decaissements</strong></a>
        </li>
        <li class="breadcrumb-item active">
            <strong>Global</strong>
        </li>
    </ol>
@endsection
