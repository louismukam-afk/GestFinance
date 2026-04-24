@extends('layouts.app')
@section('content')

    <div class="container">
        <h3>Ajouter des éléments au Bon : {{ $bon->nom_bon_commande }}</h3>
        <form method="POST" action="{{ route('element_bon.buildForm', $bon->id) }}">
            @csrf
            <div class="form-group">
                <label>Nombre de lignes à saisir :</label>
                <input type="number" name="nombre_lignes" class="form-control" min="1" required>
            </div>
            <button type="submit" class="btn btn-primary">Continuer</button>
        </form>
    </div>

@endsection
