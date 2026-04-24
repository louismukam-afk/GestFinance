{{--
@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>📊 Données lignes budgétaires entrée de :
            <span class="text-primary">{{ $donnee->donnee_ligne_budgetaire_entree ?? '---' }}</span>
        </h3>

        <div class="mb-3 d-flex justify-content-between">
            <a href="{{ route('donnee_ligne_entrees.create', $donnee->id) }}" class="btn btn-primary">➕ Ajouter</a>
            <div>
                <a href="{{ route('donnee_ligne_entrees.export.excel', $donnee->id) }}" class="btn btn-success">⬇️ Excel</a>
                <a href="{{ route('donnee_ligne_entrees.export.pdf', $donnee->id) }}" class="btn btn-danger">⬇️ PDF</a>
            </div>
        </div>

        <table class="table table-bordered table-striped">
            <thead class="table-dark">
            <tr>
                <th>Libellé</th>
                <th>Code</th>
                <th>Compte</th>
                <th>Description</th>
                <th>Date</th>
                <th>Élément</th>
                <th>Montants</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($lignes as $l)
                <tr>
                    <td>{{ $l->donnee_ligne_budgetaire_entree }}</td>
                    <td>{{ $l->code_donnee_ligne_budgetaire_entree }}</td>
                    <td>{{ $l->numero_donne_ligne_budgetaire_entree }}</td>
                    <td>{{ $l->description }}</td>
                    <td>{{ $l->date_creation }}</td>
                    <td>{{ $l->element_ligne_budgetaire_entrees->libelle_elements_ligne_budgetaire_entree ?? '-' }}</td>
                    <td>{{ $l->montant ?? '-' }}</td>
                    <td>
                        <a href="{{ route('donnee_ligne_entrees.edit', $l->id) }}" class="btn btn-warning btn-sm">✏️</a>
                        <form action="{{ route('donnee_ligne_entrees.destroy', $l->id) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ?')">🗑️</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center">Aucune donnée</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
--}}
{{--
@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>📊 Données lignes budgétaires entrée de :
            <span class="text-primary">{{ $donnee->donnee_ligne_budgetaire_entree ?? '---' }}</span>
        </h3>

        <div class="mb-3 d-flex justify-content-between">
            <a href="{{ route('donnee_ligne_entrees.create', $donnee->id) }}" class="btn btn-primary">➕ Ajouter</a>
            <div>
                <a href="{{ route('donnee_ligne_entrees.export.excel', $donnee->id) }}" class="btn btn-success">⬇️ Excel</a>
                <a href="{{ route('donnee_ligne_entrees.export.pdf', $donnee->id) }}" class="btn btn-danger">⬇️ PDF</a>
            </div>
        </div>

        @forelse($grouped as $budget => $byBudget)
            <h4 class="mt-4 text-success">💰 Budget : {{ $budget }}</h4>

            @foreach($byBudget as $ligne => $byLigne)
                <h5 class="mt-3 text-primary">📌 Ligne budgétaire : {{ $ligne }}</h5>

                @foreach($byLigne as $donneeLabel => $items)
                    <h6 class="mt-2 text-secondary">📝 Donnée : {{ $donneeLabel }}</h6>

                    <table class="table table-bordered table-striped table-sm">
                        <thead class="table-dark">
                        <tr>
                            <th>Libellé</th>
                            <th>Code</th>
                            <th>Compte</th>
                            <th>Description</th>
                            <th>Date</th>
                            <th>Élément</th>
                            <th>Montant</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php $total = 0; @endphp
                        @foreach($items as $l)
                            <tr>
                                <td>{{ $l->donnee_ligne_budgetaire_entree }}</td>
                                <td>{{ $l->code_donnee_ligne_budgetaire_entree }}</td>
                                <td>{{ $l->numero_donne_ligne_budgetaire_entree }}</td>
                                <td>{{ $l->description }}</td>
                                <td>{{ $l->date_creation }}</td>
                                <td>{{ $l->element_ligne_budgetaire_entrees->libelle_elements_ligne_budgetaire_entree ?? '-' }}</td>
                                <td>{{ number_format($l->montant, 0, ',', ' ') }}</td>
                                <td>
                                    <a href="{{ route('donnee_ligne_entrees.edit', $l->id) }}" class="btn btn-warning btn-sm">✏️</a>
                                    <form action="{{ route('donnee_ligne_entrees.destroy', $l->id) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ?')">🗑️</button>
                                    </form>
                                </td>
                            </tr>
                            @php $total += $l->montant; @endphp
                        @endforeach

                        <tr class="fw-bold table-secondary">
                            <td colspan="6" class="text-end">TOTAL</td>
                            <td colspan="2">{{ number_format($total, 0, ',', ' ') }}</td>
                        </tr>
                        </tbody>
                    </table>
                @endforeach
            @endforeach
        @empty
            <div class="alert alert-warning text-center">Aucune donnée disponible</div>
        @endforelse
    </div>
@endsection
--}}
@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>📊 Données lignes budgétaires entrée de :
            <span class="text-primary">{{ $donnee->donnee_ligne_budgetaire_entree ?? '---' }}</span>
        </h3>

        <div class="mb-3 d-flex justify-content-between">
            <a href="{{ route('donnee_ligne_entrees.create', $donnee->id) }}" class="btn btn-primary">➕ Ajouter</a>
            <div>
                <a href="{{ route('donnee_ligne_entrees.export.excel', $donnee->id) }}" class="btn btn-success">⬇️ Excel</a>
                <a href="{{ route('donnee_ligne_entrees.export.pdf', $donnee->id) }}" class="btn btn-danger">⬇️ PDF</a>
            </div>
        </div>

        @forelse($grouped as $budget => $byBudget)
            <h4 class="mt-4 text-success">💰 Budget : {{ $budget }}</h4>

            @php $totalBudget = 0; @endphp

            @foreach($byBudget as $ligne => $byLigne)
                <h5 class="mt-3 text-primary">📌 Ligne budgétaire : {{ $ligne }}</h5>

                @php $totalLigne = 0; @endphp

                @foreach($byLigne as $donneeLabel => $items)
                    <h6 class="mt-2 text-secondary">📝 Donnée : {{ $donneeLabel }}</h6>

                    <table class="table table-bordered table-striped table-sm">
                        <thead class="table-dark">
                        <tr>
                            <th>Libellé</th>
                            <th>Code</th>
                            <th>Compte</th>
                            <th>Description</th>
                            <th>Date</th>
                            <th>Élément</th>
                            <th>Montant</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php $totalDonnee = 0; @endphp
                        @foreach($items as $l)
                            <tr>
                                <td>{{ $l->donnee_ligne_budgetaire_entree }}</td>
                                <td>{{ $l->code_donnee_ligne_budgetaire_entree }}</td>
                                <td>{{ $l->numero_donne_ligne_budgetaire_entree }}</td>
                                <td>{{ $l->description }}</td>
                                <td>{{ $l->date_creation }}</td>
                                <td>{{ $l->element_ligne_budgetaire_entrees->libelle_elements_ligne_budgetaire_entree ?? '-' }}</td>
                                <td>{{ number_format($l->montant, 0, ',', ' ') }}</td>
                                <td>
                                    <a href="{{ route('donnee_ligne_entrees.edit', $l->id) }}" class="btn btn-warning btn-sm">✏️</a>
                                    <form action="{{ route('donnee_ligne_entrees.destroy', $l->id) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ?')">🗑️</button>
                                    </form>
                                </td>
                            </tr>
                            @php
                                $totalDonnee += $l->montant;
                            @endphp
                        @endforeach

                        <tr class="fw-bold table-secondary">
                            <td colspan="6" class="text-end">TOTAL Donnée</td>
                            <td colspan="2">{{ number_format($totalDonnee, 0, ',', ' ') }}</td>
                        </tr>

                        @php
                            $totalLigne += $totalDonnee;
                        @endphp
                        </tbody>
                    </table>
                @endforeach

                <div class="alert alert-info fw-bold">
                    📌 Total Ligne « {{ $ligne }} » : {{ number_format($totalLigne, 0, ',', ' ') }}
                </div>

                @php
                    $totalBudget += $totalLigne;
                @endphp
            @endforeach

            <div class="alert alert-success fw-bold">
                💰 Total Budget « {{ $budget }} » : {{ number_format($totalBudget, 0, ',', ' ') }}
            </div>
        @empty
            <div class="alert alert-warning text-center">Aucune donnée disponible</div>
        @endforelse
    </div>
@endsection
@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li><a href="{{ route('budget') }}"><strong>budget</strong></a></li>
        <li><a href="{{ route('donnee_entrees.index',$donnee->id)}}"><strong>Donnée Budgétaire entrées </strong></a></li>
        <li><a href="{{ route('donnee_ligne_entrees.manage',$donnee->id)}}"><strong>Imprimer / Ajouter des données lignes budgétaires entrées </strong></a></li>
        <li><a href="{{ route('donnee_ligne_entrees.index',$donnee->id)}}"><strong>Donnée </strong></a></li>

        <li class="active"><strong>{{ $title }}</strong></li>
    </ol>
@endsection