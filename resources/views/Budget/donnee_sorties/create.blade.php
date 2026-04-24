@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="mb-4">➕ Nouvelle donnée budgétaire de sortie</h3>

        <form method="POST" action="{{ route('donnee_sorties.store') }}">
            @csrf
            <div id="elements-container">
                <div class="row element-row border p-3 mb-3">
                    <div class="col-md-6 mb-3">
                        <label>Libellé</label>
                        <input type="text" name="donnee_ligne_budgetaire_sortie[]" class="form-control" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Code</label>
                        <input type="text" name="code_donnee_budgetaire_sortie[]" class="form-control" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Numéro compte</label>
                        <input type="text" name="numero_donnee_budgetaire_sortie[]" class="form-control" required>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label>Description</label>
                        <textarea name="description[]" class="form-control"></textarea>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Date création</label>
                        <input type="date" name="date_creation[]" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Budget</label>
                        <select name="id_budget" class="form-control select2" required>
                            <option value="">-- Sélectionner --</option>
                            @foreach($budgets as $b)
                                <option value="{{ $b->id }}">{{ $b->libelle_ligne_budget }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Ligne budgétaire sortie</label>
                        <select name="id_ligne_budgetaire_sortie" class="form-control select2" required>
                            <option value="">-- Sélectionner --</option>
                            @foreach($lignes as $l)
                                <option value="{{ $l->id }}">{{ $l->libelle_ligne_budgetaire_sortie }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Montant</label>
                        <input type="number" step="0.01" name="montant[]" class="form-control" required>
                    </div>
                </div>
            </div>

            <button type="button" id="addRow" class="btn btn-info">➕ Ajouter une ligne</button>
            <button type="submit" class="btn btn-success">💾 Enregistrer</button>
            <a href="{{ route('donnee_sorties.index') }}" class="btn btn-secondary">↩️ Retour</a>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('.select2').select2({ width: '100%' });
        });

        document.getElementById('addRow').addEventListener('click', function () {
            let container = document.getElementById('elements-container');
            let newRow = container.firstElementChild.cloneNode(true);

            // Réinitialiser les valeurs
            newRow.querySelectorAll('input, textarea').forEach(input => input.value = "");
            newRow.querySelectorAll('select').forEach(select => select.selectedIndex = 0);

            container.appendChild(newRow);

            // Reinitialiser Select2 sur le nouvel élément
            $(newRow).find('.select2').select2({ width: '100%' });
        });
    </script>
@endsection
@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li><a href="{{ route('budget') }}"><strong>budget</strong></a></li>
        <li><a href="{{ route('donnee_sorties.index')}}"><strong>Donnée </strong></a></li>

        <li class="active"><strong>{{ $title }}</strong></li>
    </ol>
@endsection