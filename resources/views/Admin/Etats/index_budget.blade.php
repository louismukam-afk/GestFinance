@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="mb-4">📊 États budgétaires – Entrées</h3>

        <form method="GET" action="{{ route('etat_atterrissage_budgetaire') }}" class="row g-3 mb-4">
            <div class="col-md-3">
                <label>Année académique</label>
                <select name="annee" class="form-control">
                    <option value="">-- Toutes --</option>
                    @foreach($annees as $a)
                        <option value="{{ $a->id }}">{{ $a->nom }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label>Entité</label>
                <select name="entite" class="form-control">
                    <option value="">-- Toutes --</option>
                    @foreach($entites as $e)
                        <option value="{{ $e->id }}">{{ $e->nom_entite }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label>Caisse</label>
                <select name="caisse" class="form-control">
                    <option value="">-- Toutes --</option>
                    @foreach($caisses as $c)
                        <option value="{{ $c->id }}">{{ $c->nom_caisse }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-primary w-100">
                    🔍 Afficher l’atterrissage
                </button>
            </div>
        </form>

        <div class="alert alert-info">
            ℹ️ Sélectionnez les critères puis cliquez sur
            <b>« Afficher l’atterrissage »</b> pour consulter
            l’état budgétaire détaillé.
        </div>
    </div>
@endsection
@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent; padding: 4px 10px">
        <li class="breadcrumb-item">
            <a href="{{ route('home') }}"><strong>Accueil</strong></a>
        </li>
        <li class="breadcrumb-item active">
            <a href="{{ route('etats.index') }}"><strong>États comptables</strong></a>

        </li>

        <li class="breadcrumb-item">
            <a href="{{ route('etat_budget') }}"><strong>Nouvelle attérissage</strong></a>
        </li>
    </ol>
@endsection