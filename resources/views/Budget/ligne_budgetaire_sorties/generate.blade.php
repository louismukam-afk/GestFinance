@extends('layouts.app')
@section('content')
    <div class="container">
        <h3>Ajouter {{ $count }} éléments à la ligne : {{ $ligne->libelle_ligne_budgetaire_sortie }}</h3>

        <form method="POST" action="{{ route('element_sorties.store', $ligne->id) }}">
            @csrf
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>Libellé</th>
                    <th>Code</th>
                    <th>N° Compte</th>
                    <th>Description</th>
                    <th>Date Création</th>
                </tr>
                </thead>
                <tbody>
                @for($i=0; $i < $count; $i++)
                    <tr>
                        <td><input type="text" name="libelle[]" class="form-control" required></td>
                        <td><input type="text" name="code[]" class="form-control" required></td>
                        <td><input type="text" name="numero_compte[]" class="form-control" required></td>
                        <td><input type="text" name="description[]" class="form-control"></td>
                        <td><input type="date" name="date_creation[]" class="form-control" required></td>
                    </tr>
                @endfor
                </tbody>
            </table>
            <button type="submit" class="btn btn-success">Enregistrer</button>
        </form>
    </div>
@endsection
