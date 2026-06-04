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

            <div class="col-md-3">
                <label>Caisse</label>
                <select name="id_caisse" class="form-control">
                    <option value="">Toutes</option>
                    @foreach($caisses as $caisse)
                        <option value="{{ $caisse->id }}" {{ request('id_caisse') == $caisse->id ? 'selected' : '' }}>
                            {{ $caisse->nom_caisse }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label>Banque</label>
                <select name="id_banque" class="form-control">
                    <option value="">Toutes</option>
                    @foreach($banques as $banque)
                        <option value="{{ $banque->id }}" {{ request('id_banque') == $banque->id ? 'selected' : '' }}>
                            {{ $banque->nom_banque }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-12 text-center mt-3">
                <button class="btn btn-primary">Filtrer</button>
                <a href="{{ route('etat_sorties.pilotage') }}" class="btn btn-secondary">Reset</a>
            </div>

        </form>

        {{-- TABLE --}}
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
            <tr>
                <th>Date</th>
                <th>Titre du bon</th>
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
                    <td>{{ optional($d->bon)->nom_bon_commande ?? $d->motif ?? 'N/A' }}</td>
                    <td>{{ number_format($d->montant,0,',',' ') }} FCFA</td>
                    <td>
                        @if($d->id_banque)
                            Banque - {{ optional($d->banques)->nom_banque ?? 'N/A' }}
                        @else
                            Caisse - {{ optional($d->caisses)->nom_caisse ?? 'N/A' }}
                        @endif
                    </td>
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
