{{--@extends('layouts.app')

@section('title', 'Reporting')

@section('content')

    <div class="container">
        <h3>📊 Reporting des décaissements</h3>

        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Bon</th>
                <th>Total</th>
                <th>Financé</th>
                <th>Reste</th>
                <th>Statut</th>
            </tr>
            </thead>
            <tbody>

            @foreach($bons as $b)

                @php
                    $fin = $b->decaissements->sum('montant');
                    $reste = $b->montant_total - $fin;
                @endphp

                <tr>
                    <td>{{ $b->nom_bon_commande }}</td>
                    <td>{{ $b->montant_total }}</td>
                    <td>{{ $fin }}</td>
                    <td>{{ $reste }}</td>
                    <td>
                        @if($reste == 0)
                            <span class="badge bg-success">Financé</span>
                        @elseif($fin > 0)
                            <span class="badge bg-warning">Partiel</span>
                        @else
                            <span class="badge bg-danger">Non financé</span>
                        @endif
                    </td>
                </tr>

            @endforeach

            </tbody>
        </table>
    </div>

@endsection--}}

@extends('layouts.app')

@section('title', 'Reporting')

@section('content')

    <div class="container">

        <h2 class="text-center mb-4">📊 Reporting des Décaissements</h2>

        <!-- 🔍 FILTRES -->
        <form method="GET" action="{{ route('decaissements.reporting') }}" class="row g-3 mb-4">

            <div class="col-md-3">
                <label>Date début</label>
                <input type="date" name="date_debut" class="form-control" value="{{ request('date_debut') }}">
            </div>

            <div class="col-md-3">
                <label>Date fin</label>
                <input type="date" name="date_fin" class="form-control" value="{{ request('date_fin') }}">
            </div>

            <div class="col-md-3">
                <label>Personnel</label>
                <select name="id_personnel" class="form-control">
                    <option value="">-- Tous --</option>
                    @foreach($personnels as $p)
                        <option value="{{ $p->id }}" {{ request('id_personnel') == $p->id ? 'selected' : '' }}>
                            {{ $p->nom }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label>Utilisateur</label>
                <select name="id_user" class="form-control">
                    <option value="">-- Tous --</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('id_user') == $u->id ? 'selected' : '' }}>
                            {{ $u->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label>Caisse (sortie)</label>
                <select name="id_caisse" class="form-control">
                    <option value="">-- Toutes --</option>
                    @foreach($caisses as $c)
                        <option value="{{ $c->id }}" {{ request('id_caisse') == $c->id ? 'selected' : '' }}>
                            {{ $c->nom_caisse }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <button class="btn btn-primary">🔍 Rechercher</button>
                <a href="{{ route('decaissements.reporting') }}" class="btn btn-secondary">♻ Réinitialiser</a>
            </div>

        </form>

        <!-- 🔹 EXPORT -->
        <div class="mb-3">
            <a href="{{ route('decaissements.pdf', request()->all()) }}" class="btn btn-danger">
                📄 Export PDF
            </a>
        </div>

        <!-- 🔹 TABLE -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover">

                <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Bon</th>
                    <th>Montant Total</th>
                    <th>Financé</th>
                    <th>Reste</th>
                    <th>Statut</th>
                    <th>Personnel</th>
                    <th>Utilisateur</th>
                </tr>
                </thead>

                <tbody>
                @forelse($bons as $i => $b)
                    <tr>
                        <td>{{ $i+1 }}</td>

                        <td>{{ $b->nom_bon_commande }}</td>

                        <td>{{ number_format($b->montant_total,0,',',' ') }} FCFA</td>

                        <td>{{ number_format($b->total_decaisse,0,',',' ') }} FCFA</td>

                        <td>
                        <span class="badge {{ $b->reste > 0 ? 'bg-warning' : 'bg-success' }}">
                            {{ number_format($b->reste,0,',',' ') }}
                        </span>
                        </td>

                        <td>
                            @if($b->reste == 0)
                                <span class="badge bg-success">Financé</span>

                            @elseif($b->reste < $b->montant_total)
                                <span class="badge bg-warning">Partiel</span>

                            @else
                                <span class="badge bg-danger">Non financé</span>
                            @endif
                        </td>
                        <td>{{ $b->personnels->nom ?? 'N/A' }}</td>

                        <td>{{ $b->user->name ?? 'N/A' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">
                            ⚠ Aucun résultat trouvé
                        </td>
                    </tr>
                @endforelse
                </tbody>

            </table>
        </div>

    </div>

@endsection
@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li><a href="{{ route('budget') }}"><strong>budget</strong></a></li>
        <li><a href="{{ route('decaissements.index') }}"><strong>Decaissements</strong></a></li>
        {{--<li><a href="{{ route('budget') }}"><strong>budget</strong></a></li>--}}

        {{--<li class="active"><strong>{{ $title }}</strong></li>--}}
    </ol>
@endsection
