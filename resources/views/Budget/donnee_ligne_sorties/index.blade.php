{{--
@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>📊 Données lignes budgétaires sorties de :
            <span class="text-primary">{{ $donnee->donnee_ligne_budgetaire_sortie ?? '---' }}</span>
        </h3>

        <div class="mb-3 d-flex justify-content-between">
            <a href="{{ route('donnee_ligne_sorties.create', $donnee->id) }}" class="btn btn-primary">➕ Ajouter</a>
            <div>
                <a href="{{ route('donnee_ligne_sorties.export.excel', $donnee->id) }}" class="btn btn-success">⬇️ Excel</a>
                <a href="{{ route('donnee_ligne_sorties.export.pdf', $donnee->id) }}" class="btn btn-danger">⬇️ PDF</a>
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
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($lignes as $l)
                <tr>
                    <td>{{ $l->donnee_ligne_budgetaire_sortie }}</td>
                    <td>{{ $l->code_donnee_ligne_budgetaire_sortie }}</td>
                    <td>{{ $l->numero_donne_ligne_budgetaire_sortie }}</td>
                    <td>{{ $l->description }}</td>
                    <td>{{ $l->date_creation }}</td>
                    <td>{{ $l->element_ligne_budgetaire_sorties->libelle_elements_ligne_budgetaire_sortie ?? '-' }}</td>
                    <td>
                        <a href="{{ route('donnee_ligne_sorties.edit', $l->id) }}" class="btn btn-warning btn-sm">✏️</a>
                        <form action="{{ route('donnee_ligne_sorties.destroy', $l->id) }}" method="POST" class="d-inline">
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
@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>📊 Données lignes budgétaires sorties de :
            <span class="text-primary">{{ $donnee->donnee_ligne_budgetaire_sortie ?? '---' }}</span>
        </h3>

        <div class="mb-3 d-flex justify-content-between">
            <a href="{{ route('donnee_ligne_sorties.create', $donnee->id) }}" class="btn btn-primary">➕ Ajouter</a>
            <div>
                <a href="{{ route('donnee_ligne_sorties.export.excel', $donnee->id) }}" class="btn btn-success">⬇️ Excel</a>
                <a href="{{ route('donnee_ligne_sorties.export.pdf', $donnee->id) }}" class="btn btn-danger">⬇️ PDF</a>
            </div>
        </div>

        @forelse($grouped as $budget => $lignesBudget)
            <h4 class="mt-4 text-success">💰 Budget : {{ $budget }}</h4>

            @foreach($lignesBudget as $ligne => $donneesLigne)
                <h5 class="mt-3 text-primary">📌 Ligne budgétaire : {{ $ligne }}</h5>

                @foreach($donneesLigne as $donneeSortie => $liste)
                    <h6 class="mt-2 text-secondary">📄 Donnée budgétaire : {{ $donneeSortie }}</h6>

                    <table class="table table-bordered table-striped mt-2">
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
                        @foreach($liste as $l)
                            <tr>
                                <td>{{ $l->donnee_ligne_budgetaire_sortie }}</td>
                                <td>{{ $l->code_donnee_ligne_budgetaire_sortie }}</td>
                                <td>{{ $l->numero_donne_ligne_budgetaire_sortie }}</td>
                                <td>{{ $l->description }}</td>
                                <td>{{ $l->date_creation }}</td>
                                <td>{{ $l->element_ligne_budgetaire_sorties->libelle_elements_ligne_budgetaire_sortie ?? '-' }}</td>
                                <td>{{ number_format($l->montant ?? 0, 0, ',', ' ') }} FCFA</td>
                                <td>
                                    <a href="{{ route('donnee_ligne_sorties.edit', $l->id) }}" class="btn btn-warning btn-sm">✏️</a>
                                    <form action="{{ route('donnee_ligne_sorties.destroy', $l->id) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ?')">🗑️</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        <tr class="fw-bold table-secondary">
                            <td colspan="6" class="text-end">Total :</td>
                            <td colspan="2">
                                {{ number_format($liste->sum('montant'), 0, ',', ' ') }} FCFA
                            </td>
                        </tr>
                        </tbody>
                    </table>
                @endforeach
            @endforeach
        @empty
            <p class="text-center text-muted">Aucune donnée enregistrée</p>
        @endforelse
    </div>
@endsection
@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li><a href="{{ route('budget') }}"><strong>budget</strong></a></li>
        <li><a href="{{ route('donnee_sorties.index',$donnee->id)}}"><strong>Donnée Budgétaire sorties </strong></a></li>
        <li><a href="{{ route('donnee_ligne_sorties.manage',$donnee->id)}}"><strong>Imprimer / Ajouter des données lignes budgétaires sorties </strong></a></li>
        <li><a href="{{ route('donnee_ligne_sorties.index',$donnee->id)}}"><strong>Donnée </strong></a></li>

        <li class="active"><strong>{{ $title }}</strong></li>
    </ol>
@endsection