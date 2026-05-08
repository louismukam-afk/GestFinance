@extends('layouts.app')

@section('content')
@php($reste = max($facture->montant_total_facture - $totalPaye, 0))
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-primary">{{ $title }}</h3>
        <div>
            <a href="{{ route('reglement_by_facture', $facture->id) }}" class="btn btn-success">Regler</a>
            <a href="{{ route('factures_rattrapage.index') }}" class="btn btn-secondary">Retour</a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4"><strong>Etudiant :</strong> {{ $facture->etudiants->nom ?? '-' }}</div>
                <div class="col-md-2"><strong>N facture :</strong> {{ $facture->numero_facture }}</div>
                <div class="col-md-3"><strong>Annee :</strong> {{ $facture->Annee_academique->nom ?? '-' }}</div>
                <div class="col-md-3"><strong>Entite :</strong> {{ $facture->entite->nom_entite ?? '-' }}</div>
                <div class="col-md-12 mt-2">
                    <strong>Budget :</strong>
                    {{ $facture->budget->libelle_ligne_budget ?? '-' }} /
                    {{ $facture->ligne_budgetaire_entree->libelle_ligne_budgetaire_entree ?? '-' }} /
                    {{ $facture->element_ligne_budgetaire_entree->libelle_elements_ligne_budgetaire_entree ?? '-' }} /
                    {{ $facture->donnee_budgetaire_entree->donnee_ligne_budgetaire_entree ?? '-' }} /
                    {{ $facture->donnee_ligne_budgetaire_entree->donnee_ligne_budgetaire_entree ?? '-' }}
                </div>
            </div>
        </div>
    </div>

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Matiere</th>
                <th>Prix unitaire</th>
                <th>Quantite</th>
                <th>Montant</th>
                <th>Observation</th>
            </tr>
        </thead>
        <tbody>
            @foreach($facture->lignes_rattrapage as $i => $ligne)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $ligne->matiere->nom_matiere ?? '-' }}</td>
                    <td>{{ number_format($ligne->prix_unitaire, 0, ',', ' ') }}</td>
                    <td>{{ $ligne->quantite }}</td>
                    <td>{{ number_format($ligne->montant, 0, ',', ' ') }}</td>
                    <td>{{ $ligne->observation }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4">Total facture</th>
                <th colspan="2">{{ number_format($facture->montant_total_facture, 0, ',', ' ') }}</th>
            </tr>
            <tr>
                <th colspan="4">Deja encaisse</th>
                <th colspan="2">{{ number_format($totalPaye, 0, ',', ' ') }}</th>
            </tr>
            <tr>
                <th colspan="4">Reste</th>
                <th colspan="2">{{ number_format($reste, 0, ',', ' ') }}</th>
            </tr>
        </tfoot>
    </table>
</div>
@endsection
