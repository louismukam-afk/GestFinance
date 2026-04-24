@extends('layouts.app')
@section('content')

    <div class="container">
        <h3>
            Gestion des éléments de la ligne budgétaire sortie :
            <span class="text-primary">{{ $ligne->libelle_ligne_budgetaire_sortie }}</span>
        </h3>
        <p><strong>Code :</strong> {{ $ligne->code_ligne_budgetaire_sortie }} |
            <strong>N° Compte :</strong> {{ $ligne->numero_compte_ligne_budgetaire_sortie }}</p>

        @if($elements->count() == 0)
            {{-- ✅ Si aucun élément n’est encore enregistré --}}
            <div class="alert alert-warning">
                Cette ligne budgétaire sortie ne contient encore aucun élément.
            </div>

            <form method="GET" action="{{ route('element_sorties.create', $ligne->id) }}">
                <div class="form-group">
                    <label for="nb_lignes">Combien d’éléments souhaitez-vous enregistrer ?</label>
                    <input type="number" min="1" class="form-control" id="nb_lignes" name="nb_lignes" required>
                </div>
                <button type="submit" class="btn btn-success">
                    ➕ Ajouter des éléments
                </button>
            </form>

        @else
            {{-- ✅ Si des éléments existent déjà --}}
            <div class="alert alert-info">
                Cette ligne budgétaire sortie contient déjà <strong>{{ $elements->count() }}</strong> élément(s).
                Que souhaitez-vous faire ?
            </div>

            <a href="{{ route('element_sorties.create', $ligne->id) }}" class="btn btn-success">
                ➕ Ajouter d'autres éléments
            </a>
            <a href="{{ route('element_sorties.editForm', $ligne->id) }}" class="btn btn-warning">
                ✏️ Modifier les éléments existants
            </a>
            <a href="{{ route('element_sorties.index', $ligne->id) }}" class="btn btn-primary">
                📄 Lister / Imprimer
            </a>
        @endif
    </div>

@endsection
