@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="mb-4">📊 Données budgétaires d'entrée</h3>

        <div class="mb-3 d-flex justify-content-between">
            <a href="{{ route('donnee_entrees.create') }}" class="btn btn-primary">➕ Nouvelle donnée entrée</a>
        </div>

        <!-- Filtres -->
        <form method="GET" action="{{ route('donnee_entrees.index') }}" class="row g-3 mb-3">
            <div class="col-md-3">
                <label>Date début</label>
                <input type="date" name="date_debut" class="form-control" value="{{ request('date_debut') }}">
            </div>
            <div class="col-md-3">
                <label>Date fin</label>
                <input type="date" name="date_fin" class="form-control" value="{{ request('date_fin') }}">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">🔍 Rechercher</button>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <a href="{{ route('donnee_entrees.export.excel', request()->all()) }}" class="btn btn-success me-2">⬇️ Excel</a>
                <a href="{{ route('donnee_entrees.export.pdf', request()->all()) }}" class="btn btn-danger">⬇️ PDF</a>
            </div>
        </form>

        @forelse($grouped as $budget => $lignes)
            <h4 class="mt-4 text-primary">📘 Budget : {{ $budget }}</h4>
            @php $totalBudget = 0; @endphp

            @foreach($lignes as $ligne => $donnees)
                <h5 class="mt-3">➡️ Ligne : {{ $ligne }}</h5>
                <table class="table table-bordered">
                    <thead class="table-dark">
                    <tr>
                        <th>Libellé</th>
                        <th>Code</th>
                        <th>Compte</th>
                        <th>Montant</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php $totalLigne = 0; @endphp
                    @foreach($donnees as $d)
                        <tr>
                            <td>{{ $d->donnee_ligne_budgetaire_entree }}</td>
                            <td>{{ $d->code_donnee_budgetaire_entree }}</td>
                            <td>{{ $d->numero_donnee_budgetaire_entree }}</td>
                            <td>{{ number_format($d->montant, 0, ',', ' ') }} FCFA</td>
                            <td>{{ $d->date_creation }}</td>
                            <td>
                                {{-- ✅ Gérer les éléments --}}
                                <a href="{{ route('donnee_ligne_entrees.manage', $d->id) }}" class="btn btn-xs btn-default" style="margin:2px; background-color:#eee;">
                                    ➕ Éléments
                                </a>
                                <a href="{{ route('donnee_entrees.edit', $d->id) }}" class="btn btn-warning btn-sm">✏️</a>
                                <form action="{{ route('donnee_entrees.destroy', $d->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ?')">🗑️</button>
                                </form>
                            </td>
                        </tr>
                        @php $totalLigne += $d->montant; @endphp
                    @endforeach
                    <tr class="table-secondary">
                        <td colspan="3"><b>Total Ligne</b></td>
                        <td colspan="3"><b>{{ number_format($totalLigne, 0, ',', ' ') }} FCFA</b></td>
                    </tr>
                    </tbody>
                </table>
                @php $totalBudget += $totalLigne; @endphp
            @endforeach

            <div class="alert alert-info"><b>Total Budget {{ $budget }} : {{ number_format($totalBudget, 0, ',', ' ') }} FCFA</b></div>
        @empty
            <p class="text-center">⚠️ Aucune donnée trouvée.</p>
        @endforelse
    </div>
@endsection
@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li><a href="{{ route('budget') }}"><strong>budget</strong></a></li>
        {{--<li><a href="{{ route('element_entrees.manage',$ligne->id)}}"><strong>Donnée </strong></a></li>--}}

        <li class="active"><strong>{{ $title }}</strong></li>
    </ol>
@endsection