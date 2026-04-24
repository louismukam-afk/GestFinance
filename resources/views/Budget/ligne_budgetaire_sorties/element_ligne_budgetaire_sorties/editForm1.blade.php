@extends('layouts.app')
@section('content')

    <div class="container">
        <h3>
            ✏️ Modifier les éléments de la ligne budgétaire sortie :
            <span class="text-primary">{{ $ligne->libelle_ligne_budgetaire_sortie }}</span>
        </h3>

        <form method="POST" action="{{ route('element_sorties.updateAll', $ligne->id) }}">
            @csrf
            @method('PUT')

            @foreach($elements as $i => $el)
                <fieldset style="border:1px solid #ddd; padding:15px; margin-bottom:15px;">
                    <legend>Élément {{ $i+1 }}</legend>

                    <input type="hidden" name="elements[{{ $i }}][id]" value="{{ $el->id }}">

                    <div class="form-group">
                        <label>Libellé :</label>
                        <input type="text" name="elements[{{ $i }}][libelle]"
                               value="{{ $el->libelle_elements_ligne_budgetaire_sortie }}"
                               class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Code :</label>
                        <input type="text" name="elements[{{ $i }}][code]"
                               value="{{ $el->code_elements_ligne_budgetaire_sortie }}"
                               class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>N° Compte :</label>
                        <input type="text" name="elements[{{ $i }}][compte]"
                               value="{{ $el->numero_compte_elements_ligne_budgetaire_sortie }}"
                               class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Description :</label>
                        <textarea name="elements[{{ $i }}][description]" class="form-control">{{ $el->description }}</textarea>
                    </div>

                    <div class="form-group">
                        <label>Date création :</label>
                        <input type="date" name="elements[{{ $i }}][date_creation]"
                               value="{{ $el->date_creation }}"
                               class="form-control" required>
                    </div>
                </fieldset>
            @endforeach

            <button type="submit" class="btn btn-primary">
                💾 Enregistrer les modifications
            </button>
        </form>
    </div>

@endsection
