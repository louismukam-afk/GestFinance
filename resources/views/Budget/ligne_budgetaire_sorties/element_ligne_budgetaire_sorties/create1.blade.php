@extends('layouts.app')
@section('content')

    <div class="container">
        <h3>
            ➕ Ajouter des éléments à la ligne budgétaire sortie :
            <span class="text-primary">{{ $ligne->libelle_ligne_budgetaire_sortie }}</span>
        </h3>

        <p><strong>Code :</strong> {{ $ligne->code_ligne_budgetaire_sortie }} |
            <strong>N° Compte :</strong> {{ $ligne->numero_compte_ligne_budgetaire_sortie }}</p>

        <form method="POST" action="{{ route('element_sorties.store', $ligne->id) }}">
            @csrf

            {{-- ✅ Nombre de lignes choisi dans manage.blade --}}
            @for($i = 1; $i <= $nb_lignes; $i++)
                <fieldset style="border:1px solid #ddd; padding:15px; margin-bottom:15px;">
                    <legend>Élément {{ $i }}</legend>

                    <div class="form-group">
                        <label>Libellé :</label>
                        <input type="text" name="elements[{{ $i }}][libelle]" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Code :</label>
                        <input type="text" name="elements[{{ $i }}][code]" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>N° Compte :</label>
                        <input type="text" name="elements[{{ $i }}][compte]" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Description :</label>
                        <textarea name="elements[{{ $i }}][description]" class="form-control"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Date création :</label>
                        <input type="date" name="elements[{{ $i }}][date_creation]" class="form-control" required>
                    </div>
                </fieldset>
            @endfor

            <button type="submit" class="btn btn-success">
                💾 Enregistrer les éléments
            </button>
        </form>
    </div>

@endsection
