@extends('layouts.app')
@section('content')

    <div class="container">
        <h3>
            Modifier l’élément de la ligne  budgétaire sortie:
            <span class="text-primary">{{ $element->ligne_budgetaire_sortie->libelle_ligne_budgetaire_sortie }}</span>
        </h3>

        <form method="POST" action="{{ route('element_sorties.update', $element->id) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Libellé</label>
                <input type="text" name="libelle" class="form-control" value="{{ $element->libelle_elements_ligne_budgetaire_sortie }}" required>
            </div>
            <div class="form-group">
                <label>Code</label>
                <input type="text" name="code" class="form-control" value="{{ $element->code_elements_ligne_budgetaire_sortie }}" required>
            </div>
            <div class="form-group">
                <label>N° Compte</label>
                <input type="text" name="compte" class="form-control" value="{{ $element->numero_compte_elements_ligne_budgetaire_sortie }}" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control">{{ $element->description }}</textarea>
            </div>
            <div class="form-group">
                <label>Date création</label>
                <input type="date" name="date_creation" class="form-control" value="{{ $element->date_creation }}" required>
            </div>

            <button type="submit" class="btn btn-primary">💾 Mettre à jour</button>
            <a href="{{ route('element_sorties.index', $element->id_ligne_budgetaire_sortie) }}" class="btn btn-default">⬅ Retour</a>
        </form>
    </div>

@endsection
@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li><a href="{{ route('budget') }}"><strong>budget</strong></a></li>
        <li><a href="{{ route('element_entrees.manage',$element->id)}}"><strong>imprimer la liste des éléments de budget</strong></a></li>

        <li class="active"><strong>{{ $title }}</strong></li>
    </ol>