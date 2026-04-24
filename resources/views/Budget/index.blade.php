@extends('layouts.app')

@section('content')
    <div class="container">
        <h2 class="text-center mb-4">💰 Gestion des Budgets</h2>

        <!-- 🔹 Formulaire de filtre par période -->
        <form method="GET" action="{{ route('budgets.index') }}" class="row g-3 mb-4">
            <div class="col-md-4">
                <label>Date début</label>
                <input type="date" name="date_debut" class="form-control" value="{{ request('date_debut') }}">
            </div>
            <div class="col-md-4">
                <label>Date fin</label>
                <input type="date" name="date_fin" class="form-control" value="{{ request('date_fin') }}">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">🔍 Rechercher</button>
                <a href="{{ route('budgets.index') }}" class="btn btn-secondary">♻ Réinitialiser</a>
            </div>
        </form>

        <!-- 🔹 Boutons globaux -->
        <div class="mb-3 d-flex justify-content-between">
            <a href="{{ route('budgets.create') }}" class="btn btn-success">➕ Nouveau Budget</a>
            <div>
                <a href="{{ route('budgets.export_pdf', request()->all()) }}" class="btn btn-danger">
                    📄 Exporter PDF
                </a>
                <a href="{{ route('budgets.export_excel', request()->all()) }}" class="btn btn-success">
                    📊 Exporter Excel
                </a>
            </div>
        </div>

        <!-- 🔹 Tableau des budgets -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Libellé</th>
                    <th>Code</th>
                    <th>Description</th>
                    <th>Date Début</th>
                    <th>Date Fin</th>
                    <th>Montant Global</th>
                    <th>Utilisateur</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($budgets as $i => $budget)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $budget->libelle_ligne_budget }}</td>
                        <td>{{ $budget->code_budget }}</td>
                        <td>{{ $budget->description }}</td>
                        <td>{{ $budget->date_debut }}</td>
                        <td>{{ $budget->date_fin }}</td>
                        <td>{{ number_format($budget->montant_global,0,',',' ') }} FCFA</td>
                        <td>{{ $budget->user->name ?? 'N/A' }}</td>
                        <td>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    ⚙ Actions
                                </button>
                                <ul class="dropdown-menu">
                                    <!-- Voir -->
                                    <li>
                                        <a class="dropdown-item text-info" href="{{ route('budgets.show', $budget->id) }}">
                                            👁 Voir
                                        </a>
                                    </li>

                                    <!-- Modifier -->
                                    <li>
                                        <a class="dropdown-item text-primary" href="{{ route('budgets.edit', $budget->id) }}">
                                            ✏ Modifier
                                        </a>
                                    </li>

                                    <!-- Supprimer -->
                                    <li>
                                        <form action="{{ route('budgets.destroy', $budget->id) }}" method="POST" onsubmit="return confirm('Supprimer ce budget ?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                🗑 Supprimer
                                            </button>
                                        </form>
                                    </li>

                                    <li><hr class="dropdown-divider"></li>

                                    <!-- Export PDF -->
                                    <li>
                                        <a class="dropdown-item text-danger" href="{{ route('budgets.export_pdf_one', $budget->id) }}">
                                            📄 Exporter PDF
                                        </a>
                                    </li>

                                    <!-- Export Excel -->
                                    <li>
                                        <a class="dropdown-item text-success" href="{{ route('budgets.export_excel_one', $budget->id) }}">
                                            📊 Exporter Excel
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </td>


                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">⚠ Aucun budget trouvé pour cette période</td>
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
        {{--<li><a href="{{ route('budget') }}"><strong>budget</strong></a></li>--}}

        {{--<li class="active"><strong>{{ $title }}</strong></li>--}}
    </ol>
@endsection
