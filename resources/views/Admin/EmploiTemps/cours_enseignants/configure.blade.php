@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="text-primary">{{ $title }}</h3>
            <p class="text-muted mb-0">{{ $specialite->nom_specialite }} - {{ $specialite->code_specialite }}</p>
        </div>
        <a href="{{ route('cours_enseignants.contextes') }}" class="btn btn-secondary">Retour</a>
    </div>

    <div class="card">
        <div class="card-header"><strong>Contexte de programmation</strong></div>
        <div class="card-body">
            <form method="GET" action="{{ route('cours_enseignants.create_context', $specialite) }}">
                @foreach($baseContext as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <div class="row">
                    <div class="col-md-4">
                        <label>Cycle</label>
                        <input type="text" class="form-control" value="{{ $baseContextLabels['cycle'] ?? '-' }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label>Filiere</label>
                        <input type="text" class="form-control" value="{{ $baseContextLabels['filiere'] ?? '-' }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label>Niveau</label>
                        <input type="text" class="form-control" value="{{ $baseContextLabels['niveau'] ?? '-' }}" readonly>
                    </div>
                    <div class="col-md-4 mt-2">
                        <label>Annee academique</label>
                        <select name="id_annee_academique" class="form-control" required>
                            <option value="">--</option>
                            @foreach($annees as $annee)
                                <option value="{{ $annee->id }}">{{ $annee->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mt-2">
                        <label>Entite</label>
                        <select name="id_entite" class="form-control" required>
                            <option value="">--</option>
                            @foreach($entites as $entite)
                                <option value="{{ $entite->id }}">{{ $entite->nom_entite }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mt-2 d-flex align-items-end">
                        <button class="btn btn-success w-100">Continuer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('breadcrumb')
<ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
    <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
    <li><a href="{{ route('emploi_temps.index') }}"><strong>Ressources humaines</strong></a></li>
    <li><a href="{{ route('cours_enseignants.contextes') }}"><strong>Choisir la specialite</strong></a></li>
    <li class="active"><strong>{{ $title }}</strong></li>
</ol>
@endsection
