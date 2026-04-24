@extends('layouts.app')
@section('content')

    <div class="container">
        <h3>
            Ajouter des éléments à la ligne :
            <span class="text-primary">{{ $ligne->libelle_ligne_budgetaire_sortie }}</span>
        </h3>

        <form method="POST" action="{{ route('element_sorties.store', $ligne->id) }}">
            @csrf

            <div id="form-container">
                <div class="element-row border p-3 mb-3">
                    <div class="form-group">
                        <label>Libellé</label>
                        <input type="text" name="libelle[]" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Code</label>
                        <input type="text" name="code[]" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>N° Compte</label>
                        <input type="text" name="compte[]" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description[]" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Date création</label>
                        <input type="date" name="date_creation[]" class="form-control" required>
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-info" id="addRow">➕ Ajouter une ligne</button>
            <button type="submit" class="btn btn-success">💾 Enregistrer</button>
            <a href="{{ route('element_sorties.index', $ligne->id) }}" class="btn btn-default">⬅ Retour</a>
        </form>
    </div>

@endsection
@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li><a href="{{ route('budget') }}"><strong>budget</strong></a></li>
        <li><a href="{{ route('element_entrees.manage',$ligne->id)}}"><strong>imprimer la liste des éléments de budget</strong></a></li>

        <li class="active"><strong>{{ $title }}</strong></li>
    </ol>
@section('scripts')
    <script>
        document.getElementById('addRow').addEventListener('click', function () {
            let container = document.getElementById('form-container');
            let newRow = container.firstElementChild.cloneNode(true);
            newRow.querySelectorAll('input, textarea').forEach(el => el.value = '');
            container.appendChild(newRow);
        });
    </script>
@endsection
