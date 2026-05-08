@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between mb-3">
            <h3>{{ $entree->libelle }}</h3>
            <a href="{{ route('entrees_speciales.edit', $entree->id) }}" class="btn btn-warning">Modifier</a>
        </div>

        <table class="table table-bordered">
            <tr><th>Type</th><td>{{ $types[$entree->type_entree] ?? $entree->type_entree }}</td></tr>
            <tr><th>Code</th><td>{{ $entree->code_entree }}</td></tr>
            <tr><th>Tiers</th><td>{{ $entree->nom_tiers }} - {{ $entree->telephone_tiers }} - {{ $entree->adresse_tiers }}</td></tr>
            <tr><th>Date entree</th><td>{{ optional($entree->date_entree)->format('d/m/Y') }}</td></tr>
            <tr><th>Date contraction dette</th><td>{{ optional($entree->date_contraction_dette)->format('d/m/Y') }}</td></tr>
            <tr><th>Date remboursement final</th><td>{{ optional($entree->date_remboursement)->format('d/m/Y') }}</td></tr>
            <tr><th>Caisse</th><td>{{ optional($entree->caisse)->nom_caisse }}</td></tr>
            <tr><th>Budget</th><td>{{ optional($entree->budget)->libelle_ligne_budget }}</td></tr>
            <tr><th>Annee utilisation</th><td>{{ optional($entree->annee_utilisation)->nom }}</td></tr>
            <tr><th>Annee remboursement</th><td>{{ optional($entree->annee_remboursement)->nom }}</td></tr>
            <tr><th>Montant</th><td>{{ number_format($entree->montant, 0, ',', ' ') }} FCFA</td></tr>
            <tr><th>Montant rembourse</th><td>{{ number_format($entree->montant_rembourse, 0, ',', ' ') }} FCFA</td></tr>
            <tr><th>Encaisse net</th><td>{{ number_format($entree->montant_net_encaisse, 0, ',', ' ') }} FCFA</td></tr>
            <tr><th>Observations</th><td>{{ $entree->observations }}</td></tr>
        </table>

        <h4>Echeances</h4>
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Nom</th>
                <th>Date</th>
                <th>Montant</th>
                <th>Statut</th>
                <th>Observation</th>
            </tr>
            </thead>
            <tbody>
            @forelse($entree->echeances as $echeance)
                <tr>
                    <td>{{ $echeance->nom_echeance }}</td>
                    <td>{{ optional($echeance->date_echeance)->format('d/m/Y') }}</td>
                    <td>{{ number_format($echeance->montant, 0, ',', ' ') }}</td>
                    <td>{{ $echeance->statut }}</td>
                    <td>{{ $echeance->observations }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center">Aucune echeance.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection

@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li><a href="{{ route('budget') }}"><strong>Budget</strong></a></li>
        <li><a href="{{ route('entrees_speciales.index') }}"><strong>Entrees speciales</strong></a></li>
        <li class="active"><strong>Detail</strong></li>
    </ol>
@endsection
