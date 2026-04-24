@extends('layouts.app')

@section('content')

<div class="form-group">
    <label>Libellé</label>
    <input type="text" name="libelle_ligne_budgetaire_entree" class="form-control"
           value="{{ old('libelle_ligne_budgetaire_entree', $ligne->libelle_ligne_budgetaire_entree ?? '') }}" required>
</div>

<div class="form-group">
    <label>Code</label>
    <input type="text" name="code_ligne_budgetaire_entree" class="form-control"
           value="{{ old('code_ligne_budgetaire_entree', $ligne->code_ligne_budgetaire_entree ?? '') }}" required>
</div>

<div class="form-group">
    <label>N° Compte</label>
    <input type="text" name="numero_compte_ligne_budgetaire_entree" class="form-control"
           value="{{ old('numero_compte_ligne_budgetaire_entree', $ligne->numero_compte_ligne_budgetaire_entree ?? '') }}" required>
</div>

<div class="form-group">
    <label>Description</label>
    <textarea name="description" class="form-control">{{ old('description', $ligne->description ?? '') }}</textarea>
</div>

<div class="form-group">
    <label>Date de création</label>
    <input type="date" name="date_creation" class="form-control"
           value="{{ old('date_creation', $ligne->date_creation ?? '') }}" required>
</div>
    @endsection
