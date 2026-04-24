@extends('layouts.app')
@section('content')
    <div class="container">
        <h3>Ajouter des éléments à la ligne : {{ $ligne->libelle_ligne_budgetaire_sortie }}</h3>

        <form method="POST" action="{{ route('element_sorties.generate', $ligne->id) }}">
            @csrf
            <div class="form-group">
                <label>Nombre d’éléments à ajouter :</label>
                <input type="number" name="count" class="form-control" required min="1">
            </div>
            <button type="submit" class="btn btn-primary">Générer le formulaire</button>
        </form>
    </div>
@endsection
