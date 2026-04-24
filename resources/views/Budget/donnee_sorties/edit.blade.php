@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="mb-4">✏️ Modifier donnée budgétaire</h3>

        <form method="POST" action="{{ route('donnee_sorties.update', $donnee->id) }}">
            @csrf @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Libellé</label>
                    <input type="text" name="donnee_ligne_budgetaire_sortie" class="form-control" value="{{ $donnee->donnee_ligne_budgetaire_sortie }}" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Code</label>
                    <input type="text" name="code_donnee_budgetaire_sortie" class="form-control" value="{{ $donnee->code_donnee_budgetaire_sortie }}" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Numéro compte</label>
                    <input type="text" name="numero_donnee_budgetaire_sortie" class="form-control" value="{{ $donnee->numero_donnee_budgetaire_sortie }}" required>
                </div>
                <div class="col-md-12 mb-3">
                    <label>Description</label>
                    <textarea name="description" class="form-control">{{ $donnee->description }}</textarea>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Date création</label>
                    <input type="date" name="date_creation" class="form-control" value="{{ $donnee->date_creation }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Budget</label>
                    <select name="id_budget" class="form-control select2" required>
                        @foreach($budgets as $b)
                            <option value="{{ $b->id }}" {{ $donnee->id_budget == $b->id ? 'selected' : '' }}>
                                {{ $b->libelle_ligne_budget }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Ligne budgétaire sortie</label>
                    <select name="id_ligne_budgetaire_sortie" class="form-control select2" required>
                        @foreach($lignes as $l)
                            <option value="{{ $l->id }}" {{ $donnee->id_ligne_budgetaire_sortie == $l->id ? 'selected' : '' }}>
                                {{ $l->libelle_ligne_budgetaire_sortie  }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Montant</label>
                    <input type="number" step="0.01" name="montant" class="form-control" value="{{ $donnee->montant }}" required>
                </div>
            </div>
            <button type="submit" class="btn btn-warning">✅ Mettre à jour</button>
            <a href="{{ route('donnee_sorties.index') }}" class="btn btn-secondary">↩️ Retour</a>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('.select2').select2({ width: '100%' });
        });
    </script>
@endsection
@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li><a href="{{ route('budget') }}"><strong>budget</strong></a></li>
        <li><a href="{{ route('donnee_sorties.index')}}"><strong>Données Budgétaires sorties</strong></a></li>

        <li class="active"><strong>{{ $title }}</strong></li>
    </ol>
@endsection