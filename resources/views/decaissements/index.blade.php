{{--
@extends('layouts.app')

@section('content')

    <div class="container">

        <h3>💰 Gestion des décaissements</h3>

        <a href="{{ route('decaissements.reporting') }}" class="btn btn-info">
            📊 Reporting
        </a>

        <table class="table table-bordered mt-3">
            <thead>
            <tr>
                <th>Bon</th>
                <th>Montant total</th>
                <th>Décaissé</th>
                <th>Reste</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
            </thead>

            <tbody>
            @foreach($bons as $b)
                <tr>
                    <td>{{ $b->nom_bon_commande }}</td>
                    <td>{{ number_format($b->montant_total) }}</td>
                    <td>{{ number_format($b->total_decaisse) }}</td>
                    <td>{{ number_format($b->reste) }}</td>
                    <td>
                        @if($b->reste > 0)
                            <span class="badge bg-warning">En cours</span>
                        @else
                            <span class="badge bg-success">Réalisé</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('decaissements.create', $b->id) }}" class="btn btn-primary btn-sm">
                            💸 Financer
                        </a>

                        <form action="{{ route('decaissements.destroy', $b->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm">🗑 Supprimer</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>

        </table>

    </div>

@endsection

@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li><a href="{{ route('budget') }}"><strong>budget</strong></a></li>
        --}}
{{--<li><a href="{{ route('budget') }}"><strong>budget</strong></a></li>--}}{{--


        --}}
{{--<li class="active"><strong>{{ $title }}</strong></li>--}}{{--

    </ol>
@endsection
--}}
@extends('layouts.app')

@section('content')

    <div class="container">

        <h2 class="text-center mb-4">Bons valides a financer</h2>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <!-- 🔍 FILTRES -->
        <form method="GET" action="{{ route('decaissements.index') }}" class="row g-3 mb-4">

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

            <div class="col-md-12 text-center mt-3">
                <button class="btn btn-primary">🔍 Rechercher</button>
                <a href="{{ route('decaissements.index') }}" class="btn btn-secondary">♻ Réinitialiser</a>
            </div>

        </form>

        <!-- ACTIONS -->
        <div class="mb-3">
            <a href="{{ route('decaissements.reporting') }}" class="btn btn-info">Reporting</a>
            <a href="{{ route('decaissements.etat_realises') }}" class="btn btn-success">Bons realises</a>
            <a href="{{ route('decaissements.etat_non_finances') }}" class="btn btn-warning">Bons non finances</a>
        </div>

        <!-- TABLE -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover">

                <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Bon</th>
                    <th>Montant Total</th>
                    <th>Décaissé</th>
                    <th>Reste</th>
                    <th>Statut financement</th>
                    <th>Personnel</th>
                    <th>Utilisateur</th>
                    <th>Actions</th>
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
                        <span class="badge {{ $b->reste > 0 ? 'bg-warning' : 'bg-success' }}">
                            {{ $b->statut_financement }}
                        </span>
                        </td>

                        <td>{{ $b->personnels->nom ?? 'N/A' }}</td>

                        <td>{{ $b->user->name ?? 'N/A' }}</td>

                        <td>

                            <a href="{{ route('decaissements.create', $b->id) }}"
                               class="btn btn-primary btn-sm">
                                💸 Financer
                            </a>

                            <a href="{{ route('etat_bons.show', $b->id) }}"
                               class="btn btn-info btn-sm">
                                👁 Détails
                            </a>
                            <a href="{{ route('etat_bons.show', $b->id) }}?print=1"
                               class="btn btn-secondary btn-sm"
                               target="_blank">
                                Imprimer
                            </a>
                            <a href="{{ route('etat_bons.exportPdfOne', $b->id) }}"
                               class="btn btn-danger btn-sm">
                                PDF
                            </a>
                            <form action="{{ route('decaissements.destroy', $b->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm">🗑 Supprimer</button>
                            </form>
                            <a href="{{ route('decaissements.detailBon', $b->id) }}" class="btn btn-info btn-sm">
                                📊 Voir
                            </a>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">
                            ⚠ Aucun bon trouvé
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
        </ol>

    {{--<li><a href="{{ route('budget') }}"><strong>budget</strong></a></li>--}}
    {{--<li class="active"><strong>{{ $title }}</strong></li>--}}{{-- </ol>--}}
    @endsection
