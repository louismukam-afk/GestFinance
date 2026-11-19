@extends('layouts.app')
@section('content')
<div class="container">
    <h3 class="text-primary">{{ $title }}</h3>
    <form method="POST" action="{{ $taux->exists ? route('taux_impots.update', $taux->id) : route('taux_impots.store') }}" style="max-width: 600px;">
        @csrf
        @if($taux->exists) @method('PUT') @endif
        <div class="form-group"><label for="nomTauxImpot">Nom</label><input type="text" id="nomTauxImpot" name="nom" class="form-control" value="{{ old('nom', $taux->nom) }}" maxlength="255" required></div>
        <div class="form-group"><label for="valeurTauxImpot">Taux (%)</label><input type="number" id="valeurTauxImpot" name="taux" class="form-control" value="{{ old('taux', $taux->taux ?? '5.50') }}" min="0" max="100" step="0.01" required></div>
        <div class="checkbox"><label><input type="checkbox" name="par_defaut" value="1" {{ old('par_defaut', $taux->par_defaut) ? 'checked' : '' }}> Taux par defaut</label></div>
        <a href="{{ route('taux_impots.index') }}" class="btn btn-default">Annuler</a>
        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Enregistrer</button>
    </form>
</div>
@endsection
@section('breadcrumb')
<ol class="breadcrumb"><li><a href="{{ route('taux_impots.index') }}">Taux d'impots</a></li><li class="active">{{ $title }}</li></ol>
@endsection
